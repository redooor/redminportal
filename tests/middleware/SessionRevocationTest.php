<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

class SessionRevocationTest extends RedminBrowserTestCase
{
    private $user;

    /**
     * Initialize setup with an administrator account.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User;
        $this->user->email = 'admin@admin.com';
        $this->user->password = Hash::make('admin');
        $this->user->first_name = 'System';
        $this->user->last_name = 'Admin';
        $this->user->activated = 1;
        $this->user->save();

        $group = new Group;
        $group->name = 'Admin';
        $group->permissions = json_encode(['admin' => 1]);
        $group->save();

        $this->user->groups()->save($group);
    }

    private function login($email = 'admin@admin.com', $password = 'admin')
    {
        return $this->call('POST', '/login/login', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    /**
     * Test (Fail): a session from before a database password change is logged out.
     */
    public function testPasswordHashChangeInDatabaseEndsSession()
    {
        $user = $this->user;

        $this->login();

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertRedirectedTo('/admin/dashboard');
        $this->assertSessionHas('redmin_password_fingerprint', hash('sha256', $user->password));

        Auth::forgetGuards();

        $user->password = Hash::make('changed-password');
        $user->save();

        $this->call('GET', '/admin/dashboard');

        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }

    /**
     * Test (Fail): a database password change ends a session on the My Account page.
     */
    public function testPasswordHashChangeInDatabaseEndsMyAccountSession()
    {
        $user = $this->user;

        $this->login();

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertSessionHas('redmin_password_fingerprint', hash('sha256', $user->password));

        Auth::forgetGuards();

        $user->password = Hash::make('changed-password');
        $user->save();

        $this->call('GET', '/myaccount');

        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }

    /**
     * Test (Pass): a live session can still open the My Account page.
     */
    public function testMyAccountRemainsAvailableForTheCurrentPassword()
    {
        $this->login();

        Auth::forgetGuards();

        $this->call('GET', '/myaccount');

        $this->assertResponseOk();
        $this->assertSessionHas('redmin_password_fingerprint', hash('sha256', $this->user->password));
    }

    /**
     * Test (Pass): changing your own password keeps the current session logged in.
     */
    public function testOwnPasswordChangeKeepsCurrentSession()
    {
        $this->login();

        $input = array(
            'first_name' => 'System',
            'last_name' => 'Admin',
            'old_password' => 'admin',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        );

        $this->call('POST', 'myaccount/store', $input);

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertRedirectedTo('myaccount');

        $user = $this->user->fresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertSessionHas('redmin_password_fingerprint', hash('sha256', $user->password));

        Auth::forgetGuards();

        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();
    }

    /**
     * Test (Fail): a session started before the change is logged out on its next request.
     */
    public function testSessionStartedBeforePasswordChangeIsLoggedOut()
    {
        $user = $this->user->fresh();
        $oldFingerprint = hash('sha256', $user->password);

        $this->loginRedminUser($user->id);

        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();
        $this->assertSessionHas('redmin_password_fingerprint', $oldFingerprint);

        $user->password = Hash::make('changed-from-another-session');
        $user->save();

        Auth::forgetGuards();

        $this->call('GET', '/admin/dashboard');

        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }

    /**
     * Test (Fail): an authenticated session without a fingerprint must sign in again.
     */
    public function testSessionWithoutFingerprintIsSignedOut()
    {
        $this->loginRedminUser($this->user->id);

        $this->app['session']->forget('redmin_fingerprint_user');
        $this->app['session']->forget('redmin_password_fingerprint');
        Auth::forgetGuards();

        $this->call('GET', '/admin/dashboard');

        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
        $this->response->assertSessionMissing('redmin_password_fingerprint');
    }

    /**
     * Test (Fail): a session fingerprinted for another account must sign in again.
     */
    public function testSessionFingerprintedForAnotherUserIsSignedOut()
    {
        $other = new User;
        $other->email = 'other@example.com';
        $other->password = Hash::make('other-password');
        $other->first_name = 'Other';
        $other->last_name = 'User';
        $other->activated = 1;
        $other->save();

        $this->loginRedminUser($this->user->id);

        $this->app['session']->put('redmin_fingerprint_user', $other->id);
        $this->app['session']->put('redmin_password_fingerprint', hash('sha256', $other->password));
        Auth::forgetGuards();

        $this->call('GET', '/admin/dashboard');

        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }

    /**
     * Test (Fail): a session without a fingerprint is signed out for ajax requests too.
     */
    public function testSessionWithoutFingerprintIsSignedOutForAjax()
    {
        $this->loginRedminUser($this->user->id);

        $this->app['session']->forget('redmin_fingerprint_user');
        $this->app['session']->forget('redmin_password_fingerprint');
        Auth::forgetGuards();

        $this->call('GET', '/admin/dashboard', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->assertResponseOk();
        $this->response->assertViewIs('redminportal::users.notauthorized');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }

    /**
     * Test (Pass): a normal sign-in still opens protected pages.
     */
    public function testNormalLoginStillWorks()
    {
        $this->login();

        $this->assertTrue(Auth::guard('redminguard')->check());
        $this->assertSessionHas('redmin_fingerprint_user', $this->user->id);
        $this->assertSessionHas('redmin_password_fingerprint', hash('sha256', $this->user->password));

        Auth::forgetGuards();

        $this->call('GET', '/admin/dashboard');

        $this->assertResponseOk();
    }

    /**
     * Test (Fail): a password changed before the first request of an old session is not adopted.
     */
    public function testPasswordChangedBeforeFirstRequestAfterUpgradeIsSignedOut()
    {
        $user = $this->user->fresh();

        $this->loginRedminUser($user->id);

        $this->app['session']->forget('redmin_fingerprint_user');
        $this->app['session']->forget('redmin_password_fingerprint');

        $user->password = Hash::make('changed-before-upgrade-request');
        $user->save();

        Auth::forgetGuards();

        $this->call('GET', '/admin/dashboard');

        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }
}
