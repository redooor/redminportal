<?php

class MembershipControllerTest extends \RedminTestCase {

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/memberships');

        $this->assertResponseOk();
        $this->assertViewHas('memberships');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/memberships/create');

        $this->assertResponseOk();
    }

    public function testStoreCreateFails_NameBlank()
    {
        $input = array(
            'name'     => '',
            'rank'     => 1
        );

        $this->call('POST', '/admin/memberships/store', $input);

        $this->assertRedirectedTo('/admin/memberships/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateSuccess()
    {
        $input = array(
            'name'     => 'This is title',
            'rank'     => 1
        );

        $this->call('POST', '/admin/memberships/store', $input);

        $this->assertRedirectedTo('/admin/memberships');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/memberships/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/memberships/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('membership');
    }

    public function testStoreEditFails()
    {
        $this->testStoreCreateSuccess();

        $input = array(
            'id'      => 1,
            'name'    => 'This is title'
        );

        $this->call('POST', '/admin/memberships/store', $input);

        $this->assertRedirectedTo('/admin/memberships/edit/1');
        $this->assertSessionHasErrors();
    }

    public function testStoreEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $input = array(
            'id'      => 1,
            'name'    => 'This is title',
            'rank'    => 2
        );

        $this->call('POST', '/admin/memberships/store', $input);

        $this->assertRedirectedTo('/admin/memberships');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/memberships/delete/1');

        $this->assertRedirectedTo('/admin/memberships');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/memberships/delete/1');

        $this->assertRedirectedTo('/admin/memberships');
    }

}
