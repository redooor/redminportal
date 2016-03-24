<?php namespace Redooor\Redminportal\Test;

use Auth;

class MyaccountControllerTest extends RedminTestCase
{
    /**
     * Initialize Setup with seed
     */
    public function setUp()
    {
        parent::setUp();

        $this->seed('RedminSeeder');
        
        Auth::loginUsingId(1);
    }
    
    /**
     * Test (Pass): access getIndex
     */
    public function testIndexPass()
    {
        $this->call('GET', 'myaccount');

        $this->assertResponseOk();
    }
    
    /**
     * Test (Fail): access getIndex no login
     */
    public function testIndexFail()
    {
        Auth::logout();
        
        $this->call('GET', 'myaccount');

        $this->assertRedirectedTo('login');
    }
    
    /**
     * Test (Pass): access postStore with input
     */
    public function testPostStorePass()
    {
        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHas('success_message', trans('redminportal::messages.user_myaccount_save_success'));
    }
    
    /**
     * Test (Pass): access postStore with input and new password, will logout
     */
    public function testPostStorePassWithNewPassword()
    {
        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => '123456',
            'password_confirmation' => '123456',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);

        $this->assertTrue(Auth::check() == false); // Logged out after save
        $this->assertRedirectedTo('myaccount');
    }
    
    /**
     * Test (Fail): access postStore with wrong existing password
     */
    public function testPostStoreFailWrongPassword()
    {
        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => '123456',
            'password_confirmation' => '123456',
            'old_password' => 'admin2'
        );

        $this->call('POST', 'myaccount/store', $input);
        
        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHasErrors();
    }
    
    /**
     * Test (Fail): access postStore with unmatched new password
     */
    public function testPostStoreFailUnmatchedPassword()
    {
        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => '123456',
            'password_confirmation' => '1234567',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);
        
        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHasErrors();
    }
}
