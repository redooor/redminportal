<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class LoginControllerTest extends RedminBrowserTestCase
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
        Auth::guard('redminguard')->logout();
        
        $input = array(
            'email' => 'admin@admin.com',
            'password' => 'admin'
        );

        $this->call('POST', '/login/login', $input);

        // Check custom guard
        $this->assertNotTrue(Auth::getDefaultDriver() === 'redminportal');
        $this->assertNotTrue(Auth::guard('web')->check()); // Not using 'web' guard
        $this->assertTrue(Auth::guard('redminguard')->check()); // Using custom guard

        $this->assertRedirectedTo('/admin/dashboard');
    }
    
    /**
     * Test (Fail): access postLogin with correct username but wrong password
     */
    public function testStoreCreateFailWrongPassword()
    {
        Auth::guard('redminguard')->logout();
        
        $input = array(
            'email' => 'admin@admin.com',
            'password' => 'wrong'
        );

        $this->call('POST', '/login/login', $input);

        // Check custom guard
        $this->assertNotTrue(Auth::guard('redminguard')->check());
        $this->assertNotTrue(Auth::getDefaultDriver() === 'redminportal');
        $this->assertNotTrue(Auth::guard('web')->check()); // Check 'web' guard
        $this->assertNotTrue(Auth::guard('redminguard')->check()); // Check custom guard
        $this->assertRedirectedTo('/login');
        $this->assertSessionHasErrors();
    }

    /**
     * Test (Fail): access postLogin with correct username but no password
     */
    public function testStoreCreateFailNoPassword()
    {
        Auth::guard('redminguard')->logout();
        
        $input = array(
            'email' => 'admin@admin.com'
        );

        $this->call('POST', '/login/login', $input);

        // Check custom guard
        $this->assertNotTrue(Auth::guard('redminguard')->check());
        $this->assertNotTrue(Auth::getDefaultDriver() === 'redminportal');
        $this->assertNotTrue(Auth::guard('web')->check()); // Check 'web' guard
        $this->assertNotTrue(Auth::guard('redminguard')->check()); // Check custom guard
        $this->assertRedirectedTo('/login');
        $this->assertSessionHasErrors();
    }

    /**
     * Test (Fail): access postLogin with no username but with password
     */
    public function testStoreCreateFailNoUsername()
    {
        Auth::guard('redminguard')->logout();
        
        $input = array(
            'password' => 'wrong'
        );

        $this->call('POST', '/login/login', $input);

        // Check custom guard
        $this->assertNotTrue(Auth::guard('redminguard')->check());
        $this->assertNotTrue(Auth::getDefaultDriver() === 'redminportal');
        $this->assertNotTrue(Auth::guard('web')->check()); // Check 'web' guard
        $this->assertNotTrue(Auth::guard('redminguard')->check()); // Check custom guard
        $this->assertRedirectedTo('/login');
        $this->assertSessionHasErrors();
    }
}
