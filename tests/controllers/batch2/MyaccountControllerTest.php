<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class MyaccountControllerTest extends RedminBrowserTestCase
{
    /**
     * Initialize Setup with seed
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed('RedminSeeder');
        
        Auth::guard('redminguard')->loginUsingId(1);
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
        Auth::guard('redminguard')->logout();
        
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
     * Test (Pass): access postStore with input and new password, stays logged in
     */
    public function testPostStorePassWithNewPassword()
    {
        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => 'Passw0rd-1234',
            'password_confirmation' => 'Passw0rd-1234',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);

        $this->assertTrue(Auth::guard('redminguard')->check()); // Still logged in after save
        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHas('success_message', trans('redminportal::messages.user_myaccount_save_success'));
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
            'password'   => 'Passw0rd-1234',
            'password_confirmation' => 'Passw0rd-1234',
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
            'password'   => 'Passw0rd-1234',
            'password_confirmation' => 'Passw0rd-12345',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);
        
        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHasErrors();
    }

    /**
     * Test (Fail): access postStore with a 9-character new password
     */
    public function testPostStoreFailPasswordTooShort()
    {
        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => 'Passw0rd1',
            'password_confirmation' => 'Passw0rd1',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('admin', Auth::guard('redminguard')->user()->fresh()->password));
    }

    /**
     * Test (Pass): access postStore with a 10-character new password
     */
    public function testPostStorePassTenCharacterPassword()
    {
        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => 'Passw0rd12',
            'password_confirmation' => 'Passw0rd12',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHas('success_message', trans('redminportal::messages.user_myaccount_save_success'));
        $this->assertTrue(Hash::check('Passw0rd12', Auth::guard('redminguard')->user()->fresh()->password));
    }

    /**
     * Test (Pass): minimum password length follows the config value
     */
    public function testPasswordMinLengthFollowsConfig()
    {
        Config::set('redminportal::password.min_length', 12);

        $input = array(
            'first_name' => 'Peter',
            'last_name'  => 'Lim',
            'email'      => 'peter.lim@test.com',
            'password'   => 'Passw0rd123',
            'password_confirmation' => 'Passw0rd123',
            'old_password' => 'admin'
        );

        $this->call('POST', 'myaccount/store', $input);

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHasErrors('password');

        $input['password'] = 'Passw0rd1234';
        $input['password_confirmation'] = 'Passw0rd1234';

        $this->call('POST', 'myaccount/store', $input);

        $this->assertRedirectedTo('myaccount');
        $this->assertSessionHas('success_message', trans('redminportal::messages.user_myaccount_save_success'));
        $this->assertTrue(Hash::check('Passw0rd1234', Auth::guard('redminguard')->user()->fresh()->password));
    }
}
