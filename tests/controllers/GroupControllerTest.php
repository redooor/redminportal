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
    
    /**
     * Test (Pass): access getSort by email, asc
     */
    public function testSortByPass()
    {
        $this->client->request('GET', '/admin/groups/sort/email/asc');

        $this->assertResponseOk();
        $this->assertViewHas('groups');
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as email
     */
    public function testSortByValidationFail()
    {
        $this->client->request('GET', '/admin/groups/sort/->where("id", 5)/asc');

        $this->assertRedirectedTo('/admin/groups');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as orderBy
     */
    public function testSortByValidationOrderByFail()
    {
        $this->client->request('GET', '/admin/groups/sort/email/->where("id", 5)');

        $this->assertRedirectedTo('/admin/groups');
        $this->assertSessionHasErrors();
    }
}
