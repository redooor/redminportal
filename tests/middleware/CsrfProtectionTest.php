<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\User;

/**
 * The redminsession routes must verify the CSRF token and actions that change data must use POST.
 */
class CsrfProtectionTest extends RedminBrowserTestCase
{
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User;
        $this->user->email = 'peter@test.com';
        $this->user->password = Hash::make('test');
        $this->user->first_name = 'Peter';
        $this->user->last_name = 'Tester';
        $this->user->activated = 1;
        $this->user->save();

        $group = new Group;
        $group->name = 'Admin';
        $group->permissions = json_encode(['admin' => 1]);
        $group->save();

        $this->user->groups()->save($group);
        $this->loginRedminUser($this->user->id);
    }

    public function tearDown(): void
    {
        $this->app['env'] = 'testing';

        parent::tearDown();
    }

    private function token()
    {
        $this->app['session']->start();

        return $this->app['session']->token();
    }

    private function createPage()
    {
        $page = new Page;
        $page->title = 'A page';
        $page->slug = 'a-page';
        $page->content = 'Body';
        $page->private = false;
        $page->save();

        return $page;
    }

    private function pageInput()
    {
        return [
            'title' => 'A page',
            'slug' => 'a-page',
            'content' => 'Body',
        ];
    }

    public function testStoreWithoutTokenIsRejected()
    {
        $this->app['env'] = 'production';

        $this->call('POST', '/admin/pages/store', $this->pageInput());
        $this->assertResponseStatus(419);
        $this->notSeeInDatabase('pages', ['slug' => 'a-page']);
    }

    public function testStoreWithTokenIsAccepted()
    {
        $this->app['env'] = 'production';

        $this->call('POST', '/admin/pages/store', array_merge(
            ['_token' => $this->token()],
            $this->pageInput()
        ));

        $this->assertResponseStatus(302);
        $this->seeInDatabase('pages', ['slug' => 'a-page']);
    }

    public function testSignedOutAjaxRequestGetsTheNotAuthorizedPage()
    {
        $this->app['env'] = 'production';
        Auth::guard('redminguard')->logout();

        $this->call('GET', '/admin/pages', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->assertResponseStatus(200);
        $this->assertStringContainsString(
            e(Lang::get('redminportal::messages.not_authorized_to_view_this_page')),
            $this->response->getContent()
        );
    }

    public function testAdminCookiesAreEncryptedLikeTheWebGroup()
    {
        $group = $this->app['router']->getMiddlewareGroups()['redminsession'];

        $this->assertSame(\Illuminate\Cookie\Middleware\EncryptCookies::class, $group[0]);

        // The array session driver sets no session cookie in tests; the token cookie goes through the same middleware
        $this->call('GET', '/login');
        $cookie = null;

        foreach ($this->response->headers->getCookies() as $item) {
            if ($item->getName() == 'XSRF-TOKEN') {
                $cookie = $item;
            }
        }

        $this->assertNotNull($cookie, 'The login page sets no token cookie');
        $this->assertNotSame(40, strlen($cookie->getValue()), 'The cookie is not encrypted');
        $this->assertNotFalse(@$this->app['encrypter']->decrypt($cookie->getValue(), false));
    }

    public function testDeleteWithoutTokenIsRejected()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $this->call('POST', '/admin/pages/delete/' . $page->id);
        $this->assertResponseStatus(419);
        $this->seeInDatabase('pages', ['id' => $page->id]);
    }

    public function testDeleteWithTokenDeletesThePage()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $this->call('POST', '/admin/pages/delete/' . $page->id, [
            '_token' => $this->token(),
        ]);

        $this->assertResponseStatus(302);
        $this->notSeeInDatabase('pages', ['id' => $page->id]);
    }

    public function testGetDeleteIsNotAllowed()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $this->call('GET', '/admin/pages/delete/' . $page->id);
        $this->assertResponseStatus(405);
        $this->seeInDatabase('pages', ['id' => $page->id]);
    }

    public function testLogoutIsPostOnly()
    {
        $this->app['env'] = 'production';

        $this->call('GET', '/logout');
        $this->assertResponseStatus(405);
        $this->assertTrue(Auth::guard('redminguard')->check());

        $this->call('POST', '/logout', ['_token' => $this->token()]);
        $this->assertResponseStatus(302);
        $this->assertFalse(Auth::guard('redminguard')->check());
    }

    public function testGetRoutesDoNotChangeData()
    {
        $offending = [];
        $checked = 0;
        $markers = ['delete', 'imgremove', 'activate', 'deactivate', 'update/', 'logout', 'duration'];

        foreach (app('router')->getRoutes() as $route) {
            $uri = $route->uri();

            if ($uri !== 'logout' && strpos($uri, 'admin/') !== 0) {
                continue;
            }

            if (! in_array('GET', $route->methods())) {
                continue;
            }

            $checked++;

            foreach ($markers as $marker) {
                if (strpos($uri, $marker) !== false) {
                    $offending[] = $uri;
                    break;
                }
            }
        }

        $offending = array_values(array_unique($offending));

        $this->assertGreaterThan(0, $checked, 'No admin GET routes were found');
        $this->assertEmpty(
            $offending,
            'These GET routes still change data: ' . implode(', ', $offending)
        );
    }
}
