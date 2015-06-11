<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Post;

class PostControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/posts';
        $viewhas = array(
            'singular' => 'post',
            'plural' => 'posts'
        );
        $input = array(
            'create' => array(
                'title' => 'This is the title',
                'slug' => 'this_is_a_slug',
                'content' => 'This is the body',
                'private' => false,
                'featured' => true,
                'category_id' => 1
            ),
            'edit' => array(
                'id'        => 1,
                'title' => 'This is the title',
                'slug' => 'this_is_a_slug',
                'content' => 'This is the body',
                'private' => false,
                'featured' => true,
                'category_id' => 1
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
     * Test (Fail): access postStore with input but no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'title'     => '',
            'slug'      => 'this_is_a_slug',
            'content'   => 'This is body'
        );

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with input but name is non-alphaNum
     */
    public function testStoreCreateFailsNameNonAlphaNum()
    {
        $input = array(
            'title'     => 'Open&%*<',
            'slug'      => 'this_is_a_slug',
            'content'   => 'This is body'
        );

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors();
    }
}
