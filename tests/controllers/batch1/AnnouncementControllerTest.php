<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Announcement;

class AnnouncementControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/announcements';
        $viewhas = array(
            'singular' => 'announcement',
            'plural' => 'models'
        );
        $input = array(
            'create' => array(
                'title'     => 'This is title',
                'content'   => 'This is body',
                'private'   => false
            ),
            'edit' => array(
                'id'        => 1,
                'title'     => 'This is title',
                'content'   => 'This is body',
                'private'   => false
            )
        );
        
        // For testing sort
        $this->sortBy = 'created_at';
        
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
            'content'   => 'This is body'
        );

        $this->call('POST', $this->page . '/store', $input);

        $this->assertRedirectedTo($this->page . '/create');
        $this->assertSessionHasErrors();
    }
}
