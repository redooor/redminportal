<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\User;

class LoginThrottleTest extends RedminBrowserTestCase
{
    private $clientIp = '203.0.113.10';
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
        $this->limiter()->clear('user:' . $this->admin->id . '|' . $this->clientIp);
        $this->limiter()->clear($this->emailKey('admin@admin.com'));
        $this->limiter()->clear($this->ipKey());
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

    private function limiter()
    {
        return app(\Illuminate\Cache\RateLimiter::class);
    }

    private function limiterKey($email)
    {
        $user = User::where('email', $email)->first();

        if ($user != null) {
            return 'user:' . $user->id . '|' . $this->clientIp;
        }

        return $this->emailKey($email);
    }

    private function emailKey($email)
    {
        return strtolower(trim($email)) . '|' . $this->clientIp;
    }

    private function ipKey()
    {
        return 'ip:' . $this->clientIp;
    }

    private function login($email, $password)
    {
        return $this->call('POST', '/login/login', array(
            'email' => $email,
            'password' => $password,
        ), array(), array(), array('REMOTE_ADDR' => $this->clientIp));
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

        $this->assertRegExp(
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

        $this->assertSame(2, (int) $this->limiter()->attempts($key));

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertSame(0, (int) $this->limiter()->attempts($key));
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

        $this->limiter()->clear($key);

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
        $variants = array(
            'admin@admin.com',
            'ADMIN@ADMIN.COM',
            'Admin@Admin.Com',
        );

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

        $this->assertSame(2, (int) $this->limiter()->attempts($this->limiterKey('admin@admin.com')));

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

        $this->assertSame(20, (int) $this->limiter()->attempts($this->ipKey()));

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

        $this->assertSame(3, (int) $this->limiter()->attempts($this->ipKey()));

        $this->login('admin@admin.com', 'admin');

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertRedirectedTo('/admin/dashboard');
        $this->assertSame(3, (int) $this->limiter()->attempts($this->ipKey()));
    }
}
