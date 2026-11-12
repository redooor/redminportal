<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Group;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\User;

class PurchaseControllerTest extends BaseControllerTest
{
    /**
     * Prepare data for tests
     */
    public function prepare()
    {
        $this->page = '/admin/purchases';
        $this->viewhas = array(
            'singular' => 'purchase',
            'plural' => 'purchases'
        );
        $this->input = array(
            'create' => array(
                'pricelist_id'   => 1,
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
            ),
            'edit' => array(
                'id'   => 1,
                'pricelist_id'   => 1,
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
            )
        );
    }
    
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();

        $this->prepare();
        
        // Add membership
        $membership = new Membership;
        $membership->name = "Gold";
        $membership->rank = 5;
        $membership->save();
        
        // Add module
        $module = new Module;
        $module->name = 'This is title';
        $module->sku = 'UNIQUESKU001';
        $module->short_description = 'This is body';
        $module->long_description = 'This is long body';
        $module->featured = true;
        $module->active = true;
        $module->category_id = 1;
        $module->save();
                
        // Create a new Pricelist for use later
        $pricelist = new Pricelist;
        $pricelist->price = 0;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->save();
    }

    /**
     * Create a user in a group with the supplied permissions and log in.
     */
    private function loginWithPermissions(array $permissions)
    {
        $group = new Group;
        $group->name = 'Purchases ' . uniqid();
        $group->permissions = json_encode($permissions);
        $group->save();

        $user = new User;
        $user->email = uniqid('purchases') . '@example.com';
        $user->password = Hash::make('secret');
        $user->first_name = 'Purchases';
        $user->last_name = 'Tester';
        $user->activated = 1;
        $user->save();
        $user->groups()->save($group);

        Auth::guard('redminguard')->loginUsingId($user->id);
    }

    /**
     * Create a purchase order that can be shown and deleted.
     */
    private function createPurchaseOrder()
    {
        $order = new Order;
        $order->user_id = 1;
        $order->paid = 10.99;
        $order->transaction_id = 'PURCHASE-ORDER-001';
        $order->payment_status = 'Completed';
        $order->save();

        $order->pricelists()->attach(1);

        return $order;
    }

    /**
     * Overwrite base functions, no edit for Purchase
     */
    public function testEditPass()
    {
        $this->call('GET', '/admin/purchases/edit/1');
        $this->assertRedirectedTo('/admin/purchases');
    }
    
    /**
     * Overwrite base functions, no edit for Purchase
     */
    public function testStoreEdit()
    {
        $this->call('GET', '/admin/purchases/edit/1');
        $this->assertRedirectedTo('/admin/purchases');
    }

    public function testStoreCreateFailedNoPricelist()
    {
        $input = array(
            'pricelist_id'   => 2,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases/create');
    }
    
    public function testStoreCreateFailedNoSuchUser()
    {
        $input = array(
            'pricelist_id'   => 2,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'nosuchuser@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases/create');
    }

    /**
     * Test (Fail): delete a purchase without permission to delete orders
     */
    public function testDeleteRefusedWithoutOrdersDeletePermission()
    {
        $order = $this->createPurchaseOrder();

        $this->loginWithPermissions([
            'admin.purchases.view' => 1,
            'admin.purchases.delete' => 1,
        ]);

        $this->call('POST', '/admin/purchases/delete/' . $order->id);

        $this->assertResponseStatus(302);
        $this->assertSessionHasErrors('deleteError');
        $this->assertNotNull(Order::find($order->id));
    }

    /**
     * Test (Pass): delete a purchase with permission to delete orders
     */
    public function testDeleteAllowedWithOrdersDeletePermission()
    {
        $order = $this->createPurchaseOrder();

        $this->loginWithPermissions([
            'admin.purchases.view' => 1,
            'admin.purchases.delete' => 1,
            'admin.orders.delete' => 1,
        ]);

        $this->call('POST', '/admin/purchases/delete/' . $order->id);

        $this->assertResponseStatus(302);
        $this->assertNull(Order::find($order->id));
    }

    /**
     * Test (Pass): the delete button is shown only when both delete permissions are held
     */
    public function testDeleteButtonNeedsPurchasesAndOrdersDeletePermissions()
    {
        $order = $this->createPurchaseOrder();

        $this->loginWithPermissions([
            'admin.purchases.view' => 1,
            'admin.purchases.delete' => 1,
        ]);

        $this->call('GET', '/admin/purchases');

        $this->assertResponseOk();
        $this->assertStringNotContainsString('admin/purchases/delete/' . $order->id, $this->response->getContent());

        $this->loginWithPermissions([
            'admin.purchases.view' => 1,
            'admin.purchases.delete' => 1,
            'admin.orders.delete' => 1,
        ]);

        $this->call('GET', '/admin/purchases');

        $this->assertResponseOk();
        $this->assertStringContainsString('admin/purchases/delete/' . $order->id, $this->response->getContent());
    }
}
