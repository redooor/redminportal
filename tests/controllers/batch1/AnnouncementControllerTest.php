<?php namespace Redooor\Redminportal\Test;

use Redooor\Redminportal\App\Models\Announcement;

class AnnouncementControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest, TraitImageControllerTest;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->page = '/admin/announcements';
        $this->viewhas = array(
            'singular' => 'announcement',
            'plural' => 'models'
        );
        $this->input = array(
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

        // For testing image
        $this->img_parent_model = new Announcement;
        $this->img_parent_create = [
            'title'     => 'This is title',
            'content'   => 'This is body',
            'private'   => false
        ];
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->img_parent_model = null;
        $this->img_parent_create = null;
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
