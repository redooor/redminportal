<?php namespace Redooor\Redminportal\Test;

use DB;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Order;

class OrderPricelistTest extends BaseRelationshipTest
{
    public function testAddPricelistsToOrder()
    {
        $order = $this->createNewModel(new Order, array(
            'user_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
        ));
        $check_id = $order->id;
        
        $testcase = array(
            'price' => 0,
            'module_id' => 1,
            'membership_id' => 1,
            'active' => true
        );
        
        $pricelist_1 = $this->createNewModel(new Pricelist, $testcase);
        $order->pricelists()->save($pricelist_1);

        $this->assertTrue($order->pricelists->count() == 1);
        $this->assertTrue($pricelist_1->orders->count() == 1);
        
        $check_orders = DB::table('order_pricelist')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 1);
        
        foreach ($order->pricelists as $pricelist) {
            $this->assertTrueModelAllTestcases($pricelist, $testcase);
        }
        
        // Delete order will delete pricelists relationship
        $order->delete();
        
        $check_orders = DB::table('order_pricelist')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 0);
    }
}
