<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Page;
use Redooor\Redminportal\App\Models\User;

/**
 * The redminsession routes must verify the CSRF token and actions that change data must use POST.
 */
class CsrfProtectionTest extends RedminBrowserTestCase
{
    private $user;

    public function setup(): void
    {
        parent::setup();

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
        Auth::guard('redminguard')->loginUsingId($this->user->id);
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

    public function testCrossSiteRequestWithoutTokenIsRejected()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $this->call('POST', '/admin/pages/delete/' . $page->id, [], [], [], ['HTTP_SEC_FETCH_SITE' => 'cross-site'])
            ->assertStatus(419);

        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function testSameOriginRequestIsAcceptedWithoutToken()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $this->call('POST', '/admin/pages/delete/' . $page->id, [], [], [], ['HTTP_SEC_FETCH_SITE' => 'same-origin'])
            ->assertStatus(302);

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function testStoreWithoutTokenIsRejected()
    {
        $this->app['env'] = 'production';

        $this->call('POST', '/admin/pages/store', $this->pageInput())
            ->assertStatus(419);

        $this->assertDatabaseMissing('pages', ['slug' => 'a-page']);
    }

    public function testStoreWithTokenIsAccepted()
    {
        $this->app['env'] = 'production';

        $response = $this->call('POST', '/admin/pages/store', array_merge(
            ['_token' => $this->token()],
            $this->pageInput()
        ));

        $response->assertStatus(302);
        $this->assertDatabaseHas('pages', ['slug' => 'a-page']);
    }

    public function testDeleteWithoutTokenIsRejected()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $this->call('POST', '/admin/pages/delete/' . $page->id)
            ->assertStatus(419);

        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function testDeleteWithTokenDeletesThePage()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $response = $this->call('POST', '/admin/pages/delete/' . $page->id, [
            '_token' => $this->token(),
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function testGetDeleteIsNotAllowed()
    {
        $this->app['env'] = 'production';
        $page = $this->createPage();

        $this->call('GET', '/admin/pages/delete/' . $page->id)
            ->assertStatus(405);

        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function testLogoutIsPostOnly()
    {
        $this->app['env'] = 'production';

        $this->call('GET', '/logout')->assertStatus(405);
        $this->assertTrue(Auth::guard('redminguard')->check());

        $this->call('POST', '/logout', ['_token' => $this->token()])
            ->assertStatus(302);
        $this->assertFalse(Auth::guard('redminguard')->check());
    }

    public function testGetRoutesDoNotChangeData()
    {
        $offending = [];
        $checked = 0;
        $markers = ['delete', 'imgremove', 'activate', 'deactivate', 'update/', 'logout'];

        foreach (app('router')->getRoutes() as $route) {
            $uri = $route->uri();

            if ($uri !== 'logout' && ! str_starts_with($uri, 'admin/')) {
                continue;
            }

            if (! in_array('GET', $route->methods())) {
                continue;
            }

            $checked++;

            foreach ($markers as $marker) {
                if (str_contains($uri, $marker)) {
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
