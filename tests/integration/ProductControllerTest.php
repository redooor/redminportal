<?php

class ProductControllerTest extends \RedminTestCase {

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/products');

        $this->assertResponseOk();
        $this->assertViewHas('products');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/products/create');

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

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/create');
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

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/products/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/products/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('product');
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

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/edit/1');
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
            'category_id'           => 1,
            'price'                 => 10
        );

        $this->call('POST', '/admin/products/store', $input);

        $this->assertRedirectedTo('/admin/products/edit/1');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/products/delete/1');

        $this->assertRedirectedTo('/admin/products');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/products/delete/1');

        $this->assertRedirectedTo('/admin/products');
    }

}
