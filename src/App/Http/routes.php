<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

Route::resource('login', 'Redooor\Redminportal\App\Http\Controllers\LoginController');
Route::get('logout', 'Redooor\Redminportal\App\Http\Controllers\LoginController@getLogout');
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
        Route::resource('announcements', 'AnnouncementController');
        Route::resource('categories', 'CategoryController');
        Route::resource('coupons', 'CouponController');
        Route::resource('groups', 'GroupController');
        Route::resource('medias', 'MediaController');
        Route::resource('mailinglists', 'MailinglistController');
        Route::resource('memberships', 'MembershipController');
        Route::resource('modules', 'ModuleController');
        Route::resource('portfolios', 'PortfolioController');
        Route::resource('products', 'ProductController');
        Route::resource('promotions', 'PromotionController');
        Route::resource('purchases', 'PurchaseController');
        Route::resource('reports', 'ReportController');
        Route::resource('users', 'UserController');
        Route::resource('posts', 'PostController');
        Route::resource('pages', 'PageController');
        Route::resource('orders', 'OrderController');
        Route::resource('bundles', 'BundleController');
        Route::resource('images', 'ImageController');
    }
);

Route::group(
    [
        'middleware' => 'Redooor\Redminportal\App\Http\Middleware\Authenticate',
        'namespace' => 'Redooor\Redminportal\App\Http\API',
        'prefix' => 'admin/api'
    ],
    function () {
        Route::resource('email', 'EmailApi');
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
        Route::resource('tag', 'TagApi');
    }
);
