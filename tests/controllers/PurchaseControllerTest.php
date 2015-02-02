<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Pricelist;
use Redooor\Redminportal\UserPricelist;
use Redooor\Redminportal\Module;
use Redooor\Redminportal\Membership;

class PurchaseControllerTest extends \RedminTestCase {

    public function setup()
    {
        parent::setup();
        
        $this->seed('RedminSeeder');
    }
    
    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/purchases');

        $this->assertResponseOk();
        $this->assertViewHas('purchases');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/purchases/create');

        $this->assertResponseOk();
    }
    
    public function testEmails()
    {
        $crawler = $this->client->request('GET', '/admin/purchases/emails');

        $this->assertResponseOk();
    }

    public function testStoreCreate_Success()
    {
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

        $input = array(
            'pricelist_id'   => 1,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases');
        
        $purchase = UserPricelist::find(1);
        $this->assertTrue($purchase->pricelist_id == 1);
        $this->assertTrue($purchase->price == 0);
        $this->assertTrue($purchase->payment_status == 'Completed');
        $this->assertTrue($purchase->transaction_id == 1);
        $this->assertTrue($purchase->user->email == 'admin@admin.com');
    }

    public function testStoreCreate_Failed_NoPricelist()
    {
        $input = array(
            'pricelist_id'   => 1,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases/create');
    }
    
    public function testStoreCreate_Failed_NoSuchUser()
    {
        // Create a new Pricelist for use later
        $pricelist = new Pricelist;
        $pricelist->price = 0;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->save();

        $input = array(
            'pricelist_id'   => 1,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'nosuchuser@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases/create');
    }
    
    public function testStoreCreate_Failed_UserPricelist_Exists()
    {
        $this->testStoreCreate_Success();

        $input = array(
            'pricelist_id'   => 1,
            'payment_status' => 'Completed',
            'paid'           => 1.99,
            'transaction_id' => 1,
            'email'          => 'admin@admin.com',
        );

        $this->call('POST', '/admin/purchases/store', $input);

        $this->assertRedirectedTo('/admin/purchases/create');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/purchases/delete/1');

        $this->assertRedirectedTo('/admin/purchases');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreate_Success();

        $this->call('GET', '/admin/purchases/delete/1');

        $this->assertRedirectedTo('/admin/purchases');
    }

}
