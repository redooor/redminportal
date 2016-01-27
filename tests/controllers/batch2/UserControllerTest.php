<?php namespace Redooor\Redminportal\Test;

class UserControllerTest extends BaseControllerTest
{
    use TraitSorterControllerTest;
    
    /**
     * Contructor
     */
    public function __construct()
    {
        $page = '/admin/users';
        $viewhas = array(
            'singular' => 'user',
            'plural' => 'models'
        );
        $input = array(
            'create' => array(
                'first_name' => 'Peter',
                'last_name'  => 'Lim',
                'email'      => 'peter.lim@test.com',
                'password'   => '123456',
                'password_confirmation' => '123456',
                'role'       => 1,
                'activated'  => true,
                'permission-inherit' => 'admin.view,admin.users.view',
                'permission-allow'  => 'admin.create,admin.update',
                'permission-deny' => 'admin.delete,admin.users.delete'
            ),
            'edit' => array(
                'id'   => 2,
                'first_name' => 'Peter',
                'last_name'  => 'Lim',
                'email'      => 'peter.lim@test.com',
                'password'   => '123456',
                'password_confirmation' => '123456',
                'role'       => 1,
                'activated'  => true,
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
     * Test (Pass): access getDelete with id = 2
     * Override BaseControllerTest method, cannot delete self, must delete another user
     */
    public function testDeletePass()
    {
        $this->testStoreCreatePass();
        
        $this->call('GET', $this->page . '/delete/2');

        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
    }
    
    /**
     * Test (Fail): access getDelete with id = 2
     * Override BaseControllerTest method because Seeder already created 1 user
     */
    public function testDeleteFailNoRecord()
    {
        $this->call('GET', $this->page . '/delete/2');

        $this->assertRedirectedTo('/');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getDelete with id = 1
     * Cannot delete own account while logged in
     */
    public function testDeleteFailDeleteSelf()
    {
        $this->call('GET', $this->page . '/delete/1');

        $this->assertRedirectedTo('/');
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
    public function testActivatePassActivateSelf()
    {
        $this->call('GET', $this->page . '/activate/1');
        
        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
    }
    
    /**
     * Test (Pass): access getActivate with id = 2
     */
    public function testActivatePassActivateOther()
    {
        // Creates 2nd record
        $this->testStoreCreatePass();
        
        // Then try activate it
        $this->call('GET', $this->page . '/activate/2');
        
        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
    }
    
    /**
     * Test (Fail): access getDeactivate with id = 2
     */
    public function testDeactivateFailNoRecord()
    {
        $this->call('GET', $this->page . '/deactivate/2');

        $this->assertRedirectedTo($this->page);
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access getDeactivate with id = 1
     * Cannot deactivate own account while logged in
     */
    public function testDeactivateFailDeactivateSelf()
    {
        $this->call('GET', $this->page . '/deactivate/1');
        
        $this->assertResponseStatus(302); // Redirected
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getDeactivate with id = 2
     */
    public function testDeactivatePass()
    {
        // Creates 2nd record
        $this->testStoreCreatePass();
        
        // Then try deactivate it
        $this->call('GET', $this->page . '/deactivate/2');
        
        $this->assertResponseStatus(302); // Redirected
        $this->assertRedirectedTo('/'); // Redirected to previous page, which is root
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
     * Test (Pass): access postSearch with valid input
     */
    public function testPostSearchNamePass()
    {
        $input = array(
            'search' => 'peter'
        );

        $this->call('POST', '/admin/users/search', $input);

        $this->assertRedirectedTo('admin/users/search/peter');
    }
    
    /**
     * Test (Fail): access postSearch with invalid input
     */
    public function testPostSearchNameFail()
    {
        $input = array(
            'search' => 'peter$&%*'
        );

        $this->call('POST', '/admin/users/search', $input);

        $this->assertRedirectedTo('/admin/users');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access postSearch with valid input, for Group
     */
    public function testPostSearchGroupNamePass()
    {
        $input = array(
            'search' => 'group:user'
        );

        $this->call('POST', '/admin/users/search', $input);

        $this->assertRedirectedTo('admin/users/group/user');
    }
    
    /**
     * Test (Fail): access postSearch with invalid input, for Goup
     */
    public function testPostSearchGroupNameFail()
    {
        $input = array(
            'search' => 'group:user$&%*'
        );

        $this->call('POST', '/admin/users/search', $input);

        $this->assertRedirectedTo('/admin/users');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getSearch with valid input
     */
    public function testGetSearchNamePass()
    {
        $this->call('GET', '/admin/users/search/peter');

        $this->assertResponseOk();
        $this->assertViewHas('models');
    }
    
    /**
     * Test (Fail): access getSearch with invalid input
     */
    public function testGetSearchNameFail()
    {
        $this->call('GET', '/admin/users/search/peter$&%*');

        $this->assertRedirectedTo('/admin/users');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Pass): access getGroup with valid input
     */
    public function testGetGroupNamePass()
    {
        $this->call('GET', '/admin/users/group/user');

        $this->assertResponseOk();
        $this->assertViewHas('models');
    }
    
    /**
     * Test (Fail): access getGroup with invalid input
     */
    public function testGetGroupNameFail()
    {
        $this->call('GET', '/admin/users/group/user$&%*');

        $this->assertRedirectedTo('/admin/users');
        $this->assertSessionHasErrors();
    }
}
