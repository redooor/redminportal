<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\UserPricelist;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Membership;

class PurchaseControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/purchases';
        $viewhas = array(
            'singular' => 'purchase',
            'plural' => 'purchases'
        );
        $input = array(
            'create' => array(
                'pricelist_id'   => 1,
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
            ),
            'edit' => array(
                'id'   => 1,
                'pricelist_id'   => 1,
                'payment_status' => 'Completed',
                'paid'           => 1.99,
                'transaction_id' => 1,
                'email'          => 'admin@admin.com'
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
        
        $this->seed('RedminSeeder');
        
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
        $pricelist->price = 0;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->save();
    }
    
    /**
     * Test (Pass): access getEmails
     */
    public function testEmails()
    {
        $this->call('GET', '/admin/purchases/emails');

        $this->assertResponseOk();
    }
    
    /**
     * Overwrite base functions, no edit for Purchase
     */
    public function testEditPass()
    {
        return;
    }
    
    /**
     * Overwrite base functions, no edit for Purchase
     */
    public function testStoreEdit()
    {
        return;
    }

    public function testStoreCreateFailedNoPricelist()
    {
        $input = array(
            'pricelist_id'   => 2,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases/create');
    }
    
    public function testStoreCreateFailedNoSuchUser()
    {
        $input = array(
            'pricelist_id'   => 2,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'nosuchuser@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases/create');
    }
}
