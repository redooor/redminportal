<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class PublicAuthenticateTest extends BaseAuthenticateTest
{
    /**
     * Constructs dataset for tests later
     **/
    public function setUp(): void
    {
        parent::setUp();
        
        $this->test_pages = [
            'login',
            'login/unauthorized',
            'api/tag',
            'api/tag/name'
        ];
        
        $this->test_redirects = [
            'logout' => '/',
        ];
        
        $this->test_posts = [];
    }
    
    /**
     * Override parent
     * This test should pass because these pages are public accessible
     * Test (fail): test not login, all denied
     **/
    public function testNotLoginAllDenied()
    {
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
     * Override parent
     * This test should pass because these pages are public accessible
     * Test (pass): test backward compatibility with 'user'
     **/
    public function testBackwardCompatibilityUser()
    {
        $group = $this->createGroup('User', array(
            'user' => 1
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        // This part is different from parent
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
     * Override parent
     * This test should pass because these pages are public accessible
     * Test (fail): test new authentication with 'user'
     **/
    public function testNewAuthenticationUser()
    {
        $group = $this->createGroup('User', array(
            'admin.view' => 0,
            'admin.create' => 0,
            'admin.delete' => 0,
            'admin.update' => 0
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
     * Override parent
     * This test should pass because these pages are public accessible
     * Test (fail): test new authentication with 'user', with bool support
     **/
    public function testNewAuthenticationUserBoolSupport()
    {
        $group = $this->createGroup('User', array(
            'admin.view' => false,
            'admin.create' => false,
            'admin.delete' => false,
            'admin.update' => false
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
     * Override parent
     * This test should pass because these pages are public accessible
     * Test (pass): test multiple groups with at least 1 group deny
     **/
    public function testMultipleGroupsAtLeastOneDeny()
    {
        $deny_group_1 = $this->createGroup('Group1', array('admin.view' => -1));
        $deny_group_2 = $this->createGroup('Group2', array('admin.create' => -1));
        $deny_group_3 = $this->createGroup('Group3', array('admin.delete' => -1));
        $deny_group_4 = $this->createGroup('Group4', array('admin.update' => -1));
        
        // Assign groups to user
        $this->user->groups()->save($deny_group_1);
        $this->user->groups()->save($deny_group_2);
        $this->user->groups()->save($deny_group_3);
        $this->user->groups()->save($deny_group_4);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
}
