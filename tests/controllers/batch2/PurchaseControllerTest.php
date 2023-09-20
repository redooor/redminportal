<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Pricelist;
use Redooor\Redminportal\App\Models\Module;
use Redooor\Redminportal\App\Models\Membership;

class PurchaseControllerTest extends BaseControllerTest
{
    /**
     * Prepare data for tests
     */
    public function prepare()
    {
        $this->page = '/admin/purchases';
        $this->viewhas = array(
            'singular' => 'purchase',
            'plural' => 'purchases'
        );
        $this->input = array(
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
    }
    
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();

        $this->prepare();
        
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
     * Overwrite base functions, no edit for Purchase
     */
    public function testEditPass()
    {
        $this->call('GET', '/admin/purchases/edit/1');
        $this->assertRedirectedTo('/admin/purchases');
    }
    
    /**
     * Overwrite base functions, no edit for Purchase
     */
    public function testStoreEdit()
    {
        $this->call('GET', '/admin/purchases/edit/1');
        $this->assertRedirectedTo('/admin/purchases');
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
