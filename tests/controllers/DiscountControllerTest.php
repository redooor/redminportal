<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Pricelist;

class DiscountControllerTest extends \RedminTestCase
{
    public function testBlankIndex()
    {
        $this->client->request('GET', '/admin/discounts');

        $this->assertResponseOk();
        $this->assertViewHas('pricelists');
    }

    public function testStoreCreateSuccess()
    {
        // Create a Pricelist first
        $pricelist = new Pricelist;
        $pricelist->module_id = 1;
        $pricelist->membership_id = 1;
        $pricelist->price = 1;
        $pricelist->save();

        $input = array(
            'pricelist_id'          => 1,
            'code'                  => 'UY8736',
            'percent'               => 10,
            'expiry_date'           => '01/01/1990'
        );

        $this->call('POST', '/admin/discounts/store', $input);

        $this->assertRedirectedTo('/admin/discounts');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/discounts/delete/1');

        $this->assertRedirectedTo('/admin/discounts');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/discounts/delete/1');

        $this->assertRedirectedTo('/admin/discounts');
    }
}
