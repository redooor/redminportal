<?php

use Redooor\Redminportal\Category;

class CategoryControllerTest extends \RedminTestCase {

    public function testBlankIndex()
    {
        $crawler = $this->client->request('GET', '/admin/categories');

        $this->assertResponseOk();
        $this->assertViewHas('categories');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', '/admin/categories/create');

        $this->assertResponseOk();
    }

    public function testStoreCreateFails_NameBlank()
    {
        $input = array(
            'name'     => '',
            'short_description'   => 'This is short description',
            'long_description'   => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 1,
            'cn_name' => 'This is cn name',
            'cn_short_description'   => 'This is cn short description',
            'cn_long_description'   => 'This is cn long description',
        );

        $this->call('POST', '/admin/categories/store', $input);

        $this->assertRedirectedTo('/admin/categories/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateFails_DescriptionBlank()
    {
        $input = array(
            'name'     => 'This is a name',
            'short_description'   => '',
            'long_description'   => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 1,
            'cn_name' => 'This is cn name',
            'cn_short_description'   => 'This is cn short description',
            'cn_long_description'   => 'This is cn long description',
        );

        $this->call('POST', '/admin/categories/store', $input);

        $this->assertRedirectedTo('/admin/categories/create');
        $this->assertSessionHasErrors();
    }

    public function testStoreCreateSuccess()
    {
        $input = array(
            'name'     => 'This is a name',
            'short_description'   => 'This is short description',
            'long_description'   => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 1,
            'cn_name' => 'This is cn name',
            'cn_short_description'   => 'This is cn short description',
            'cn_long_description'   => 'This is cn long description',
        );

        $this->call('POST', '/admin/categories/store', $input);

        $this->assertRedirectedTo('/admin/categories');

        $model = Category::find(1);

        $this->assertTrue($model->id == 1);
        $this->assertTrue($model->name == 'This is a name');
        $this->assertTrue($model->short_description == 'This is short description');
        $this->assertTrue($model->long_description == 'This is long description');
        $this->assertTrue($model->active == true);
        $this->assertTrue($model->order == 1);
        $this->assertTrue($model->category_id == 1);
    }

    public function testDetailFail404()
    {
        $crawler = $this->client->request('GET', '/admin/categories/detail/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testDetailSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/categories/detail/1');

        $this->assertResponseOk();
        $this->assertViewHas('category');
    }

    public function testEditFail404()
    {
        $crawler = $this->client->request('GET', '/admin/categories/edit/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }

    public function testEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $crawler = $this->client->request('GET', '/admin/categories/edit/1');

        $this->assertResponseOk();
        $this->assertViewHas('category');
    }

    public function testStoreEditFails()
    {
        $input = array(
            'id' => '1',
            'name'     => 'This is a name',
            'short_description'   => 'This is short description',
            'long_description'   => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 1,
            'cn_name' => 'This is cn name',
            'cn_short_description'   => 'This is cn short description',
            'cn_long_description'   => 'This is cn long description',
        );

        $this->call('POST', '/admin/categories/store', $input);

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSessionHasErrors();
    }

    public function testStoreEditSuccess()
    {
        $this->testStoreCreateSuccess();

        $input = array(
            'id' => '1',
            'name'     => 'This is a name',
            'short_description'   => 'This is short description',
            'long_description'   => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 1,
            'cn_name' => 'This is cn name',
            'cn_short_description'   => 'This is cn short description',
            'cn_long_description'   => 'This is cn long description',
        );

        $this->call('POST', '/admin/categories/store', $input);

        $this->assertRedirectedTo('/admin/categories');
    }

    public function testDeleteFail()
    {
        $this->call('GET', '/admin/categories/delete/1');

        $this->assertRedirectedTo('/admin/categories');
        $this->assertSessionHasErrors();
    }

    public function testDeleteSuccess()
    {
        $this->testStoreCreateSuccess();

        $this->call('GET', '/admin/categories/delete/1');

        $this->assertRedirectedTo('/admin/categories');
    }
    
}
