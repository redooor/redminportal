<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;
use Redooor\Redminportal\App\Models\Bundle;
use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Coupon;

class OrderControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest, TraitSearcherControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/orders';
        $viewhas = array(
            'singular' => 'order',
            'plural' => 'models'
        );
        $input = array(
            'create' => array(
                'selected_products' => array('{"id":"1","name":"This is title","quantity":"1"}'),
                'selected_bundles' => array('{"id":"1","name":"This is the title","quantity":"1"}'),
                'pricelist_id' => array(1),
                'coupon_id' => array(1),
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
            ),
            'edit' => array(
                'id'   => 1,
                'selected_products' => array('{"id":"1","name":"This is title","quantity":"1"}'),
                'selected_bundles' => array('{"id":"1","name":"This is the title","quantity":"1"}'),
                'pricelist_id' => array(1),
                'coupon_id' => array(1),
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
            )
        );
        
        // For testing sort
        $this->sortBy = 'created_at';
        
        // For testing search
        $this->searchable_field = 'payment_status';
        $this->search_text = 'Completed';
        
        parent::__construct($page, $viewhas, $input);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }
    
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();
        
        // Add product
        $product = new Product;
        $product->name = 'This is title';
        $product->sku = 'UNIQUESKU001';
        $product->short_description = 'This is body';
        $product->active = true;
        $product->category_id = 1;
        $product->save();
        
        $bundle = new Bundle;
        $bundle->name = 'This is the title';
        $bundle->sku = 'UNIQUESKU001';
        $bundle->short_description = 'This is the body';
        $bundle->category_id = 1;
        $bundle->active = true;
        
        $coupon = new Coupon;
        $coupon->code = 'ABC123';
        $coupon->description = 'This is a description';
        $coupon->amount = 10.99;
        $coupon->is_percent = true;
        $coupon->start_date = '02/05/2016 5:39 PM';
        $coupon->end_date = '02/05/2016 5:39 PM';
        $coupon->max_spent = 200.99;
        $coupon->min_spent = 199.88;
        $coupon->usage_limit_per_coupon = 10;
        $coupon->usage_limit_per_user = 1;
        $coupon->multiple_coupons = true;
        $coupon->exclude_sale_item = true;
        $coupon->usage_limit_per_coupon_count = 0;
        
        $pricelist = new Pricelist;
        $pricelist->price = 0;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->active = true;
    }
    
    /**
     * Overwrite base functions, no edit for Order
     */
    public function testEditPass()
    {
        return;
    }
    
    /**
     * Overwrite base functions, no edit for Order
     */
    public function testStoreEdit()
    {
        return;
    }

    public function testStoreCreateFailedNoProduct()
    {
        $input = array(
            'selected_products' => array('{"id":"2","name":"This is title","quantity":"1"}'),
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
    }
    
    public function testStoreCreateFailedNoBundle()
    {
        $input = array(
            'selected_bundles' => array('{"id":"2","name":"This is title","quantity":"1"}'),
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
    }
    
    public function testStoreCreateFailedNoPricelist()
    {
        $input = array(
            'pricelist_id' => array(2),
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
    }
    
    public function testStoreCreateFailedNoSuchUser()
    {
        $input = array(
            'selected_products' => array('{"id":"1","name":"This is title","quantity":"1"}'),
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'nosuchuser@admin.com',
        );

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
    }
}
