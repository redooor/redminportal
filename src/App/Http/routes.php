<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

// Login controller
Route::get('login', 'Redooor\Redminportal\App\Http\Controllers\LoginController@getIndex');
Route::post('login/login', 'Redooor\Redminportal\App\Http\Controllers\LoginController@postLogin');
Route::get('login/unauthorized', 'Redooor\Redminportal\App\Http\Controllers\LoginController@getUnauthorized');
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
        Route::resource('groups', 'GroupController');
        Route::resource('medias', 'MediaController');
        Route::resource('mailinglists', 'MailinglistController');
        Route::resource('memberships', 'MembershipController');
        Route::resource('modules', 'ModuleController');
        Route::resource('portfolios', 'PortfolioController');
        Route::resource('promotions', 'PromotionController');
        Route::resource('purchases', 'PurchaseController');
        Route::resource('reports', 'ReportController');
        Route::resource('users', 'UserController');
        Route::resource('posts', 'PostController');
        Route::resource('pages', 'PageController');
        Route::resource('orders', 'OrderController');
        Route::resource('images', 'ImageController');

        // Announcements
        // ------------
        // Route::resource('announcements', 'AnnouncementController');
        // ------------
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
        // ------------
        // Route::resource('bundles', 'BundleController');
        // ------------
        Route::group(['prefix' => 'bundles'], function () {
            Route::get('/', 'BundleController@getIndex');
            Route::get('create', 'BundleController@getCreate');
            Route::get('edit/{sid}', 'BundleController@getEdit');
            Route::post('store', 'BundleController@postStore');
            Route::get('delete/{sid}', 'BundleController@getDelete');
            Route::get('sort/{sortBy?}/{orderBy?}', 'BundleController@getSort');
        });

        // Categories
        // ------------
        // Route::resource('categories', 'CategoryController');
        // ------------
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
        // ------------
        // Route::resource('coupons', 'CouponController');
        // ------------
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

        // Products
        // ---------
        // Route::resource('products', 'ProductController');
        // ---------
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
