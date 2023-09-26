<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Redooor\Redminportal\App\Models\User;
use Redooor\Redminportal\App\Models\Group;

class BaseAuthenticateTest extends RedminBrowserTestCase
{
    protected $user;
    protected $test_pages;
    protected $test_redirects;
    protected $test_posts;
    
    /**
     * Setup initial data for use in tests
     **/
    public function setup(): void
    {
        parent::setup();
        
        /* Creates a user for each test */
        $this->user = new User;
        $this->user->email        = 'peter@test.com';
        $this->user->password     = Hash::make("test");
        $this->user->first_name   = 'Peter';
        $this->user->last_name    = 'Tester';
        $this->user->activated    = 1;
        $this->user->save();
    }
    
    /**
     * Create group
     * @param string Group Name
     * @param array Permission
     * @return Group
     **/
    protected function createGroup($name, $permission)
    {
        $group = new Group;
        $group->name = $name;
        $group->permissions = json_encode($permission);
        $group->save();
        return $group;
    }
    
    /**
     * Echos text with a carriage return prepended
     **/
    protected function echoText($text)
    {
        return; // Remove this line for debug only
//         echo '
// ' . $text;
    }
    
    /**
     * Run through all pages to check HTTP status
     **/
    protected function runThroughAllPagesAllowedAccess()
    {
        $this->echoText('--- testing allowed access ---');
        
        foreach ($this->test_pages as $page) {
            $this->echoText('testing: ' . $page . ' ...');
            $this->call('GET', $page);
            $this->assertResponseStatus(200);
        }
        
        foreach ($this->test_redirects as $page => $redirect) {
            $this->echoText('testing: ' . $page . ' ...');
            $this->call('GET', $page);
            $this->assertResponseStatus(302);
            $this->assertRedirectedTo($redirect);
        }
        
        foreach ($this->test_posts as $page => $redirect) {
            $this->echoText('testing: ' . $page . ' ...');
            $this->call('POST', $page);
            $this->assertResponseStatus(302);
            $this->assertRedirectedTo($redirect);
        }
    }
    
    /**
     * Run through all pages to check HTTP status
     **/
    protected function runThroughAllPagesDeniedAccess($redirected_page = 'login/unauthorized')
    {
        $this->echoText('--- testing denied access ---');
        
        foreach ($this->test_pages as $page) {
            $this->echoText('testing: ' . $page . ' ...');
            $this->call('GET', $page);
            $this->assertResponseStatus(302);
            $this->assertRedirectedTo($redirected_page);
        }
        
        foreach ($this->test_redirects as $page => $redirect) {
            $this->echoText('testing: ' . $page . ' ...');
            $this->call('GET', $page);
            $this->assertResponseStatus(302);
            $this->assertRedirectedTo($redirected_page);
        }
        
        foreach ($this->test_posts as $page => $redirect) {
            $this->echoText('testing: ' . $page . ' ...');
            $this->call('POST', $page);
            $this->assertResponseStatus(302);
            $this->assertRedirectedTo($redirected_page);
        }
    }
    
    /**
     * Test (fail): test not login, all denied
     **/
    public function testNotLoginAllDenied()
    {
        $this->runThroughAllPagesDeniedAccess('login');
    }
    
    /**
     * Test (pass): test backward compatibility with 'admin'
     **/
    public function testBackwardCompatibilityAdmin()
    {
        // Create backward compatible admin group
        $group = $this->createGroup('Admin', array(
            'admin' => 1
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
     * Test (fail): test backward compatibility with 'user'
     **/
    public function testBackwardCompatibilityUser()
    {
        // Create backward compatible admin group
        $group = $this->createGroup('User', array(
            'user' => 1
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesDeniedAccess();
    }
    
    /**
     * Test (pass): test new authentication with 'admin'
     **/
    public function testNewAuthenticationAdmin()
    {
        $group = $this->createGroup('Admin', array(
            'admin.view' => 1,
            'admin.create' => 1,
            'admin.delete' => 1,
            'admin.update' => 1
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
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
        
        $this->runThroughAllPagesDeniedAccess();
    }
    
    /**
     * Test (pass): test new authentication with 'admin', with bool support
     **/
    public function testNewAuthenticationAdminBoolSupport()
    {
        $group = $this->createGroup('Admin', array(
            'admin.view' => true,
            'admin.create' => true,
            'admin.delete' => true,
            'admin.update' => true
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
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
        
        $this->runThroughAllPagesDeniedAccess();
    }
    
    /**
     * Test (pass): test multiple groups with at least 1 group allow
     **/
    public function testMultipleGroupsAtLeastOneAllow()
    {
        $allow_group_1 = $this->createGroup('Group1', array('admin.view' => 1));
        $allow_group_2 = $this->createGroup('Group2', array('admin.create' => 1));
        $allow_group_3 = $this->createGroup('Group3', array('admin.delete' => 1));
        $allow_group_4 = $this->createGroup('Group4', array('admin.update' => 1));
        
        // Assign groups to user
        $this->user->groups()->save($allow_group_1);
        $this->user->groups()->save($allow_group_2);
        $this->user->groups()->save($allow_group_3);
        $this->user->groups()->save($allow_group_4);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
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
        
        $this->runThroughAllPagesDeniedAccess();
    }
}
