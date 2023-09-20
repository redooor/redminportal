<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class DeleteAuthenticateTest extends BaseAuthenticateTest
{
    /**
     * Constructs dataset for tests later
     **/
    public function setup(): void
    {
        parent::setUp();

        $this->test_pages = [
            '/admin/products/delete-variant-json/1',
        ];
        
        // Redirected to previous page
        $this->test_redirects = [
            '/admin/announcements/delete/1'  => '/admin/products/delete-variant-json/1',
            '/admin/bundles/delete/1'        => '/admin/announcements/delete/1',
            '/admin/categories/delete/1'     => '/admin/bundles/delete/1',
            '/admin/coupons/delete/1'        => '/admin/categories/delete/1',
            '/admin/groups/delete/1'         => '/admin/coupons/delete/1',
            '/admin/images/delete/1'         => '/admin/groups/delete/1',
            '/admin/mailinglists/delete/1'   => '/admin/images/delete/1',
            '/admin/medias/delete/1'         => '/admin/mailinglists/delete/1',
            '/admin/memberships/delete/1'    => '/admin/medias/delete/1',
            '/admin/modules/delete/1'        => '/admin/memberships/delete/1',
            '/admin/orders/delete/1'         => '/admin/modules/delete/1',
            '/admin/pages/delete/1'          => '/admin/orders/delete/1',
            '/admin/portfolios/delete/1'     => '/admin/pages/delete/1',
            '/admin/posts/delete/1'          => '/admin/portfolios/delete/1',
            '/admin/products/delete/1'       => '/admin/posts/delete/1',
            '/admin/promotions/delete/1'     => '/admin/products/delete/1',
            '/admin/purchases/delete/1'      => '/admin/promotions/delete/1',
            '/admin/users/delete/1'          => '/admin/purchases/delete/1'
        ];
        
        $this->test_posts = [];
    }
    
    /**
     * Test (pass): Specific pages are allowed, others denied
     **/
    public function testSpecificPagesAllowedButNotOthers()
    {
        $this->test_pages = [];
        
        // Redirected to previous page
        $this->test_redirects = [
            '/admin/bundles/delete/1'        => 'login/unauthorized',
            '/admin/categories/delete/1'     => 'login/unauthorized',
            '/admin/groups/delete/1'         => 'login/unauthorized',
            '/admin/images/delete/1'         => 'login/unauthorized',
            '/admin/mailinglists/delete/1'   => 'login/unauthorized',
            '/admin/medias/delete/1'         => 'login/unauthorized',
            '/admin/memberships/delete/1'    => 'login/unauthorized',
            '/admin/modules/delete/1'        => 'login/unauthorized',
            '/admin/orders/delete/1'         => 'login/unauthorized',
            '/admin/portfolios/delete/1'     => 'login/unauthorized',
            '/admin/products/delete/1'       => 'login/unauthorized',
            '/admin/purchases/delete/1'      => 'login/unauthorized',
            '/admin/users/delete/1'          => 'login/unauthorized',
            '/admin/announcements/delete/1'  => '/admin/users/delete/1',
            '/admin/coupons/delete/1'        => '/admin/announcements/delete/1',
            '/admin/pages/delete/1'          => '/admin/coupons/delete/1',
            '/admin/posts/delete/1'          => '/admin/pages/delete/1',
            '/admin/promotions/delete/1'     => '/admin/posts/delete/1'
        ];
        
        $this->test_posts = [];
        
        $group = $this->createGroup('Specific', array(
            'admin.announcements.delete' => 1,
            'admin.coupons.delete' => 1,
            'admin.pages.delete' => 1,
            'admin.posts.delete' => 1,
            'admin.promotions.delete' => 1,
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
        
        // Redirected to previous page
        $this->test_redirects = [
            '/admin/bundles/delete/1'        => '/',
            '/admin/categories/delete/1'     => '/admin/bundles/delete/1',
            '/admin/groups/delete/1'         => '/admin/categories/delete/1',
            '/admin/images/delete/1'         => '/admin/groups/delete/1',
            '/admin/mailinglists/delete/1'   => '/admin/images/delete/1',
            '/admin/medias/delete/1'         => '/admin/mailinglists/delete/1',
            '/admin/memberships/delete/1'    => '/admin/medias/delete/1',
            '/admin/modules/delete/1'        => '/admin/memberships/delete/1',
            '/admin/orders/delete/1'         => '/admin/modules/delete/1',
            '/admin/portfolios/delete/1'     => '/admin/orders/delete/1',
            '/admin/products/delete/1'       => '/admin/portfolios/delete/1',
            '/admin/purchases/delete/1'      => '/admin/products/delete/1',
            '/admin/users/delete/1'          => 'login/unauthorized',
            '/admin/announcements/delete/1'  => 'login/unauthorized',
            '/admin/coupons/delete/1'        => 'login/unauthorized',
            '/admin/pages/delete/1'          => 'login/unauthorized',
            '/admin/posts/delete/1'          => 'login/unauthorized',
            '/admin/promotions/delete/1'     => 'login/unauthorized',
        ];
        
        $this->test_posts = [];
        
        $group = $this->createGroup('Specific', array(
            'admin' => 1, // This opens up all access to sub-route
            'admin.announcements.delete' => -1,
            'admin.coupons.delete' => -1,
            'admin.pages.delete' => -1,
            'admin.posts.delete' => -1,
            'admin.promotions.delete' => -1,
            'admin.users.delete' => -1,
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
}
