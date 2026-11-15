<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Pricelist;

class ModuleDeletePurchasedTest extends BaseControllerTest
{
    use CharacterizationSupport;

    private $membership;

    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/modules';
        $this->viewhas = array(
            'singular' => 'module',
            'plural' => 'models'
        );
        $this->input = array(
            'create' => array(
                'name' => 'This is title',
                'short_description' => 'This is body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001'
            ),
            'edit' => array(
                'id' => 1,
                'name' => 'This is title',
                'short_description' => 'This is body',
                'category_id' => 1,
                'sku' => 'UNIQUESKU001'
            )
        );

        $this->membership = new Membership;
        $this->membership->name = 'Gold';
        $this->membership->rank = 1;
        $this->membership->save();
    }

    private function createModule($name, $sku)
    {
        $module = new Module;
        $module->name = $name;
        $module->sku = $sku;
        $module->short_description = 'Short';
        $module->category_id = 1;
        $module->save();

        return $module;
    }

    private function createPricelist($module, $price)
    {
        $pricelist = new Pricelist;
        $pricelist->module_id = $module->id;
        $pricelist->membership_id = $this->membership->id;
        $pricelist->price = $price;
        $pricelist->save();

        return $pricelist;
    }

    public function testPurchasedModuleCannotBeDeletedWhenIdsDiverge()
    {
        $module = $this->createModule('Purchased module', 'PURCHASED-SKU');
        $otherModule = $this->createModule('Other module', 'OTHER-SKU');
        $pricelist = $this->createPricelist($module, 10);
        $otherPricelist = $this->createPricelist($otherModule, 20);

        $order = new Order;
        $order->user_id = 1;
        $order->paid = 20;
        $order->transaction_id = 'OTHER-TX';
        $order->payment_status = 'Completed';
        $order->save();

        // The target pivot row gets id 2 while the only order is id 1.
        $order->pricelists()->attach($otherPricelist->id);
        $order->pricelists()->attach($pricelist->id);

        $pivot = DB::table('order_pricelist')
            ->where('order_id', $order->id)
            ->where('pricelist_id', $pricelist->id)
            ->first();

        $this->assertNotSame((int) $order->id, (int) $pivot->id);
        $this->assertNull(Order::find($pivot->id));

        $this->call('POST', '/admin/modules/delete/' . $module->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/modules'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertErrorMessage(
            'deleteError',
            'This module has been purchased before. You cannot delete it. Please disable it instead.'
        );
        $this->assertNotNull(Module::find($module->id));
    }

    public function testUnpurchasedModuleCanBeDeleted()
    {
        $module = $this->createModule('Unpurchased module', 'UNPURCHASED-SKU');

        $this->call('POST', '/admin/modules/delete/' . $module->id, array(), array(), array(), array(
            'HTTP_REFERER' => '/admin/modules'
        ));

        $this->assertRedirectedTo('/admin/modules');
        $this->assertNull(Module::find($module->id));
    }
}
