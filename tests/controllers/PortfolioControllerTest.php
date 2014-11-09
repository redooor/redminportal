<?php

class PortfolioControllerTest extends \RedminTestCase {

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/portfolios');

        $this->assertResponseOk();
        $this->assertViewHas('portfolios');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/portfolios/create');

        $this->assertResponseOk();
    }

    public function testStoreCreateFails_NameBlank()
    {
        $input = array(
            'name'                  => '',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1
        );

        $this->call('POST', '/admin/portfolios/store', $input);

        $this->assertRedirectedTo('/admin/portfolios/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateSuccess()
    {
        $input = array(
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1
        );

        $this->call('POST', '/admin/portfolios/store', $input);

        $this->assertRedirectedTo('/admin/portfolios');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/portfolios/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/portfolios/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('portfolio');
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

        $this->call('POST', '/admin/portfolios/store', $input);

        $this->assertRedirectedTo('/admin/portfolios/edit/1');
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

        $this->call('POST', '/admin/portfolios/store', $input);

        $this->assertRedirectedTo('/admin/portfolios/edit/1');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/portfolios/delete/1');

        $this->assertRedirectedTo('/admin/portfolios');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/portfolios/delete/1');

        $this->assertRedirectedTo('/admin/portfolios');
    }
}
