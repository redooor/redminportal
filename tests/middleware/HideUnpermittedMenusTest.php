<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\User;

/**
 * The sidebar and breadcrumb only list the menus that the signed-in user is allowed to view.
 */
class HideUnpermittedMenusTest extends RedminBrowserTestCase
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
    }

    private function group(array $permissions)
    {
        $group = new Group;
        $group->name = 'Limited';
        $group->permissions = json_encode($permissions);
        $group->save();

        return $group;
    }

    private function dashboardAs(array $permissions)
    {
        $this->user->groups()->save($this->group($permissions));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        $response = $this->call('GET', '/admin/dashboard');
        $response->assertStatus(200);

        return $response->getContent();
    }

    private function sidebar($html)
    {
        preg_match('/<div id="sidebar".*?<div id="sidebar-overlay"/s', $html, $matches);

        return $matches[0] ?? '';
    }

    public function testLimitedUserOnlySeesPermittedMenus()
    {
        $html = $this->dashboardAs([
            'admin.dashboard.view' => 1,
            'admin.pages.view' => 1,
            'admin.categories.view' => 1,
        ]);
        $sidebar = $this->sidebar($html);

        $this->assertStringContainsString('href="' . url('admin/dashboard') . '"', $sidebar);
        $this->assertStringContainsString('href="' . url('admin/pages') . '"', $sidebar);
        $this->assertStringContainsString('href="' . url('admin/categories') . '"', $sidebar);
        foreach (['posts', 'orders', 'users', 'groups', 'products', 'medias'] as $name) {
            $this->assertStringNotContainsString('href="' . url('admin/' . $name) . '"', $sidebar, $name);
        }
    }

    public function testSectionHeadersWithNothingUnderThemAreHidden()
    {
        $html = $this->dashboardAs([
            'admin.dashboard.view' => 1,
            'admin.pages.view' => 1,
        ]);
        $sidebar = $this->sidebar($html);

        $this->assertStringContainsString(trans('redminportal::menus.contents_management'), $sidebar);
        $this->assertStringNotContainsString(trans('redminportal::menus.store'), $sidebar);
        $this->assertStringNotContainsString(trans('redminportal::menus.users_management'), $sidebar);
    }

    public function testNestedGroupsFollowTheirChildren()
    {
        $html = $this->dashboardAs([
            'admin.dashboard.view' => 1,
            'admin.medias.view' => 1,
        ]);
        $sidebar = $this->sidebar($html);

        $this->assertStringContainsString(trans('redminportal::menus.store'), $sidebar);
        $this->assertStringContainsString(trans('redminportal::menus.digital'), $sidebar);
        $this->assertStringContainsString('href="' . url('admin/medias') . '"', $sidebar);
        $this->assertStringNotContainsString(trans('redminportal::menus.physical'), $sidebar);
    }

    public function testDeniedMenuIsHiddenEvenWhenGroupAllowsTheRest()
    {
        $html = $this->dashboardAs(['admin' => 1, 'admin.orders.view' => -1]);
        $sidebar = $this->sidebar($html);

        $this->assertStringNotContainsString('href="' . url('admin/orders') . '"', $sidebar);
        $this->assertStringContainsString('href="' . url('admin/products') . '"', $sidebar);
    }

    public function testFullAccessUserSeesEveryMenu()
    {
        $html = $this->dashboardAs(['admin' => 1]);
        $sidebar = $this->sidebar($html);

        foreach (config('redminportal::menu') as $menu) {
            $this->assertStringContainsString(trans('redminportal::menus.' . $menu['name']), $sidebar);
        }
        foreach (['pages', 'posts', 'orders', 'users', 'groups', 'products', 'medias', 'mailinglists'] as $name) {
            $this->assertStringContainsString('href="' . url('admin/' . $name) . '"', $sidebar, $name);
        }
    }

    public function testBreadcrumbDashboardLinkNeedsDashboardPermission()
    {
        $html = $this->dashboardAs(['admin.dashboard.view' => 1]);
        $this->assertStringContainsString('class="nav-link" href="' . url('admin/dashboard') . '"', $html);
    }

    public function testBreadcrumbDashboardLinkIsHiddenWithoutDashboardPermission()
    {
        $this->user->groups()->save($this->group(['admin.pages.view' => 1]));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        $response = $this->call('GET', '/admin/pages');
        $response->assertStatus(200);

        $this->assertStringNotContainsString('class="nav-link" href="' . url('admin/dashboard') . '"', $response->getContent());
    }

    public function testDeniedParentKeepsPermittedChildrenAsPlainHeader()
    {
        $this->user->groups()->save($this->group(['admin.pages.view' => -1, 'admin.pages.update' => 1]));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        $menus = [[
            'name' => 'pages',
            'path' => 'admin/pages',
            'hide' => false,
            'children' => [['name' => 'posts', 'path' => 'admin/pages/edit/5', 'hide' => false]],
        ]];
        $html = (string) (new \Redooor\Redminportal\App\UI\Html)->printMenu($menus);

        $this->assertStringNotContainsString('href="' . url('admin/pages') . '"', $html);
        $this->assertStringContainsString('href="' . url('admin/pages/edit/5') . '"', $html);
    }
}
