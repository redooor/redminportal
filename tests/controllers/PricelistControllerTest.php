<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Pricelist;

class PricelistControllerTest extends \RedminTestCase
{
    public function testBlankIndex()
    {
        $this->client->request('GET', '/admin/pricelists');

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

        $this->call('POST', '/admin/pricelists/discount', $input);

        $this->assertRedirectedTo('/admin/pricelists');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/pricelists/deletediscount/1');

        $this->assertRedirectedTo('/admin/pricelists');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/pricelists/deletediscount/1');

        $this->assertRedirectedTo('/admin/pricelists');
    }
}
