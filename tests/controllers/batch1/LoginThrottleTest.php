<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Redooor\Redminportal\App\Models\User;

class LoginThrottleTest extends RedminBrowserTestCase
{
    private const CLIENT_IP = '203.0.113.10';

    private $admin;

    /**
     * Initialize setup with a clean array cache.
     */
    public function setUp(): void
    {
        parent::setUp();

        Config::set('cache.default', 'array');

        $this->useCaseInsensitiveEmailComparison();

        $this->admin = $this->createUser('admin@admin.com', 'admin');

        Auth::guard('redminguard')->logout();
        RateLimiter::clear('user:' . $this->admin->id . '|' . self::CLIENT_IP);
        RateLimiter::clear($this->emailKey('admin@admin.com'));
        RateLimiter::clear($this->ipKey());
    }

    /**
     * Use a case-insensitive email collation in the SQLite test database.
     */
    private function useCaseInsensitiveEmailComparison()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        $table = DB::selectOne("select sql from sqlite_master where type = 'table' and name = 'users'");

        if ($table === null || $table->sql === null) {
            return;
        }

        $indexes = DB::select("select sql from sqlite_master where type = 'index' and tbl_name = 'users' and sql is not null");
        $create = preg_replace('/^create table ["\']?users["\']?/i', 'create table "users_nocase"', $table->sql, 1);
        $create = preg_replace('/"email"\s+varchar(\s+not null)?/i', '"email" varchar collate nocase$1', $create, 1);

        DB::statement('pragma foreign_keys = off');
        DB::statement($create);
        DB::statement('insert into "users_nocase" select * from "users"');
        DB::statement('drop table "users"');
        DB::statement('alter table "users_nocase" rename to "users"');

        foreach ($indexes as $index) {
            DB::statement($index->sql);
        }

        DB::statement('pragma foreign_keys = on');
    }

    private function limiterKey($email)
    {
        $user = User::where('email', $email)->first();

        if ($user != null) {
            return 'user:' . $user->id . '|' . self::CLIENT_IP;
        }

        return $this->emailKey($email);
    }

    private function emailKey($email)
    {
        return strtolower(trim($email)) . '|' . self::CLIENT_IP;
    }

    private function ipKey()
    {
        return 'ip:' . self::CLIENT_IP;
    }

    private function login($email, $password)
    {
        return $this->call('POST', '/login/login', [
            'email' => $email,
            'password' => $password,
        ], [], [], ['REMOTE_ADDR' => self::CLIENT_IP]);
    }

    private function createUser($email, $password)
    {
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->first_name = 'Other';
        $user->last_name = 'User';
        $user->activated = 1;
        $user->save();

        return $user;
    }

    private function assertTooManyAttemptsError()
    {
        $this->assertSessionHasErrors('invalid');

        $errors = $this->app['session']->get('errors');

        $this->assertMatchesRegularExpression(
            '/^Too many login attempts\. Try again in [0-9]+ seconds\.$/',
            $errors->first('invalid')
        );
    }

    /**
     * Test (Fail): correct password is refused after five failed attempts.
     */
    public function testCorrectPasswordRefusedAfterFiveFailedAttempts()
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->login('admin@admin.com', 'wrong');
            $this->assertRedirectedTo('login');
            $this->assertTrue(Auth::guard('redminguard')->guest());
        }

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->assertRedirectedTo('login');
        $this->assertTooManyAttemptsError();
    }

    /**
     * Test (Pass): another email is not affected by the lockout.
     */
    public function testDifferentEmailIsNotLocked()
    {
        $this->createUser('other@example.com', 'other-password');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->login('admin@admin.com', 'wrong');
        }

        $this->login('other@example.com', 'other-password');

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertRedirectedTo('/admin/dashboard');
    }

    /**
     * Test (Pass): a successful login clears the failed attempt counter.
     */
    public function testSuccessfulLoginResetsAttempts()
    {
        $key = $this->limiterKey('admin@admin.com');

        $this->login('admin@admin.com', 'wrong');
        $this->login('admin@admin.com', 'wrong');

        $this->assertSame(2, (int) RateLimiter::attempts($key));

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertSame(0, (int) RateLimiter::attempts($key));
    }

    /**
     * Test (Pass): login succeeds again after the limiter is cleared.
     */
    public function testLoginSucceedsAfterLimiterIsCleared()
    {
        $key = $this->limiterKey('admin@admin.com');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->login('admin@admin.com', 'wrong');
        }

        $this->login('admin@admin.com', 'admin');
        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->assertTooManyAttemptsError();

        RateLimiter::clear($key);

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertRedirectedTo('/admin/dashboard');
    }

    /**
     * Test (Fail): the limiter treats differently cased emails as the same key.
     */
    public function testEmailCaseIsNormalisedForTheLimiter()
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->login('ADMIN@ADMIN.COM', 'wrong');
        }

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->assertTooManyAttemptsError();
    }

    /**
     * Test (Fail): different case spellings share the same counter.
     */
    public function testEmailVariantsShareTheLimiterCounter()
    {
        $variants = [
            'admin@admin.com',
            'ADMIN@ADMIN.COM',
            'Admin@Admin.Com',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->login($variants[$attempt % count($variants)], 'wrong');
        }

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->assertTooManyAttemptsError();
    }

    /**
     * Test (Fail): collation-equivalent spellings that match one account share a counter.
     */
    public function testCollationEquivalentSpellingsShareTheLimiterCounter()
    {
        $this->assertNotNull(User::where('email', 'ADMIN@ADMIN.COM')->first());

        $this->login('ADMIN@ADMIN.COM', 'wrong');
        $this->login('admin@admin.com', 'wrong');

        $this->assertSame(2, (int) RateLimiter::attempts($this->limiterKey('admin@admin.com')));

        $this->login('Admin@Admin.Com', 'wrong');
        $this->login('ADMIN@ADMIN.COM', 'wrong');
        $this->login('admin@admin.com', 'wrong');

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->assertTooManyAttemptsError();
    }

    /**
     * Test (Fail): failed attempts from one IP are limited across many email addresses.
     */
    public function testManyDifferentEmailsFromOneIpHitTheIpWideLimit()
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->login('unknown' . $attempt . '@example.com', 'wrong');
            $this->assertRedirectedTo('login');
        }

        $this->assertSame(20, (int) RateLimiter::attempts($this->ipKey()));

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->assertRedirectedTo('login');
        $this->assertTooManyAttemptsError();
    }

    /**
     * Test (Pass): a successful sign-in clears only the account counter.
     */
    public function testSuccessfulLoginDoesNotResetTheIpCounter()
    {
        $this->login('admin@admin.com', 'wrong');
        $this->login('admin@admin.com', 'wrong');
        $this->login('unknown@example.com', 'wrong');

        $this->assertSame(3, (int) RateLimiter::attempts($this->ipKey()));

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertRedirectedTo('/admin/dashboard');
        $this->assertSame(3, (int) RateLimiter::attempts($this->ipKey()));
    }
}
