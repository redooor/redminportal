<?php

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

Route::controller('login', 'Redooor\Redminportal\App\Http\Controllers\LoginController');
Route::get('logout', 'Redooor\Redminportal\App\Http\Controllers\LoginController@getLogout');

Route::group(['middleware' => 'Redooor\Redminportal\App\Http\Middleware\Authenticate', 'namespace' => 'Redooor\Redminportal\App\Http\Controllers', 'prefix' => 'admin'], function () {
    Route::get('/', function() {
        return redirect('admin/dashboard');
    });
    Route::get('dashboard', 'PageController@home');
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
});
