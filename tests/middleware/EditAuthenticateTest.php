<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class EditAuthenticateTest extends BaseAuthenticateTest
{
    /**
     * Constructs dataset for tests later
     **/
    public function setUp(): void
    {
        parent::setUp();
        
        $this->test_pages = [
            'admin/groups/edit/1',
            'admin/users/edit/1'
        ];
        
        $this->test_redirects = [
            'admin/announcements/edit/1'    => 'admin/announcements',
            'admin/bundles/edit/1'          => 'admin/bundles',
            'admin/categories/edit/1'       => 'admin/categories',
            'admin/coupons/edit/1'          => 'admin/coupons',
            'admin/mailinglists/edit/1'     => 'admin/mailinglists',
            'admin/medias/edit/1'           => 'admin/medias',
            'admin/memberships/edit/1'      => 'admin/memberships',
            'admin/modules/edit/1'          => 'admin/modules',
            'admin/orders/edit/1'           => 'admin/orders',
            'admin/pages/edit/1'            => 'admin/pages',
            'admin/portfolios/edit/1'       => 'admin/portfolios',
            'admin/posts/edit/1'            => 'admin/posts',
            'admin/products/edit/1'         => 'admin/products',
            'admin/promotions/edit/1'       => 'admin/promotions',
            'admin/purchases/edit/1'        => 'admin/purchases',
            'admin/products/edit-variant/1/1' => 'admin/products',
        ];
        
        $this->test_posts = [];
    }
    
    /**
     * Override parent's function
     * Insert ID to Post method
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
            $this->call('POST', $page, ['id' => 1]);
            $this->assertResponseStatus(302);
            $this->assertRedirectedTo($redirect);
        }
    }
    
    /**
     * Test (pass): Specific pages are allowed, others denied
     **/
    public function testSpecificPagesAllowedButNotOthers()
    {
        $this->test_pages = [];
        
        $this->test_redirects = [
            'admin/dashboard'               => 'login/unauthorized',
            'admin/bundles/edit/1'          => 'login/unauthorized',
            'admin/categories/edit/1'       => 'login/unauthorized',
            'admin/groups/edit/1'           => 'login/unauthorized',
            'admin/images'                  => 'login/unauthorized',
            'admin/mailinglists/edit/1'     => 'login/unauthorized',
            'admin/medias/edit/1'           => 'login/unauthorized',
            'admin/memberships/edit/1'      => 'login/unauthorized',
            'admin/modules/edit/1'          => 'login/unauthorized',
            'admin/orders/edit/1'           => 'login/unauthorized',
            'admin/portfolios/edit/1'       => 'login/unauthorized',
            'admin/products/edit/1'         => 'login/unauthorized',
            'admin/products/create-variant/1' => 'login/unauthorized',
            'admin/purchases/edit/1'        => 'login/unauthorized',
            'admin/reports'                 => 'login/unauthorized',
            'admin/users/edit/1'            => 'login/unauthorized',
            'admin/api/email'               => 'login/unauthorized',
            'admin/api/email/all'           => 'login/unauthorized',
            'admin/products/edit-variant/1/1'   => 'login/unauthorized',
            'admin/announcements/edit/1'    => 'admin/announcements',
            'admin/coupons/edit/1'          => 'admin/coupons',
            'admin/pages/edit/1'            => 'admin/pages',
            'admin/posts/edit/1'            => 'admin/posts',
            'admin/promotions/edit/1'       => 'admin/promotions',
        ];
        
        $this->test_posts = [
            'admin/reports/mailinglist' => 'login/unauthorized',
            'admin/reports/purchases'   => 'login/unauthorized',
            'admin/reports/orders'      => 'login/unauthorized',
            'admin/bundles/store'       => 'login/unauthorized',
            'admin/categories/store'    => 'login/unauthorized',
            'admin/groups/store'        => 'login/unauthorized',
            'admin/mailinglists/store'  => 'login/unauthorized',
            'admin/medias/store'        => 'login/unauthorized',
            'admin/memberships/store'   => 'login/unauthorized',
            'admin/modules/store'       => 'login/unauthorized',
            'admin/orders/store'        => 'login/unauthorized',
            'admin/portfolios/store'    => 'login/unauthorized',
            'admin/products/store'      => 'login/unauthorized',
            'admin/purchases/store'     => 'login/unauthorized',
            'admin/users/store'         => 'login/unauthorized',
            'admin/announcements/store' => 'admin/announcements/edit/1',
            'admin/coupons/store'       => 'admin/coupons/edit/1',
            'admin/pages/store'         => 'admin/pages/edit/1',
            'admin/posts/store'         => 'admin/posts/edit/1',
            'admin/promotions/store'    => 'admin/promotions/edit/1',
        ];
        
        $group = $this->createGroup('Specific', array(
            'admin.announcements.update' => 1,
            'admin.coupons.update' => 1,
            'admin.pages.update' => 1,
            'admin.posts.update' => 1,
            'admin.promotions.update' => 1,
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
     * Test (pass): Specific pages are denied, others allowed
     **/
    public function testSpecificPagesDeniedButNotOthers()
    {
        $this->test_pages = [];
        
        $this->test_redirects = [
            'admin/bundles/edit/1'          => 'admin/bundles',
            'admin/categories/edit/1'       => 'admin/categories',
            'admin/mailinglists/edit/1'     => 'admin/mailinglists',
            'admin/medias/edit/1'           => 'admin/medias',
            'admin/memberships/edit/1'      => 'admin/memberships',
            'admin/modules/edit/1'          => 'admin/modules',
            'admin/orders/edit/1'           => 'admin/orders',
            'admin/portfolios/edit/1'       => 'admin/portfolios',
            'admin/products/edit/1'         => 'admin/products',
            'admin/purchases/edit/1'        => 'admin/purchases',
            'admin/announcements/edit/1'   => 'login/unauthorized',
            'admin/coupons/edit/1'         => 'login/unauthorized',
            'admin/pages/edit/1'           => 'login/unauthorized',
            'admin/posts/edit/1'           => 'login/unauthorized',
            'admin/promotions/edit/1'      => 'login/unauthorized',
        ];
        
        $this->test_posts = [
            'admin/reports/mailinglist' => 'admin/mailinglists',
            'admin/reports/purchases'   => 'admin/purchases',
            'admin/reports/orders'      => 'admin/orders',
            'admin/bundles/store'       => 'admin/bundles/edit/1',
            'admin/categories/store'    => 'admin/categories/edit/1',
            'admin/groups/store'        => 'admin/groups/edit/1',
            'admin/mailinglists/store'  => 'admin/mailinglists/edit/1',
            'admin/medias/store'        => 'admin/medias/edit/1',
            'admin/memberships/store'   => 'admin/memberships/edit/1',
            'admin/modules/store'       => 'admin/modules/edit/1',
            'admin/orders/store'        => 'admin/orders/create',
            'admin/portfolios/store'    => 'admin/portfolios/edit/1',
            'admin/products/store'      => 'admin/products/edit/1',
            'admin/purchases/store'     => 'admin/purchases/edit/1',
            'admin/users/store'         => 'admin/users/edit/1',
            'admin/announcements/store' => 'login/unauthorized',
            'admin/coupons/store'       => 'login/unauthorized',
            'admin/pages/store'         => 'login/unauthorized',
            'admin/posts/store'         => 'login/unauthorized',
            'admin/promotions/store'    => 'login/unauthorized',
        ];
        
        $group = $this->createGroup('Specific', array(
            'admin' => 1, // This opens up all access to sub-route
            'admin.announcements.update' => -1,
            'admin.coupons.update' => -1,
            'admin.pages.update' => -1,
            'admin.posts.update' => -1,
            'admin.promotions.update' => -1,
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
}
