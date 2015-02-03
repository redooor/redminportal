<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Pricelist;
use Redooor\Redminportal\Module;
use Redooor\Redminportal\Membership;

class DiscountControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/discounts';
        $viewhas = array(
            'singular' => 'discount',
            'plural' => 'discounts'
        );
        $input = array(
            'create' => array(
                'pricelist_id'          => 1,
                'code'                  => 'UY8736',
                'percent'               => 10,
                'expiry_date'           => '01/01/1990'
            ),
            'edit' => array(
                'id'   => 1,
                'pricelist_id'          => 1,
                'code'                  => 'UY8736',
                'percent'               => 10,
                'expiry_date'           => '01/01/1990'
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
    }
    
    /**
     * Overwrite base functions, no edit for Discount
     */
    public function testEditPass()
    {
        return;
    }
    
    /**
     * Overwrite base functions, no edit for Discount
     */
    public function testStoreEdit() {
        return;
    }
    
}
