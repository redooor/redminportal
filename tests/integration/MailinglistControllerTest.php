<?php

class MailinglistControllerTest extends \RedminTestCase {

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/mailinglists');

        $this->assertResponseOk();
        $this->assertViewHas('mailinglists');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/mailinglists/create');

        $this->assertResponseOk();
    }

    public function testStoreCreateFails_NameBlank()
    {
        $input = array(
            'email'                 => '',
            'first_name'            => 'Peter',
            'last_name'             => 'Lim'
        );

        $this->call('POST', '/admin/mailinglists/store', $input);

        $this->assertRedirectedTo('/admin/mailinglists/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateSuccess()
    {
        $input = array(
            'email'                 => 'peter.lim@test.com',
            'first_name'            => 'Peter',
            'last_name'             => 'Lim'
        );

        $this->call('POST', '/admin/mailinglists/store', $input);

        $this->assertRedirectedTo('/admin/mailinglists');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/mailinglists/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/mailinglists/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('mailinglist');
    }

    public function testStoreEditFails()
    {
        $input = array(
            'id'                    => 1,
            'email'                 => 'peter.lim@test.com',
            'first_name'            => 'Peter',
            'last_name'             => 'Lim'
        );

        $this->call('POST', '/admin/mailinglists/store', $input);

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSessionHasErrors();
    }

    public function testStoreEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $input = array(
            'id'                    => 1,
            'email'                 => 'peter2.lim@test.com',
            'first_name'            => 'Peter2',
            'last_name'             => 'Lim2'
        );

        $this->call('POST', '/admin/mailinglists/store', $input);

        $this->assertRedirectedTo('/admin/mailinglists');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/mailinglists/delete/1');

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/mailinglists/delete/1');

        $this->assertRedirectedTo('/admin/mailinglists');
    }

    public function testSortBy_Success()
    {
        $crawler = $this->client->request('GET', '/admin/mailinglists/sort/email/asc');

        $this->assertResponseOk();
        $this->assertViewHas('mailinglists');
    }

    public function testSortBy_SortByValidation_Fail()
    {
        $crawler = $this->client->request('GET', '/admin/mailinglists/sort/->where("id", 5)/asc');

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSessionHasErrors();
    }

    public function testSortBy_OrderByValidation_Fail()
    {
        $crawler = $this->client->request('GET', '/admin/mailinglists/sort/email/->where("id", 5)');

        $this->assertRedirectedTo('/admin/mailinglists');
        $this->assertSessionHasErrors();
    }

}
