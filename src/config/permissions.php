<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permissible Values
    |--------------------------------------------------------------------------
    |
    | Defines the value for inherit, allow and deny.
    | These values are used in User/Group permission logic.
    | They are shown in Permission Builder when creating user and group.
    |
    */
    'permissions' => [
        [
            'name' => 'Inherit',
            'value' => 0,
            'translate' => 'redminportal::forms.inherit'
        ],
        [
            'name' => 'Allow',
            'value' => 1,
            'translate' => 'redminportal::forms.allow'
        ],
        [
            'name' => 'Deny',
            'value' => -1,
            'translate' => 'redminportal::forms.deny'
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Actions Values
    |--------------------------------------------------------------------------
    |
    | Defines the value for view, create, update and delete actions.
    | These values are used in User/Group permission logic.
    | They are shown in Permission Builder when creating user and group.
    |
    */
    'actions' => [
        [
            'name' => 'View',
            'value' => 'view',
            'translate' => 'redminportal::forms.view'
        ],
        [
            'name' => 'Create',
            'value' => 'create',
            'translate' => 'redminportal::forms.create'
        ],
        [
            'name' => 'Update',
            'value' => 'update',
            'translate' => 'redminportal::forms.update'
        ],
        [
            'name' => 'Delete',
            'value' => 'delete',
            'translate' => 'redminportal::forms.delete'
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Routes Values
    |--------------------------------------------------------------------------
    |
    | Defines all routes into dotted path.
    | These values are used in User/Group permission logic.
    | They are shown in Permission Builder when creating user and group.
    | You can add in more routes for your application to be used in
    | permission logic.
    |
    | You can add in a 'translate' key-value to use the localization feature.
    | E.g. 'translate' => 'filename.key'
    |
    */
    'routes' => [
        [
            'name' => 'admin',
            'value' => 'admin'
        ],
        [
            'name' => 'admin/dashboard',
            'value' => 'admin.dashboard'
        ],
        [
            'name' => 'admin/announcements',
            'value' => 'admin.announcements'
        ],
        [
            'name' => 'admin/bundles',
            'value' => 'admin.bundles'
        ],
        [
            'name' => 'admin/categories',
            'value' => 'admin.categories'
        ],
        [
            'name' => 'admin/coupons',
            'value' => 'admin.coupons'
        ],
        [
            'name' => 'admin/groups',
            'value' => 'admin.groups'
        ],
        [
            'name' => 'admin/images',
            'value' => 'admin.images'
        ],
        [
            'name' => 'admin/mailinglists',
            'value' => 'admin.mailinglists'
        ],
        [
            'name' => 'admin/medias',
            'value' => 'admin.medias'
        ],
        [
            'name' => 'admin/memberships',
            'value' => 'admin.memberships'
        ],
        [
            'name' => 'admin/modules',
            'value' => 'admin.modules'
        ],
        [
            'name' => 'admin/orders',
            'value' => 'admin.orders'
        ],
        [
            'name' => 'admin/pages',
            'value' => 'admin.pages'
        ],
        [
            'name' => 'admin/portfolios',
            'value' => 'admin.portfolios'
        ],
        [
            'name' => 'admin/posts',
            'value' => 'admin.posts'
        ],
        [
            'name' => 'admin/products',
            'value' => 'admin.products'
        ],
        [
            'name' => 'admin/promotions',
            'value' => 'admin.promotions'
        ],
        [
            'name' => 'admin/purchases',
            'value' => 'admin.purchases'
        ],
        [
            'name' => 'admin/reports',
            'value' => 'admin.reports'
        ],
        [
            'name' => 'admin/users',
            'value' => 'admin.users'
        ],
        [
            'name' => 'admin/api',
            'value' => 'admin.api'
        ],
        [
            'name' => 'admin/api/email',
            'value' => 'admin.api.email'
        ]
    ]
];
