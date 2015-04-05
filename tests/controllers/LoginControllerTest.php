<?php namespace Redooor\Redminportal\Test;

class LoginControllerTest extends RedminTestCase
{
    /**
     * Initialize Setup with seed
     */
    public function setUp()
    {
        parent::setUp();

        $this->seed('RedminSeeder');
    }
    
    /**
     * Test (Pass): access getIndex
     */
    public function testIndex()
    {
        $this->call('GET', '/login');
        $this->assertResponseOk();
    }
    
    /**
     * Test (Pass): access getUnauthorized
     */
    public function testUnauthorized()
    {
        $this->call('GET', '/login/unauthorized');
        $this->assertResponseOk();
    }
    
    /**
     * Test (Pass): access getLogout
     */
    public function testLogout()
    {
        $this->call('GET', '/logout');
        $this->assertRedirectedTo('/');
    }
    
    /**
     * Test (Pass): access postLogin with correct username and password
     */
    public function testStoreCreatePass()
    {
        $input = array(
            'email' => 'admin@admin.com',
            'password' => 'admin'
        );

        $this->call('POST', '/login/login', $input);

        $this->assertRedirectedTo('/admin');
    }
    
    /**
     * Test (Fail): access postLogin with correct username but wrong password
     */
    public function testStoreCreateFailWrongPassword()
    {
        $input = array(
            'email' => 'admin@admin.com',
            'password' => 'wrong'
        );

        $this->call('POST', '/login/login', $input);

        $this->assertRedirectedTo('/login');
        $this->assertSessionHasErrors();
    }
}
