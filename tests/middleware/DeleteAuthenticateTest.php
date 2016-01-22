<?php namespace Redooor\Redminportal\Test;

class DeleteAuthenticateTest extends BaseAuthenticateTest
{
    /**
     * Constructs dataset for tests later
     **/
    public function __construct()
    {
        $this->test_pages = [
            'admin/products/delete-variant-json/1',
        ];
        
        $this->test_redirects = [
            'admin/announcements/delete/1'  => '/',
            'admin/bundles/delete/1'        => '/',
            'admin/categories/delete/1'     => '/',
            'admin/coupons/delete/1'        => '/',
            'admin/groups/delete/1'         => '/',
            'admin/images/delete/1'         => '/',
            'admin/mailinglists/delete/1'   => '/',
            'admin/medias/delete/1'         => '/',
            'admin/memberships/delete/1'    => '/',
            'admin/modules/delete/1'        => '/',
            'admin/orders/delete/1'         => '/',
            'admin/pages/delete/1'          => '/',
            'admin/portfolios/delete/1'     => '/',
            'admin/posts/delete/1'          => '/',
            'admin/products/delete/1'       => '/',
            'admin/promotions/delete/1'     => '/',
            'admin/purchases/delete/1'      => '/',
            'admin/users/delete/1'          => '/',
        ];
        
        $this->test_posts = [];
    }
}
