<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Category;

class CategoryControllerTest extends BaseControllerTest
{
    use TraitImageControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/categories';
        $this->viewhas = array(
            'singular' => 'category',
            'plural' => 'categories'
        );
        $this->input = array(
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

        // For testing image
        $this->img_parent_model = new Category;
        $this->img_parent_create = [
            'name' => 'This is a name',
            'short_description' => 'This is short description',
            'long_description' => 'This is long description',
            'active' => true,
            'order' => 1,
        ];
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->img_parent_model = null;
        $this->img_parent_create = null;
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
        $this->call('GET', $this->page . '/detail/1');

        $this->assertResponseOk();
    }
    
    /**
     * Test (Pass): access getDetail
     */
    public function testDetailPass()
    {
        $this->testStoreCreatePass();

        $this->call('GET', $this->page . '/detail/1');

        $this->assertResponseOk();
        $this->assertViewHas('category');
    }
}
