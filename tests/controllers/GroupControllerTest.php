<?php namespace Redooor\Redminportal\Test;

class GroupControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/groups';
        $viewhas = array(
            'singular' => 'group',
            'plural' => 'groups'
        );
        $input = array(
            'create' => array(
                'name'  => 'This is a group name',
                'admin' => true,
                'user'  => true
            ),
            'edit' => array(
                'id'   => 1,
                'name'  => 'This is another group name',
                'admin' => true,
                'user'  => false
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
            'name'  => '',
            'admin' => true,
            'user'  => false
        );

        $this->call('POST', '/admin/groups/store', $input);

        $this->assertRedirectedTo('/admin/groups/create');
        $this->assertSessionHasErrors();
    }
}
