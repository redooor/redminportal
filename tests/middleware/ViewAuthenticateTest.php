<?php namespace Redooor\Redminportal\Test;

use Illuminate\Support\Facades\Auth;

class ViewAuthenticateTest extends BaseAuthenticateTest
{
    /**
     * Constructs dataset for tests later
     **/
    public function setUp(): void
    {
        parent::setUp();

        $this->test_pages = [
            'admin/dashboard',
            'admin/announcements',
            'admin/bundles',
            'admin/categories',
            'admin/coupons',
            'admin/groups',
            'admin/images',
            'admin/mailinglists',
            'admin/medias',
            'admin/memberships',
            'admin/modules',
            'admin/orders',
            'admin/pages',
            'admin/portfolios',
            'admin/posts',
            'admin/products',
            'admin/promotions',
            'admin/purchases',
            'admin/reports',
            'admin/users',
            'admin/api/email',
            'admin/api/email/all',
        ];
        
        $this->test_redirects = [
            'admin' => 'admin/dashboard',
            'admin/products/list-variants/1' => 'admin/products',
            'admin/products/view-variant/1' => 'admin/products',
        ];
        
        $this->test_posts = [];
    }
    
    // /**
    //  * Test (pass): Specific pages are allowed, others denied
    //  **/
    // public function testSpecificPagesAllowedButNotOthers()
    // {
    //     $this->test_pages = [
    //         'admin/announcements',
    //         'admin/coupons',
    //         'admin/pages',
    //         'admin/posts',
    //         'admin/promotions',
    //     ];
        
    //     $this->test_redirects = [
    //         'admin/dashboard'       => 'login/unauthorized',
    //         'admin/bundles'         => 'login/unauthorized',
    //         'admin/categories'      => 'login/unauthorized',
    //         'admin/groups'          => 'login/unauthorized',
    //         'admin/images'          => 'login/unauthorized',
    //         'admin/mailinglists'    => 'login/unauthorized',
    //         'admin/medias'          => 'login/unauthorized',
    //         'admin/memberships'     => 'login/unauthorized',
    //         'admin/modules'         => 'login/unauthorized',
    //         'admin/orders'          => 'login/unauthorized',
    //         'admin/portfolios'      => 'login/unauthorized',
    //         'admin/products'        => 'login/unauthorized',
    //         'admin/products/view-variant/1' => 'login/unauthorized',
    //         'admin/purchases'       => 'login/unauthorized',
    //         'admin/reports'         => 'login/unauthorized',
    //         'admin/users'           => 'login/unauthorized',
    //         'admin/api/email'       => 'login/unauthorized',
    //         'admin/api/email/all'   => 'login/unauthorized',
    //     ];
        
    //     $group = $this->createGroup('Specific', array(
    //         'admin.announcements.view' => 1,
    //         'admin.coupons.view' => 1,
    //         'admin.pages.view' => 1,
    //         'admin.posts.view' => 1,
    //         'admin.promotions.view' => 1,
    //     ));
    //     // Assign group to user
    //     $this->user->groups()->save($group);
    //     // Login as user
    //     Auth::loginUsingId($this->user->id);
        
    //     $this->runThroughAllPagesAllowedAccess();
    // }
    
    // /**
    //  * Test (pass): Specific pages are denied, others allowed
    //  **/
    // public function testSpecificPagesDeniedButNotOthers()
    // {
    //     $this->test_pages = [
    //         'admin/dashboard',
    //         'admin/bundles',
    //         'admin/categories',
    //         'admin/groups',
    //         'admin/images',
    //         'admin/mailinglists',
    //         'admin/medias',
    //         'admin/memberships',
    //         'admin/modules',
    //         'admin/orders',
    //         'admin/portfolios',
    //         'admin/products',
    //         'admin/products/view-variant/1',
    //         'admin/purchases',
    //         'admin/reports',
    //         'admin/users',
    //         'admin/api/email',
    //         'admin/api/email/all',
    //     ];
        
    //     $this->test_redirects = [
    //         'admin/announcements'   => 'login/unauthorized',
    //         'admin/coupons'         => 'login/unauthorized',
    //         'admin/pages'           => 'login/unauthorized',
    //         'admin/posts'           => 'login/unauthorized',
    //         'admin/promotions'      => 'login/unauthorized',
    //     ];
        
    //     $group = $this->createGroup('Specific', array(
    //         'admin' => 1, // This opens up all access to sub-route
    //         'admin.announcements.view' => -1,
    //         'admin.coupons.view' => -1,
    //         'admin.pages.view' => -1,
    //         'admin.posts.view' => -1,
    //         'admin.promotions.view' => -1,
    //     ));
    //     // Assign group to user
    //     $this->user->groups()->save($group);
    //     // Login as user
    //     Auth::loginUsingId($this->user->id);
        
    //     $this->runThroughAllPagesAllowedAccess();
    // }
}
