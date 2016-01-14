<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Order;
use Redooor\Redminportal\App\Models\Product;

class OrderControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
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
                'product_id' => array(1),
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
            ),
            'edit' => array(
                'id'   => 1,
                'product_id' => array(1),
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
            )
        );
        
        // For testing sort
        $this->sortBy = 'created_at';
        
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
    public function setup()
    {
        parent::setup();
        
        $this->seed('RedminSeeder');
        
        // Add product
        $product = new Product;
        $product->name = 'This is title';
        $product->sku = 'UNIQUESKU001';
        $product->short_description = 'This is body';
        $product->active = true;
        $product->category_id = 1;
        $product->save();
    }
    
    /**
     * Test (Pass): access getEmails
     */
    public function testEmails()
    {
        $this->call('GET', '/admin/orders/emails');

        $this->assertResponseOk();
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
            'product_id' => array(2),
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
            'product_id' => array(1),
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'nosuchuser@admin.com',
        );

        $this->call('POST', '/admin/orders/store', $input);

        $this->assertRedirectedTo('/admin/orders/create');
    }
}
