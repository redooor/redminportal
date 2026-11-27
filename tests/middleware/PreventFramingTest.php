<?php namespace Redooor\Redminportal\Test;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Http\Middleware\PreventFraming;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Package pages must not be embeddable by other origins, but stay embeddable by their own origin.
 */
class PreventFramingTest extends RedminBrowserTestCase
{
    public function tearDown(): void
    {
        $this->app['env'] = 'testing';

        parent::tearDown();
    }

    private function signIn()
    {
        $user = new User;
        $user->email = 'frame@test.com';
        $user->password = Hash::make('test');
        $user->first_name = 'Frame';
        $user->last_name = 'Tester';
        $user->activated = 1;
        $user->save();

        $group = new Group;
        $group->name = 'Admin';
        $group->permissions = json_encode(['admin' => 1]);
        $group->save();

        $user->groups()->save($group);
        $this->loginRedminUser($user->id);
    }

    private function assertFrameHeaders($response)
    {
        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertContains("frame-ancestors 'self'", $response->headers->all('Content-Security-Policy'));
    }

    public function testLoginPageSendsFrameHeaders()
    {
        $this->assertFrameHeaders($this->call('GET', '/login'));
    }

    public function testAdminPageSendsFrameHeaders()
    {
        $this->signIn();

        $response = $this->call('GET', '/admin/dashboard');

        $response->assertStatus(200);
        $this->assertFrameHeaders($response);
    }

    public function testRedirectAndCsrfRejectionSendFrameHeaders()
    {
        $this->signIn();
        $this->app['env'] = 'production';

        $this->assertFrameHeaders($this->call('GET', '/admin'));

        $rejected = $this->call('POST', '/admin/pages/store', [], [], [], ['HTTP_SEC_FETCH_SITE' => 'cross-site']);

        $rejected->assertStatus(419);
        $this->assertFrameHeaders($rejected);
    }

    public function testHostFrameOptionsAreKept()
    {
        $response = (new PreventFraming)->handle(Request::create('/x'), function () {
            $response = new Response('ok');
            $response->headers->set('X-Frame-Options', 'DENY');

            return $response;
        });

        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertFalse($response->headers->has('Content-Security-Policy'));
    }

    public function testHostContentSecurityPolicyIsKeptAndExtended()
    {
        $response = (new PreventFraming)->handle(Request::create('/x'), function () {
            $response = new Response('ok');
            $response->headers->set('Content-Security-Policy', "default-src 'self'");

            return $response;
        });

        $policies = $response->headers->all('Content-Security-Policy');

        $this->assertContains("default-src 'self'", $policies);
        $this->assertContains("frame-ancestors 'self'", $policies);
    }
}
