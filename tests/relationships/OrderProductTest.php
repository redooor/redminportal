<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\DB;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Order;

class OrderProductTest extends BaseRelationshipTest
{
    public function testAddProductsToOrder()
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
        
        $product_1 = $this->createNewModel(new Product, $testcase);
        $order->products()->save($product_1);

        $this->assertTrue($order->products->count() == 1);
        $this->assertTrue($product_1->orders->count() == 1);
        
        $check_orders = DB::table('order_product')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 1);
        
        foreach ($order->products as $product) {
            $this->assertTrueModelAllTestcases($product, $testcase);
        }
        
        // Delete order will delete products relationship
        $order->delete();
        
        $check_orders = DB::table('order_product')->where('order_id', $check_id)->count();
        $this->assertTrue($check_orders == 0);
    }
}
