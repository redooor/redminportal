<?php namespace Redooor\Redminportal\Test;

class EditAuthenticateTest extends BaseAuthenticateTest
{
    /**
     * Constructs dataset for tests later
     **/
    public function __construct()
    {
        $this->test_pages = [
            'admin/groups/edit/1',
            'admin/products/edit-variant/1/1',
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
        ];
        
        $this->test_posts = [];
    }
}
