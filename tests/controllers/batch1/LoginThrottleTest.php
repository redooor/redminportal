<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Redooor\Redminportal\App\Models\User;

class LoginThrottleTest extends RedminBrowserTestCase
{
    private const CLIENT_IP = '203.0.113.10';

    /**
     * Initialize setup with a clean array cache.
     */
    public function setUp(): void
    {
        parent::setUp();

        Config::set('cache.default', 'array');

        $this->createUser('admin@admin.com', 'admin');

        Auth::guard('redminguard')->logout();
        RateLimiter::clear($this->limiterKey('admin@admin.com'));
    }

    private function limiterKey($email)
    {
        return strtolower($email) . '|' . self::CLIENT_IP;
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
}
