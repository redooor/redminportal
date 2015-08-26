<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Membership;
use Redooor\Redminportal\App\Models\Product;

class BundleControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/bundles';
        $viewhas = array(
            'singular' => 'bundle',
            'plural' => 'bundles'
        );
        $input = array(
            'create' => array(
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description',
                'product_id' => array(1),
                'pricelist_id' => array(1)
            ),
            'edit' => array(
                'id'   => 1,
                'name'                  => 'This is title',
                'short_description'     => 'This is body',
                'cn_name'               => 'CN name',
                'cn_short_description'  => 'CN short body',
                'category_id'           => 1,
                'sku'                   => 'UNIQUESKU001',
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description',
                'product_id' => array(1),
                'pricelist_id' => array(1)
            )
        );
        
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
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->price = 1;
        $pricelist->save();
        
        // Create a new Product for use later
        $product = new Product;
        $product->name = 'This is the title';
        $product->sku = 'UNIQUESKU001';
        $product->short_description = 'This is the body';
        $product->category_id = 1;
        $product->active = true;
        $product->save();
    }
    
    
    /**
     * Test (Fail): access postStore with input but no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'product_id' => array(1),
            'pricelist_id' => array(1)
        );

        $this->call('POST', '/admin/bundles/store', $input);

        $this->assertRedirectedTo('/admin/bundles/create');
        $this->assertSessionHasErrors();
    }
}
