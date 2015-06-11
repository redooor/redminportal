<?php namespace Redooor\Redminportal\Test;

class UserControllerTest extends BaseControllerTest
{
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/users';
        $viewhas = array(
            'singular' => 'user',
            'plural' => 'users'
        );
        $input = array(
            'create' => array(
                'first_name' => 'Peter',
                'last_name'  => 'Lim',
                'email'      => 'peter.lim@test.com',
                'password'   => '123456',
                'password_confirmation' => '123456',
                'role'       => 1,
                'activated'  => true
            ),
            'edit' => array(
                'id'   => 2,
                'first_name' => 'Peter',
                'last_name'  => 'Lim',
                'email'      => 'peter.lim@test.com',
                'password'   => '123456',
                'password_confirmation' => '123456',
                'role'       => 1,
                'activated'  => true
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
     * Test (Fail): access getEdit, return 404.
     * Override BaseControllerTest method because Seeder already created 1 user
     */
    public function testEditFail404()
    {
        $this->call('GET', $this->page . '/edit/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getDelete with id = 2
     * Override BaseControllerTest method because Seeder already created 1 user
     */
    public function testDeleteFail()
    {
        $this->call('GET', $this->page . '/delete/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getActivate with id = 2
     */
    public function testActivateFail()
    {
        $this->call('GET', $this->page . '/activate/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getActivate with id = 1
     */
    public function testActivatePass()
    {
        $this->call('GET', $this->page . '/activate/1');

        $this->assertResponseStatus(302); // Redirected
    }
    
    /**
     * Test (Fail): access getDeactivate with id = 2
     */
    public function testDeactivateFail()
    {
        $this->call('GET', $this->page . '/deactivate/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getDeactivate with id = 1
     */
    public function testDeactivatePass()
    {
        $this->call('GET', $this->page . '/deactivate/1');
        
        $this->assertResponseStatus(302); // Redirected
    }
    
    /**
     * Test (Fail): access postStore with input but no name
     */
    public function testStoreCreateFailsNameBlank()
    {
        $input = array(
            'first_name' => '',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => '123456',
            'role'       => 1,
            'activated'  => true
        );

        $this->call('POST', '/admin/users/store', $input);

        $this->assertRedirectedTo('/admin/users/create');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getSort by email, asc
     */
    public function testSortByPass()
    {
        $this->call('GET', '/admin/users/sort/email/asc');

        $this->assertResponseOk();
        $this->assertViewHas('users');
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as email
     */
    public function testSortByValidationFail()
    {
        $this->call('GET', '/admin/users/sort/->where("id", 5)/asc');

        $this->assertRedirectedTo('/admin/users');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getSort, try to insert query as orderBy
     */
    public function testSortByValidationOrderByFail()
    {
        $this->call('GET', '/admin/users/sort/email/->where("id", 5)');

        $this->assertRedirectedTo('/admin/users');
        $this->assertSessionHasErrors();
    }
}
