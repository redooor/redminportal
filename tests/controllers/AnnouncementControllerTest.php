<?php

class AnnouncementControllerTest extends \RedminTestCase {

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/announcements');

        $this->assertResponseOk();
        $this->assertViewHas('announcements');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/announcements/create');

        $this->assertResponseOk();
    }

    public function testStoreCreateFails_NameBlank()
    {
        $input = array(
            'title'     => '',
            'content'   => 'This is body'
        );

        $this->call('POST', '/admin/announcements/store', $input);

        $this->assertRedirectedTo('/admin/announcements/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateFails_NameNonAlphaNum()
    {
        $input = array(
            'title'     => 'Open&%*<',
            'content'   => 'This is body'
        );

        $this->call('POST', '/admin/announcements/store', $input);

        $this->assertRedirectedTo('/admin/announcements/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateSuccess()
    {
        $input = array(
            'title'     => 'This is title',
            'content'   => 'This is body',
            'private'   => FALSE
        );

        $this->call('POST', '/admin/announcements/store', $input);

        $this->assertRedirectedTo('/admin/announcements');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/announcements/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/announcements/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('announcement');
    }

    public function testStoreEditFails()
    {
        $input = array(
            'id'        => 1,
            'title'     => 'This is title',
            'content'   => 'This is body',
            'private'   => FALSE
        );

        $this->call('POST', '/admin/announcements/store', $input);

        $this->assertRedirectedTo('/admin/announcements/edit/1');
        $this->assertSessionHasErrors();
    }

    public function testStoreEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $input = array(
            'id'        => 1,
            'title'     => 'This is title',
            'content'   => 'This is body',
            'private'   => FALSE
        );

        $this->call('POST', '/admin/announcements/store', $input);

        $this->assertRedirectedTo('/admin/announcements');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/announcements/delete/1');

        $this->assertRedirectedTo('/admin/announcements');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/announcements/delete/1');

        $this->assertRedirectedTo('/admin/announcements');
    }
}
