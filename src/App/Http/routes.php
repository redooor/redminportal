<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

// Login controller
Route::group(
    [
        'middleware' => 'redminsession',
        'namespace' => 'Redooor\Redminportal\App\Http\Controllers',
        'prefix' => 'login'
    ],
    function () {
        Route::get('/', 'LoginController@getIndex');
        Route::post('login', 'LoginController@postLogin');
        Route::get('unauthorized', 'LoginController@getUnauthorized');
    }
);

// Logout
Route::group(
    [
        'middleware' => 'redminsession',
        'namespace' => 'Redooor\Redminportal\App\Http\Controllers',
        'prefix' => 'logout'
    ],
    function () {
        Route::get('/', 'LoginController@getLogout');
    }
);

// My Account controller
Route::group(
    [
        'middleware' => 'redminsession',
        'namespace' => 'Redooor\Redminportal\App\Http\Controllers',
        'prefix' => 'myaccount'
    ],
    function () {
        Route::get('/', 'MyaccountController@getIndex');
        Route::post('store', 'MyaccountController@postStore');
    }
);

Route::group(
    [
        'middleware' => ['redminsession', 'redminauth'],
        'namespace' => 'Redooor\Redminportal\App\Http\Controllers',
        'prefix' => 'admin'
    ],
    function () {
        Route::get('/', function () {
            return redirect('admin/dashboard');
        });
        Route::get('dashboard', 'HomeController@home');

        // Announcements
        // -------------
        Route::group(['prefix' => 'announcements'], function () {
            Route::get('/', 'AnnouncementController@getIndex');
            Route::get('create', 'AnnouncementController@getCreate');
            Route::get('edit/{sid}', 'AnnouncementController@getEdit');
            Route::post('store', 'AnnouncementController@postStore');
            Route::get('delete/{sid}', 'AnnouncementController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'AnnouncementController@getSort');
            //
            Route::get('imgremove/{sid}', 'AnnouncementController@getImgremove');
        });

        // Bundles
        // -------
        Route::group(['prefix' => 'bundles'], function () {
            Route::get('/', 'BundleController@getIndex');
            Route::get('create', 'BundleController@getCreate');
            Route::get('edit/{sid}', 'BundleController@getEdit');
            Route::post('store', 'BundleController@postStore');
            Route::get('delete/{sid}', 'BundleController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'BundleController@getSort');
        });

        // Categories
        // ----------
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', 'CategoryController@getIndex');
            Route::get('create', 'CategoryController@getCreate');
            Route::get('edit/{sid}', 'CategoryController@getEdit');
            Route::post('store', 'CategoryController@postStore');
            Route::get('delete/{sid}', 'CategoryController@getDelete');
            //
            Route::get('imgremove/{sid}', 'CategoryController@getImgremove');
            Route::get('detail/{sid}', 'CategoryController@getDetail');
        });

        // Coupons
        // -------
        Route::group(['prefix' => 'coupons'], function () {
            Route::get('/', 'CouponController@getIndex');
            Route::get('create', 'CouponController@getCreate');
            Route::get('edit/{sid}', 'CouponController@getEdit');
            Route::post('store', 'CouponController@postStore');
            Route::get('delete/{sid}', 'CouponController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'CouponController@getSort');
            //
            Route::get('categories', 'CouponController@getCategories');
        });

        // Groups
        // ------
        Route::group(['prefix' => 'groups'], function () {
            Route::get('/', 'GroupController@getIndex');
            Route::get('create', 'GroupController@getCreate');
            Route::get('edit/{sid}', 'GroupController@getEdit');
            Route::post('store', 'GroupController@postStore');
            Route::get('delete/{sid}', 'GroupController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'GroupController@getSort');
        });

        // Images
        // ------
        Route::group(['prefix' => 'images'], function () {
            Route::get('/', 'ImageController@getIndex');
            Route::get('delete/{sid}', 'ImageController@getDelete');
        });

        // Mailinglists
        // ------------
        Route::group(['prefix' => 'mailinglists'], function () {
            Route::get('/', 'MailinglistController@getIndex');
            Route::get('create', 'MailinglistController@getCreate');
            Route::get('edit/{sid}', 'MailinglistController@getEdit');
            Route::post('store', 'MailinglistController@postStore');
            Route::get('delete/{sid}', 'MailinglistController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'MailinglistController@getSort');
        });

        // Medias
        // ------
        Route::group(['prefix' => 'medias'], function () {
            Route::get('/', 'MediaController@getIndex');
            Route::get('create', 'MediaController@getCreate');
            Route::get('edit/{sid}', 'MediaController@getEdit');
            Route::post('store', 'MediaController@postStore');
            Route::get('delete/{sid}', 'MediaController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'MediaController@getSort');
            //
            Route::get('imgremove/{sid}', 'MediaController@getImgremove');
            Route::get('duration/{sid}', 'MediaController@getDuration');
            Route::get('uploadform/{sid}', 'MediaController@getUploadform');
            Route::post('upload/{sid}', 'MediaController@postUpload');
        });

        // Membership
        // ----------
        Route::group(['prefix' => 'memberships'], function () {
            Route::get('/', 'MembershipController@getIndex');
            Route::get('create', 'MembershipController@getCreate');
            Route::get('edit/{sid}', 'MembershipController@getEdit');
            Route::post('store', 'MembershipController@postStore');
            Route::get('delete/{sid}', 'MembershipController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'MembershipController@getSort');
        });

        // Modules
        Route::group(['prefix' => 'modules'], function () {
            Route::get('/', 'ModuleController@getIndex');
            Route::get('create', 'ModuleController@getCreate');
            Route::get('edit/{sid}', 'ModuleController@getEdit');
            Route::post('store', 'ModuleController@postStore');
            Route::get('delete/{sid}', 'ModuleController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'ModuleController@getSort');
            //
            Route::get('imgremove/{sid}', 'ModuleController@getImgremove');
            Route::get('medias/{sid}', 'ModuleController@getMedias');
            Route::get('editmedias/{sid}/{module_id}', 'ModuleController@getEditmedias');
        });

        // Orders
        // ------
        Route::group(['prefix' => 'orders'], function () {
            Route::get('/', 'OrderController@getIndex');
            Route::get('create', 'OrderController@getCreate');
            Route::get('edit/{sid}', 'OrderController@getEdit'); // This will redirect to order
            Route::post('store', 'OrderController@postStore');
            Route::get('delete/{sid}', 'OrderController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'OrderController@getSort');
            //
            Route::get('update/{field?}/{sid?}/{status?}', 'OrderController@getUpdate');
            Route::post('search', 'OrderController@postSearch');
            Route::get('search-all/{search?}', 'OrderController@getSearchAll');
            Route::get('search/{field?}/{search?}', 'OrderController@getSearch');
        });

        // Pages
        // -----
        Route::group(['prefix' => 'pages'], function () {
            Route::get('/', 'PageController@getIndex');
            Route::get('create', 'PageController@getCreate');
            Route::get('edit/{sid}', 'PageController@getEdit');
            Route::post('store', 'PageController@postStore');
            Route::get('delete/{sid}', 'PageController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'PageController@getSort');
            //
            Route::get('imgremove/{sid}', 'PageController@getImgremove');
        });

        // Portfolio
        // ---------
        Route::group(['prefix' => 'portfolios'], function () {
            Route::get('/', 'PortfolioController@getIndex');
            Route::get('create', 'PortfolioController@getCreate');
            Route::get('edit/{sid}', 'PortfolioController@getEdit');
            Route::post('store', 'PortfolioController@postStore');
            Route::get('delete/{sid}', 'PortfolioController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'PortfolioController@getSort');
            //
            Route::get('imgremove/{sid}', 'PortfolioController@getImgremove');
        });

        // Posts
        // -----
        Route::group(['prefix' => 'posts'], function () {
            Route::get('/', 'PostController@getIndex');
            Route::get('create', 'PostController@getCreate');
            Route::get('edit/{sid}', 'PostController@getEdit');
            Route::post('store', 'PostController@postStore');
            Route::get('delete/{sid}', 'PostController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'PostController@getSort');
            //
            Route::get('imgremove/{sid}', 'PostController@getImgremove');
        });

        // Products
        // --------
        Route::group(['prefix' => 'products'], function () {
            Route::get('/', 'ProductController@getIndex');
            Route::get('create', 'ProductController@getCreate');
            Route::get('edit/{sid}', 'ProductController@getEdit');
            Route::post('store', 'ProductController@postStore');
            Route::get('delete/{sid}', 'ProductController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'ProductController@getSort');
            Route::get('imgremove/{sid}', 'ProductController@getImgremove');
            // ---------------
            // Product variant
            // ---------------
            Route::get('create-variant/{product_id}', 'ProductController@getCreateVariant');
            Route::get('edit-variant/{product_id}/{sid}', 'ProductController@getEditVariant');
            Route::get('view-variant/{sid}', 'ProductController@getViewVariant');
            Route::get('list-variants/{sid}', 'ProductController@getListVariants');
            Route::get('delete-variant-json/{sid}', 'ProductController@getDeleteVariantJson');
            Route::get('variant-imgremove/{product_id}/{sid}', 'ProductController@getVariantImgremove');
        });

        // Promotions
        // ----------
        Route::group(['prefix' => 'promotions'], function () {
            Route::get('/', 'PromotionController@getIndex');
            Route::get('create', 'PromotionController@getCreate');
            Route::get('edit/{sid}', 'PromotionController@getEdit');
            Route::post('store', 'PromotionController@postStore');
            Route::get('delete/{sid}', 'PromotionController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'PromotionController@getSort');
            //
            Route::get('imgremove/{sid}', 'PromotionController@getImgremove');
        });

        // Purchases
        // ---------
        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', 'PurchaseController@getIndex');
            Route::get('create', 'PurchaseController@getCreate');
            Route::get('edit/{sid}', 'PurchaseController@getEdit');
            Route::post('store', 'PurchaseController@postStore');
            Route::get('delete/{sid}', 'PurchaseController@getDelete');
        });

        // Reports
        // -------
        Route::group(['prefix' => 'reports'], function () {
            Route::get('/', 'ReportController@getIndex');
            Route::post('mailinglist', 'ReportController@postMailinglist');
            Route::post('purchases', 'ReportController@postPurchases');
            Route::post('orders', 'ReportController@postOrders');
        });
        
        // Users
        // -----
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', 'UserController@getIndex');
            Route::get('create', 'UserController@getCreate');
            Route::get('edit/{sid}', 'UserController@getEdit');
            Route::post('store', 'UserController@postStore');
            Route::get('delete/{sid}', 'UserController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'UserController@getSort');
            //
            Route::get('activate/{sid}', 'UserController@getActivate');
            Route::get('deactivate/{sid}', 'UserController@getDeactivate');
            Route::post('search', 'UserController@postSearch');
            Route::get('search-all/{search?}', 'UserController@getSearchAll');
            Route::get('search/{field?}/{search?}', 'UserController@getSearch');
        });
    }
);

Route::group(
    [
        'middleware' => ['redminsession', 'redminauth'],
        'namespace' => 'Redooor\Redminportal\App\Http\API',
        'prefix' => 'admin/api'
    ],
    function () {
        Route::get('email', 'EmailApi@getIndex');
        Route::get('email/all', 'EmailApi@getAll');
    }
);

Route::group(
    [
        'middleware' => 'redminsession',
        'namespace' => 'Redooor\Redminportal\App\Http\API',
        'prefix' => 'api'
    ],
    function () {
        Route::get('/', function () {
            return redirect('/');
        });
        Route::get('tag', 'TagApi@getIndex');
        Route::get('tag/name', 'TagApi@getName');
    }
);
