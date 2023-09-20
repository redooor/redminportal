<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Order;

class BundleOrderTest extends BaseRelationshipTest
{
    public function testAddBundlesToOrder()
    {
        $order = $this->createNewModel(new Order, array(
            'user_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
        ));
        $check_id = $order->id;
        
        $testcase = array(
            'name' => 'This is the title',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'active' => true
        );
        
        $bundle_1 = $this->createNewModel(new Bundle, $testcase);
        $order->bundles()->save($bundle_1);

        $this->assertTrue($order->bundles->count() == 1);
        $this->assertTrue($bundle_1->orders->count() == 1);
        
        $check_orders = DB::table('bundle_order')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 1);
        
        foreach ($order->bundles as $bundle) {
            $this->assertTrueModelAllTestcases($bundle, $testcase);
        }
        
        // Delete order will delete bundles relationship
        $order->delete();
        
        $check_orders = DB::table('bundle_order')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 0);
    }
}
