<?php namespace Redooor\Redminportal\Test;

class GroupControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/groups';
        $viewhas = array(
            'singular' => 'group',
            'plural' => 'models'
        );
        $input = array(
            'create' => array(
                'name'  => 'This is a group name',
                'permission-inherit' => 'admin.view,admin.users.view',
                'permission-allow'  => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete,admin.users.delete'
            ),
            'edit' => array(
                'id'   => 3,
                'name'  => 'This is another group name',
                'permission-inherit' => 'admin.view,admin.groups.view',
                'permission-allow'  => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete,admin.groups.delete'
            )
        );
        
        // For testing sort
        $this->sortBy = 'email';
        
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
     * Test (Fail): access getEdit, return 404
     */
    public function testEditFail404()
    {
        $this->call('GET', $this->page . '/edit/3');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with input but no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'name'  => '',
            'permission-inherit' => 'admin.view,admin.users.view',
            'permission-allow'  => 'admin.create,admin.update',
            'permission-deny' => 'admin.delete,admin.users.delete'
        );

        $this->call('POST', '/admin/groups/store', $input);

        $this->assertRedirectedTo('/admin/groups/create');
        $this->assertSessionHasErrors();
    }
}
