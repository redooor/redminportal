<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\User;

class PurchaseExistingCheckTest extends BaseControllerTest
{
    use CharacterizationSupport;

    private $pricelist;
    private $secondPricelist;
    private $buyer;
    private $otherBuyer;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/purchases';
        $this->viewhas = array(
            'singular' => 'purchase',
            'plural' => 'purchases'
        );

        $module = new Module;
        $module->name = 'Existing Check Module';
        $module->sku = 'EXISTING-CHECK-SKU';
        $module->short_description = 'Short';
        $module->long_description = 'Long';
        $module->featured = false;
        $module->active = true;
        $module->category_id = 1;
        $module->save();

        $membership = new Membership;
        $membership->name = 'Gold';
        $membership->rank = 5;
        $membership->save();

        $secondMembership = new Membership;
        $secondMembership->name = 'Silver';
        $secondMembership->rank = 1;
        $secondMembership->save();

        $this->pricelist = new Pricelist;
        $this->pricelist->price = 10;
        $this->pricelist->module_id = $module->id;
        $this->pricelist->membership_id = $membership->id;
        $this->pricelist->save();

        $this->secondPricelist = new Pricelist;
        $this->secondPricelist->price = 20;
        $this->secondPricelist->module_id = $module->id;
        $this->secondPricelist->membership_id = $secondMembership->id;
        $this->secondPricelist->save();

        $this->buyer = $this->createUser('buyer@example.com');
        $this->otherBuyer = $this->createUser('other-buyer@example.com');

        $this->input = array(
            'create' => $this->validInput(),
            'edit' => $this->validInput(null, array('id' => '1'))
        );
    }

    private function createUser($email)
    {
        $user = new User;
        $user->email = $email;
        $user->password = Hash::make('secret');
        $user->first_name = 'Purchase';
        $user->last_name = 'Tester';
        $user->activated = 1;
        $user->permissions = json_encode(array());
        $user->save();

        return $user;
    }

    private function validInput($pricelist = null, array $overrides = array())
    {
        $pricelist = ($pricelist === null ? $this->pricelist : $pricelist);

        return array_merge(array(
            'pricelist_id' => $pricelist->id,
            'transaction_id' => 'TX-EXISTING-CHECK',
            'payment_status' => 'Completed',
            'paid' => '9.99',
            'email' => $this->buyer->email
        ), $overrides);
    }

    /**
     * The target order is id 1 while its pivot row is id 2, and the order at
     * pivot id 2 belongs to a different buyer. The old orders.id = pivot.id
     * join therefore looked at the wrong buyer.
     */
    private function createDivergedPurchase()
    {
        $order = new Order;
        $order->user_id = $this->buyer->id;
        $order->paid = 9.99;
        $order->transaction_id = 'EXISTING-TX';
        $order->payment_status = 'Completed';
        $order->save();

        $otherOrder = new Order;
        $otherOrder->user_id = $this->otherBuyer->id;
        $otherOrder->paid = 19.99;
        $otherOrder->transaction_id = 'OTHER-TX';
        $otherOrder->payment_status = 'Completed';
        $otherOrder->save();

        $otherOrder->pricelists()->attach($this->secondPricelist->id);
        $order->pricelists()->attach($this->pricelist->id);

        $pivot = DB::table('order_pricelist')
            ->where('order_id', $order->id)
            ->where('pricelist_id', $this->pricelist->id)
            ->first();

        $this->assertNotSame((int) $order->id, (int) $pivot->id);
        $this->assertSame($this->otherBuyer->id, (int) Order::find($pivot->id)->user_id);

        return $order;
    }

    /**
     * Overwrite base function, edit is disabled for purchases
     */
    public function testEditPass()
    {
        $this->call('GET', '/admin/purchases/edit/1');

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSessionHasErrors('editError');
    }

    /**
     * Overwrite base function, a posted id still creates a new purchase instead of updating
     */
    public function testStoreEdit()
    {
        $this->postStore($this->validInput(null, array('id' => '1')));

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSame(1, Order::count());
    }

    public function testRepostingSamePurchaseIsRejectedWhenIdsDiverge()
    {
        $this->createDivergedPurchase();

        $this->postStore($this->validInput());

        $this->assertRedirectedTo('/admin/purchases/create');
        $this->assertErrorMessage(
            'userpricelistError',
            'buyer@example.com has already purchased Existing Check Module (Gold).'
        );
        $this->assertSame(2, Order::count());
        $this->assertSame(
            1,
            DB::table('order_pricelist')->where('pricelist_id', $this->pricelist->id)->count()
        );
    }

    public function testDifferentPricelistIsAcceptedWhenIdsDiverge()
    {
        $this->createDivergedPurchase();

        $this->postStore($this->validInput($this->secondPricelist));

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSame(3, Order::count());
        $this->assertSame(
            2,
            DB::table('order_pricelist')->where('pricelist_id', $this->secondPricelist->id)->count()
        );
    }
}
