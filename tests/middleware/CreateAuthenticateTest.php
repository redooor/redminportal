<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class CreateAuthenticateTest extends BaseAuthenticateTest
{
    /**
     * Constructs dataset for tests later
     **/
    public function setUp(): void
    {
        parent::setup();

        $this->test_pages = [
            'admin/announcements/create',
            'admin/bundles/create',
            'admin/categories/create',
            'admin/coupons/create',
            'admin/groups/create',
            'admin/mailinglists/create',
            'admin/medias/create',
            'admin/memberships/create',
            'admin/modules/create',
            'admin/orders/create',
            'admin/pages/create',
            'admin/portfolios/create',
            'admin/posts/create',
            'admin/products/create',
            'admin/promotions/create',
            'admin/purchases/create',
            'admin/users/create'
        ];
        
        $this->test_redirects = [
            'admin/products/create-variant/1' => 'admin/products',
        ];
        
        $this->test_posts = [
            'admin/reports/mailinglist' => 'admin/mailinglists',
            'admin/reports/purchases'   => 'admin/purchases',
            'admin/reports/orders'      => 'admin/orders',
            'admin/announcements/store' => 'admin/announcements/create',
            'admin/bundles/store'       => 'admin/bundles/create',
            'admin/categories/store'    => 'admin/categories/create',
            'admin/coupons/store'       => 'admin/coupons/create',
            'admin/groups/store'        => 'admin/groups/create',
            'admin/mailinglists/store'  => 'admin/mailinglists/create',
            'admin/medias/store'        => 'admin/medias/create',
            'admin/memberships/store'   => 'admin/memberships/create',
            'admin/modules/store'       => 'admin/modules/create',
            'admin/orders/store'        => 'admin/orders/create',
            'admin/pages/store'         => 'admin/pages/create',
            'admin/portfolios/store'    => 'admin/portfolios/create',
            'admin/posts/store'         => 'admin/posts/create',
            'admin/products/store'      => 'admin/products/create',
            'admin/promotions/store'    => 'admin/promotions/create',
            'admin/purchases/store'     => 'admin/purchases/create',
            'admin/users/store'         => 'admin/users/create',
        ];
    }
    
    /**
     * Test (pass): Specific pages are allowed, others denied
     **/
    public function testSpecificPagesAllowedButNotOthers()
    {
        $this->test_pages = [
            'admin/announcements/create',
            'admin/coupons/create',
            'admin/pages/create',
            'admin/posts/create',
            'admin/promotions/create',
        ];
        
        $this->test_redirects = [
            'admin/dashboard'               => 'login/unauthorized',
            'admin/bundles/create'          => 'login/unauthorized',
            'admin/categories/create'       => 'login/unauthorized',
            'admin/groups/create'           => 'login/unauthorized',
            'admin/images'                  => 'login/unauthorized',
            'admin/mailinglists/create'     => 'login/unauthorized',
            'admin/medias/create'           => 'login/unauthorized',
            'admin/memberships/create'      => 'login/unauthorized',
            'admin/modules/create'          => 'login/unauthorized',
            'admin/orders/create'           => 'login/unauthorized',
            'admin/portfolios/create'       => 'login/unauthorized',
            'admin/products/create'         => 'login/unauthorized',
            'admin/products/create-variant/1' => 'login/unauthorized',
            'admin/purchases/create'        => 'login/unauthorized',
            'admin/reports'                 => 'login/unauthorized',
            'admin/users/create'            => 'login/unauthorized',
            'admin/api/email'               => 'login/unauthorized',
            'admin/api/email/all'           => 'login/unauthorized',
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
            'admin/announcements/store' => 'admin/announcements/create',
            'admin/coupons/store'       => 'admin/coupons/create',
            'admin/pages/store'         => 'admin/pages/create',
            'admin/posts/store'         => 'admin/posts/create',
            'admin/promotions/store'    => 'admin/promotions/create',
        ];
        
        $group = $this->createGroup('Specific', array(
            'admin.announcements.create' => 1,
            'admin.coupons.create' => 1,
            'admin.pages.create' => 1,
            'admin.posts.create' => 1,
            'admin.promotions.create' => 1,
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::guard('redminguard')->loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
    
    /**
     * Test (pass): Specific pages are denied, others allowed
     **/
    public function testSpecificPagesDeniedButNotOthers()
    {
        $this->test_pages = null; // Empty
        $this->test_pages = [
            'admin/bundles/create',
            'admin/categories/create',
            'admin/groups/create',
            'admin/mailinglists/create',
            'admin/medias/create',
            'admin/memberships/create',
            'admin/modules/create',
            'admin/orders/create',
            'admin/portfolios/create',
            'admin/products/create',
            'admin/purchases/create',
            'admin/users/create'
        ];
        
        $this->test_redirects = null; // Empty
        $this->test_redirects = [
            'admin/announcements/create'   => 'login/unauthorized',
            'admin/coupons/create'         => 'login/unauthorized',
            'admin/pages/create'           => 'login/unauthorized',
            'admin/posts/create'           => 'login/unauthorized',
            'admin/promotions/create'      => 'login/unauthorized',
        ];
        
        $this->test_posts = null; // Empty
        $this->test_posts = [
            'admin/reports/mailinglist' => 'admin/mailinglists',
            'admin/reports/purchases'   => 'admin/purchases',
            'admin/reports/orders'      => 'admin/orders',
            'admin/bundles/store'       => 'admin/bundles/create',
            'admin/categories/store'    => 'admin/categories/create',
            'admin/groups/store'        => 'admin/groups/create',
            'admin/mailinglists/store'  => 'admin/mailinglists/create',
            'admin/medias/store'        => 'admin/medias/create',
            'admin/memberships/store'   => 'admin/memberships/create',
            'admin/modules/store'       => 'admin/modules/create',
            'admin/orders/store'        => 'admin/orders/create',
            'admin/portfolios/store'    => 'admin/portfolios/create',
            'admin/products/store'      => 'admin/products/create',
            'admin/purchases/store'     => 'admin/purchases/create',
            'admin/users/store'         => 'admin/users/create',
            'admin/announcements/store' => 'login/unauthorized',
            'admin/coupons/store'       => 'login/unauthorized',
            'admin/pages/store'         => 'login/unauthorized',
            'admin/posts/store'         => 'login/unauthorized',
            'admin/promotions/store'    => 'login/unauthorized',
        ];
        
        $group = $this->createGroup('Specific', array(
            'admin' => 1, // This opens up all access to sub-route
            'admin.announcements.create' => -1,
            'admin.coupons.create' => -1,
            'admin.pages.create' => -1,
            'admin.posts.create' => -1,
            'admin.promotions.create' => -1,
        ));
        // Assign group to user
        $this->user->groups()->save($group);
        // Login as user
        Auth::guard('redminguard')->loginUsingId($this->user->id);
        
        $this->runThroughAllPagesAllowedAccess();
    }
}
