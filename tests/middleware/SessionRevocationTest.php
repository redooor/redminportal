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

        Auth::guard('redminguard')->loginUsingId($user->id);

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
}
