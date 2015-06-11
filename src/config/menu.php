<?php

return array(

    array(
        'name' => 'dashboard',
        'path' => 'admin/dashboard',
        'hide' => false
    ),
    array(
        'name' => 'users_management',
        'path' => '',
        'hide' => false,
        'children' => array(
            array(
                'name' => 'users',
                'path' => 'admin/users',
                'hide' => false
            ),
            array(
                'name' => 'groups',
                'path' => 'admin/groups',
                'hide' => false
            ),
            array(
                'name' => 'mailinglist',
                'path' => 'admin/mailinglists',
                'hide' => false
            )
        )
    ),
    array(
        'name' => 'contents_management',
        'path' => '',
        'hide' => false,
        'children' => array(
            array(
                'name' => 'announcements',
                'path' => 'admin/announcements',
                'hide' => false
            ),
            array(
                'name' => 'posts',
                'path' => 'admin/posts',
                'hide' => false
            ),
            array(
                'name' => 'portfolios',
                'path' => 'admin/portfolios',
                'hide' => false
            ),
            array(
                'name' => 'promotions',
                'path' => 'admin/promotions',
                'hide' => false
            )
        )
    ),
    array(
        'name' => 'store',
        'path' => '',
        'hide' => false,
        'children' => array(
            array(
                'name' => 'categories',
                'path' => 'admin/categories',
                'hide' => false
            ),
            array(
                'name' => 'coupons',
                'path' => 'admin/coupons',
                'hide' => false
            ),
            array(
                'name' => 'physical',
                'path' => '',
                'hide' => false,
                'children' => array(
                    array(
                        'name' => 'products',
                        'path' => 'admin/products',
                        'hide' => false
                    )
                )
            ),
            array(
                'name' => 'digital',
                'path' => '',
                'hide' => false,
                'children' => array(
                    array(
                        'name' => 'memberships',
                        'path' => 'admin/memberships',
                        'hide' => false
                    ),

                    array(
                        'name' => 'modules',
                        'path' => 'admin/modules',
                        'hide' => false
                    ),
                    array(
                        'name' => 'medias',
                        'path' => 'admin/medias',
                        'hide' => false
                    ),
                    array(
                        'name' => 'purchases',
                        'path' => 'admin/purchases',
                        'hide' => false
                    )
                )
            )
        )
    )
);
