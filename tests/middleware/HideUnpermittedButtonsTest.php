<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Page;

/**
 * List pages only show the Create, Edit and Delete buttons that the signed-in user is allowed to use.
 */
class HideUnpermittedButtonsTest extends RedminBrowserTestCase
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

        $page = new Page;
        $page->title = 'A page';
        $page->slug = 'a-page';
        $page->content = 'Body';
        $page->private = false;
        $page->save();
    }

    private function group(array $permissions)
    {
        $group = new Group;
        $group->name = 'Limited';
        $group->permissions = json_encode($permissions);
        $group->save();

        return $group;
    }

    private function listAs(array $permissions)
    {
        $this->user->groups()->save($this->group($permissions));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        $response = $this->call('GET', '/admin/pages');
        $response->assertStatus(200);

        return $response->getContent();
    }

    public function testViewOnlyUserSeesNoActionButtons()
    {
        $html = $this->listAs(['admin.pages.view' => 1]);

        $this->assertStringContainsString('A page', $html);
        $this->assertStringNotContainsString('admin/pages/create', $html);
        $this->assertStringNotContainsString('admin/pages/edit/', $html);
        $this->assertStringNotContainsString('admin/pages/delete/', $html);
        $this->assertStringNotContainsString('data-bs-popper-config', $html, 'An empty row menu should not be rendered');
    }

    public function testUpdateOnlyUserSeesEditButOnly()
    {
        $html = $this->listAs(['admin.pages.view' => 1, 'admin.pages.update' => 1]);

        $this->assertStringContainsString('admin/pages/edit/', $html);
        $this->assertStringNotContainsString('admin/pages/delete/', $html);
        $this->assertStringNotContainsString('admin/pages/create', $html);
        $this->assertStringContainsString('data-bs-popper-config', $html);
    }

    public function testCreateAndDeleteButtonsFollowTheirOwnPermission()
    {
        $html = $this->listAs(['admin.pages.view' => 1, 'admin.pages.create' => 1, 'admin.pages.delete' => 1]);

        $this->assertStringContainsString('admin/pages/create', $html);
        $this->assertStringContainsString('admin/pages/delete/', $html);
        $this->assertStringNotContainsString('admin/pages/edit/', $html);
    }

    public function testFullAccessUserSeesEveryButton()
    {
        $html = $this->listAs(['admin' => 1]);

        $this->assertStringContainsString('admin/pages/create', $html);
        $this->assertStringContainsString('admin/pages/edit/', $html);
        $this->assertStringContainsString('admin/pages/delete/', $html);
    }

    public function testForcedDenyHidesAButtonTheGroupOtherwiseAllows()
    {
        $html = $this->listAs(['admin' => 1, 'admin.pages.delete' => -1]);

        $this->assertStringContainsString('admin/pages/edit/', $html);
        $this->assertStringNotContainsString('admin/pages/delete/', $html);
    }

    public function testPurchasesCreateButtonNeedsCreatePermission()
    {
        $this->user->groups()->save($this->group(['admin.purchases.view' => 1]));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        $html = $this->call('GET', '/admin/purchases')->getContent();

        $this->assertStringNotContainsString('admin/purchases/create', $html);
    }

    public function testOrderStatusIsPlainTextForAUserWhoMayNotUpdateOrders()
    {
        $order = new Order;
        $order->user_id = $this->user->id;
        $order->payment_status = 'Pending';
        $order->save();

        $this->user->groups()->save($this->group(['admin.orders.view' => 1]));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        $html = $this->call('GET', '/admin/orders')->getContent();

        $this->assertStringContainsString('Pending', $html);
        $this->assertStringNotContainsString('admin/orders/update/status/', $html);
    }

    public function testDeniedUserActionIsHiddenFromAMenuThatStillHasOtherItems()
    {
        $this->user->groups()->save($this->group(['admin' => 1, 'admin.users.deactivate.view' => -1, 'admin.users.activate.view' => -1]));
        Auth::guard('redminguard')->loginUsingId($this->user->id);

        $html = $this->call('GET', '/admin/users')->getContent();

        $this->assertStringNotContainsString('admin/users/deactivate/', $html);
        $this->assertStringNotContainsString('admin/users/activate/', $html);
        $this->assertStringContainsString('admin/users/edit/', $html);
    }
}
