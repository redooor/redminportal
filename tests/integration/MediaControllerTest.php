<?php

class MediaControllerTest extends \RedminTestCase {

    public function tearDown() {
        Mockery::close();
    }

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/medias');

        $this->assertResponseOk();
        $this->assertViewHas('medias');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/medias/create');

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

        $this->call('POST', '/admin/medias/store', $input);

        $this->assertRedirectedTo('/admin/medias/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateSuccess()
    {
        $path = __DIR__ . '/../dummy/';
        $filename = 'foo113a.pdf';
        $mimeType = 'application/pdf';

        $file = new \Symfony\Component\HttpFoundation\File\UploadedFile (
            $path . $filename,
            $filename,
            $mimeType
        );

        $input = array(
            'name'                  => 'This is title',
            'short_description'     => 'This is body',
            'cn_name'               => 'CN name',
            'cn_short_description'  => 'CN short body',
            'category_id'           => 1,
            'sku'                   => 'UNIQUESKU001'
        );

        $this->call('POST', '/admin/medias/store', $input, array('media_file' => $file));

        $this->assertRedirectedTo('/admin/medias');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/medias/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/medias/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('media');
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

        $this->call('POST', '/admin/medias/store', $input);

        $this->assertRedirectedTo('/admin/medias/edit/1');
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

        $this->call('POST', '/admin/medias/store', $input);

        $this->assertRedirectedTo('/admin/medias/edit/1');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/medias/delete/1');

        $this->assertRedirectedTo('/admin/medias');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/medias/delete/1');

        $this->assertRedirectedTo('/admin/medias');
    }

}
