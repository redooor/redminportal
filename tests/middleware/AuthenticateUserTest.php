<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\User;

class AuthenticateUserTest extends RedminBrowserTestCase
{
    private function createUser($activated = 1)
    {
        $user = new User;
        $user->email = 'redminuser@test.com';
        $user->password = Hash::make('test');
        $user->first_name = 'Redmin';
        $user->last_name = 'User';
        $user->activated = $activated;
        $user->save();

        return $user;
    }

    public function testGuestIsRedirectedToLogin()
    {
        $this->call('GET', '/api/tag');

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('login');
    }

    public function testAjaxGuestGetsNotAuthorizedView()
    {
        $response = $this->call('GET', '/api/tag', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $this->assertResponseOk();
        $this->assertStringContainsString('alert alert-danger', $response->getContent());
    }

    public function testSignedInActiveUserIsAllowed()
    {
        $user = $this->createUser();

        Auth::guard('redminguard')->loginUsingId($user->id);

        $this->call('GET', '/api/tag');

        $this->assertResponseOk();
    }

    public function testSignedInDeactivatedUserIsDeniedAndLoggedOut()
    {
        $user = $this->createUser(0);

        Auth::guard('redminguard')->loginUsingId($user->id);

        $this->call('GET', '/api/tag');

        $this->assertResponseStatus(302);
        $this->assertRedirectedTo('login');
        $this->assertTrue(Auth::guard('redminguard')->guest());
    }
}
