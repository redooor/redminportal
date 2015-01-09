<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\Category;

class CategoryControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/categories';
        $viewhas = array(
            'singular' => 'category',
            'plural' => 'categories'
        );
        $input = array(
            'create' => array(
                'name' => 'This is a name',
                'short_description' => 'This is short description',
                'long_description' => 'This is long description',
                'active' => true,
                'order' => 1,
                'parent_id' => 1,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            ),
            'edit' => array(
                'id' => '1',
                'name' => 'This is a name',
                'short_description' => 'This is short description',
                'long_description' => 'This is long description',
                'active' => true,
                'order' => 1,
                'parent_id' => 1,
                'cn_name' => 'This is cn name',
                'cn_short_description' => 'This is cn short description',
                'cn_long_description' => 'This is cn long description'
            )
        );
        
        parent::__construct($page, $viewhas, $input);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }
    
    /**
     * Test (Fail): access postStore with same name and same parent
     */
    public function testStoreCreatePassSameNameSameParent()
    {
        $this->testStoreCreatePass(); // Add a new record
        
        $this->call('POST', $this->page . '/store', $this->input['create']);
        
        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access postStore with same name and different parent
     */
    public function testStoreCreatePassSameNameDifferentParent()
    {
        $this->testStoreCreatePass(); // Add a new record
        
        $input = array(
            'name' => 'This is a name',
            'short_description' => 'This is short description',
            'long_description' => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 2, // Different Parent
            'cn_name' => 'This is cn name',
            'cn_short_description' => 'This is cn short description',
            'cn_long_description' => 'This is cn long description'
        );

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page);
    }
    
    /**
     * Test (Fail): Changing existing record to same parent that already has the same category name
     */
    public function testStoreEditFailSameNameSameParent()
    {
        $this->testStoreCreatePass(); // Add a new record
        
        // Create 2nd record under different parent
        $input = array(
            'name' => 'This is a name',
            'short_description' => 'This is short description',
            'long_description' => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 2, // Different Parent
            'cn_name' => 'This is cn name',
            'cn_short_description' => 'This is cn short description',
            'cn_long_description' => 'This is cn long description'
        );

        $this->call('POST', $this->page . '/store', $input);
        
        // Try to edit 2nd record's parent to same parent as 1st record
        $input = array(
            'id' => 2,
            'name' => 'This is a name',
            'short_description' => 'This is short description',
            'long_description' => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 1, // Change to same Parent
            'cn_name' => 'This is cn name',
            'cn_short_description' => 'This is cn short description',
            'cn_long_description' => 'This is cn long description'
        );

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/edit/2');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with input but no description
     */
    public function testStoreCreateFailsDescriptionBlank()
    {
        $input = array(
            'name' => 'This is a name',
            'short_description' => '',
            'long_description' => 'This is long description',
            'active' => true,
            'order' => 1,
            'parent_id' => 1,
            'cn_name' => 'This is cn name',
            'cn_short_description' => 'This is cn short description',
            'cn_long_description' => 'This is cn long description'
        );

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getDetail, return 404
     */
    public function testDetailFail404()
    {
        $crawler = $this->client->request('GET', $this->page . '/detail/1');

        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('h1:contains("Oops, 404!")'));
    }
    
    /**
     * Test (Pass): access getDetail
     */
    public function testDetailPass()
    {
        $this->testStoreCreatePass();

        $this->client->request('GET', $this->page . '/detail/1');

        $this->assertResponseOk();
        $this->assertViewHas('category');
    }
}
