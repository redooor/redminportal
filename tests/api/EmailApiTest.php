<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class EmailApiTest extends RedminBrowserTestCase
{
    /**
     * Setup initial data for use in tests
     */
    public function setup(): void
    {
        parent::setup();
        
        $this->seed('RedminSeeder');
    }

    /**
     * Test (Pass): access /admin/api/email
     */
    public function testIndexPass()
    {
        Auth::guard('redminguard')->loginUsingId(1); // Fake admin authentication

        $response = $this->call('GET', '/admin/api/email');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '[]';
        $this->assertTrue($input == $output);
    }
    
    /**
     * Test (Fail): access /admin/api/email without authentication
     */
    public function testIndexNoAuthFail()
    {
        $this->call('GET', '/admin/api/email');
        $this->assertResponseStatus(302);
    }

    /**
     * Test (Fail): access /admin/api/email with authentication but no permission
     */
    public function testIndexNoPermissionFail()
    {
        Auth::guard('redminguard')->loginUsingId(2); // Fake user authentication

        $this->call('GET', '/admin/api/email');
        $this->assertResponseStatus(302);
    }
    
    /**
     * Test (Pass): access /admin/api/email/all
     */
    public function testGetNamePass()
    {
        Auth::guard('redminguard')->loginUsingId(1); // Fake admin authentication
        
        $response = $this->call('GET', '/admin/api/email/all');
        $this->assertResponseOk();
        
        $input = $response->getContent();
        
        $output = '["admin@admin.com"]';
        $this->assertTrue($input == $output);
    }
    
    /**
     * Test (Fail): access /admin/api/email/all without authentication
     */
    public function testGetNameNoAuthFail()
    {
        $this->call('GET', '/admin/api/email/all');
        $this->assertResponseStatus(302);
    }

    /**
     * Test (Fail): access /admin/api/email/all with authentication but no permission
     */
    public function testGetNameNoPermissionFail()
    {
        Auth::guard('redminguard')->loginUsingId(2); // Fake user authentication

        $this->call('GET', '/admin/api/email/all');
        $this->assertResponseStatus(302);
    }
}
