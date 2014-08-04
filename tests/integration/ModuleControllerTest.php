<?php

use Redooor\Redminportal\Pricelist;
use Redooor\Redminportal\Membership;
use Redooor\Redminportal\Media;
use Redooor\Redminportal\ModuleMediaMembership;

class ModuleControllerTest extends \RedminTestCase {

    public function tearDown() {
        Mockery::close();
    }

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/modules');

        $this->assertResponseOk();
        $this->assertViewHas('modules');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/modules/create');

        $this->assertResponseOk();
    }

    public function testStoreCreateFails_NameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001'
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateSuccess()
    {
        $input = array(
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001'
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/modules/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/modules/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('module');
    }

    public function testStoreEditFails()
    {
        $input = array(
            'id'                    => 1,
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules/edit/1');
        $this->assertSessionHasErrors();
    }

    public function testStoreEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $input = array(
            'id'                    => 1,
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules/edit/1');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/modules/delete/1');

        $this->assertRedirectedTo('/admin/modules');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/modules/delete/1');

        $this->assertRedirectedTo('/admin/modules');
    }

    public function testStorePricingSuccess()
    {
        // Add membership
        $membership = new Membership;
        $membership->name = "Gold";
        $membership->rank = 5;
        $membership->save();

        $input = array(
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'price_1'               => 99.99
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules');

        $pricelist = Pricelist::where('module_id', 1)->where('membership_id', 1)->first();

        $this->assertTrue($pricelist != null);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
        $this->assertTrue($pricelist->price == 99.99);
    }

    public function testEditPricingSuccess()
    {
        $this->testStorePricingSuccess();

        $input = array(
            'id'                    => 1,
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'price_1'               => 88.88
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules');

        $pricelist = Pricelist::where('module_id', 1)->where('membership_id', 1)->first();

        $this->assertTrue($pricelist != null);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
        $this->assertTrue($pricelist->price == 88.88);
    }

    public function testStoreMediasSuccess()
    {
        // Add membership
        $membership = new Membership;
        $membership->name = "Gold";
        $membership->rank = 5;
        $membership->save();

        // Add media
        $media = new Media;
        $media->name = 'This is the title';
        $media->path = 'path/to/somewhere';
        $media->sku = 'UNIQUESKU001';
        $media->short_description = 'This is the body';
        $media->category_id = 1;
        $media->active = true;
        $result = $media->save();

        $input = array(
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001',
            'price_1'               => 99.99,
            'media_checkbox'        => array('1_1')
        );

        $this->call('POST', '/admin/modules/store', $input);

        $this->assertRedirectedTo('/admin/modules');

        $pricelist = Pricelist::where('module_id', 1)
            ->where('membership_id', 1)->first();

        $this->assertTrue($pricelist != null);
        $this->assertTrue($pricelist->module_id == 1);
        $this->assertTrue($pricelist->membership_id == 1);
        $this->assertTrue($pricelist->price == 99.99);

        $modMediaMembership = ModuleMediaMembership::where('module_id', 1)
            ->where('membership_id', 1)
            ->where('media_id', 1)
            ->first();

        $this->assertTrue($modMediaMembership != null);
    }
}
