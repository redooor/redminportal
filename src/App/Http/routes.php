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
Route::get('logout', 'Redooor\Redminportal\App\Http\Controllers\LoginController@getLogout');

// Account controller
Route::resource('myaccount', 'Redooor\Redminportal\App\Http\Controllers\MyaccountController');

Route::group(
    [
        'middleware' => 'Redooor\Redminportal\App\Http\Middleware\Authenticate',
        'namespace' => 'Redooor\Redminportal\App\Http\Controllers',
        'prefix' => 'admin'
    ],
    function () {
        Route::get('/', function () {
            return redirect('admin/dashboard');
        });
        Route::get('dashboard', 'HomeController@home');
        Route::resource('portfolios', 'PortfolioController');
        Route::resource('promotions', 'PromotionController');
        Route::resource('purchases', 'PurchaseController');
        Route::resource('reports', 'ReportController');
        Route::resource('users', 'UserController');
        Route::resource('posts', 'PostController');
        Route::resource('pages', 'PageController');
        Route::resource('orders', 'OrderController');

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
    }
);

Route::group(
    [
        'middleware' => 'Redooor\Redminportal\App\Http\Middleware\Authenticate',
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
