<?php namespace Redooor\Redminportal\Test;

use DateTime;
use Redooor\Redminportal\App\Models\Coupon;
use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;

class OrderGetDiscountTest extends BaseRelationshipTest
{
    protected $order;
    protected $product_1;
    protected $product_2;
    protected $coupon;
    
    /**
     * Setup initial data for use in tests
     */
    public function setup()
    {
        parent::setup();
        
        $this->order = $this->createNewModel(new Order, array(
            'user_id' => 1,
            'paid' => 10.99,
            'transaction_id' => 'UK12345YZ',
            'payment_status' => 'Completed'
        ));
        
        $this->product_1 = $this->createNewModel(new Product, array(
            'name' => 'This is the title',
            'sku' => 'UNIQUESKU001',
            'short_description' => 'This is the body',
            'category_id' => 1,
            'price' => 10.00,
            'active' => true
        ));
        
        $this->product_2 = $this->createNewModel(new Product, array(
            'name' => 'This is another title',
            'sku' => 'UNIQUESKU002',
            'short_description' => 'This is another body',
            'category_id' => 1,
            'price' => 90.00,
            'active' => true
        ));
        
        $this->coupon = $this->createNewModel(new Coupon, array(
            'code' => 'CNY2015',
            'description' => "Say something CNY2015",
            'amount' => 10,
            'is_percent' => true,
            'start_date' => new DateTime('now'),
            'end_date' => new DateTime('now'),
            'max_spent' => 200,
            'min_spent' => 1,
            'usage_limit_per_coupon' => 100,
            'usage_limit_per_user' => 1,
            'multiple_coupons' => true,
            'exclude_sale_item' => false,
            'usage_limit_per_coupon_count' => 0
        ));
    }
    
    public function testGetDiscountOfOrderWithProduct1()
    {
        // Link product 1 to coupon only
        $this->coupon->products()->save($this->product_1);
        $this->assertTrue($this->coupon->products->count() == 1);
        
        // Link product 1 and 2 to order
        $this->order->products()->save($this->product_1);
        $this->order->products()->save($this->product_2);
        $this->assertTrue($this->order->products->count() == 2);
        
        // Link coupon to order
        $this->order->coupons()->save($this->coupon);
        $this->assertTrue($this->order->coupons->count() == 1);
        
        // Check total price of order is same as product
        $this->assertTrue($this->order->getTotalprice() == 100.00);
        
        // Check total discount of order is 10% of product 1 only
        $this->assertTrue($this->order->getTotaldiscount() == 1.00);
    }
    
    public function testGetDiscountOfOrderWithProduct1and2()
    {
        // Link product 1 and 2 to coupon
        $this->coupon->products()->save($this->product_1);
        $this->coupon->products()->save($this->product_2);
        $this->assertTrue($this->coupon->products->count() == 2);
        
        // Link product 1 and 2 to order
        $this->order->products()->save($this->product_1);
        $this->order->products()->save($this->product_2);
        $this->assertTrue($this->order->products->count() == 2);
        
        // Link coupon to order
        $this->order->coupons()->save($this->coupon);
        $this->assertTrue($this->order->coupons->count() == 1);
        
        // Check total price of order is same as product
        $this->assertTrue($this->order->getTotalprice() == 100.00);
        
        // Check total discount of order is 10% of product 1 only
        $this->assertTrue($this->order->getTotaldiscount() == 10.00);
        
        // Check GetDiscounts() return correct value
        foreach ($this->order->getDiscounts() as $item) {
            if ($item['name'] == 'This is the title') {
                $this->assertTrue($item['price'] == $this->product_1->price);
                $this->assertTrue($item['value'] == 1.00);
            } else {
                $this->assertTrue($item['price'] == $this->product_2->price);
                $this->assertTrue($item['value'] == 9.00);
            }
        }
    }
}
