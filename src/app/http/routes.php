<?php

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'Redooor\Redminportal\App\Http\Controllers', 'prefix' => 'admin'], function () {
    Route::get('/', 'PageController@home');
    Route::controller('announcements', 'AnnouncementController');
    Route::controller('categories', 'CategoryController');
    Route::controller('coupons', 'CouponController');
    Route::controller('groups', 'GroupController');
    Route::controller('mailinglists', 'MailinglistController');
    Route::controller('memberships', 'MembershipController');
    Route::controller('portfolios', 'PortfolioController');
    Route::controller('reports', 'ReportController');
    Route::controller('users', 'UserController');
});

/*
Route::controller('login', 'Redooor\Redminportal\LoginController');
Route::get('logout', 'Redooor\Redminportal\LoginController@getLogout');

Route::group(array('prefix' => 'admin', 'before' => 'auth.sentry'), function()
{
    
    
    Route::controller('products',         'Redooor\Redminportal\ProductController');
    Route::controller('promotions',       'Redooor\Redminportal\PromotionController');
    
    
    Route::controller('medias',           'Redooor\Redminportal\MediaController');
    Route::controller('modules',          'Redooor\Redminportal\ModuleController');
    
    Route::controller('purchases',        'Redooor\Redminportal\PurchaseController');
    
    
    
    
});
*/
