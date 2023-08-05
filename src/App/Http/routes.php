<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

Route::controller('login', 'Redooor\Redminportal\App\Http\Controllers\LoginController');
Route::get('logout', 'Redooor\Redminportal\App\Http\Controllers\LoginController@getLogout');
Route::controller('myaccount', 'Redooor\Redminportal\App\Http\Controllers\MyaccountController');

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
        Route::controller('announcements', 'AnnouncementController');
        Route::controller('categories', 'CategoryController');
        Route::controller('coupons', 'CouponController');
        Route::controller('groups', 'GroupController');
        Route::controller('medias', 'MediaController');
        Route::controller('mailinglists', 'MailinglistController');
        Route::controller('memberships', 'MembershipController');
        Route::controller('modules', 'ModuleController');
        Route::controller('portfolios', 'PortfolioController');
        Route::controller('products', 'ProductController');
        Route::controller('promotions', 'PromotionController');
        Route::controller('purchases', 'PurchaseController');
        Route::controller('reports', 'ReportController');
        Route::controller('users', 'UserController');
        Route::controller('posts', 'PostController');
        Route::controller('pages', 'PageController');
        Route::controller('orders', 'OrderController');
        Route::controller('bundles', 'BundleController');
        Route::controller('images', 'ImageController');
    }
);

Route::group(
    [
        'middleware' => 'Redooor\Redminportal\App\Http\Middleware\Authenticate',
        'namespace' => 'Redooor\Redminportal\App\Http\API',
        'prefix' => 'admin/api'
    ],
    function () {
        Route::controller('email', 'EmailApi');
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
        Route::controller('tag', 'TagApi');
    }
);
