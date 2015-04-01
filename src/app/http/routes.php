<?php

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'Redooor\Redminportal\App\Http\Controllers', 'prefix' => 'admin'], function () {
    Route::get('/', 'PageController@home');
    Route::controller('users', 'UserController');
    Route::controller('groups', 'GroupController');
    Route::controller('memberships', 'MembershipController');
});

/*
Route::controller('login', 'Redooor\Redminportal\LoginController');
Route::get('logout', 'Redooor\Redminportal\LoginController@getLogout');

Route::group(array('prefix' => 'admin', 'before' => 'auth.sentry'), function()
{
    
    Route::controller('categories',       'Redooor\Redminportal\CategoryController');
    Route::controller('products',         'Redooor\Redminportal\ProductController');
    Route::controller('promotions',       'Redooor\Redminportal\PromotionController');
    Route::controller('announcements',    'Redooor\Redminportal\AnnouncementController');
    Route::controller('portfolios',       'Redooor\Redminportal\PortfolioController');
    Route::controller('medias',           'Redooor\Redminportal\MediaController');
    Route::controller('modules',          'Redooor\Redminportal\ModuleController');
    
    Route::controller('purchases',        'Redooor\Redminportal\PurchaseController');
    Route::controller('coupons',          'Redooor\Redminportal\CouponController');
    Route::controller('mailinglists',     'Redooor\Redminportal\MailinglistController');
    Route::controller('reports',          'Redooor\Redminportal\ReportController');
    // ------------------------------------
    // Legacy support for v0.1.4 and below
    // This will be removed in v0.2.0
    // ------------------------------------
    Route::controller('discounts', 'Redooor\Redminportal\DiscountController');
    Route::get('pricelists', function() {
        return Redirect::to('admin/discounts');
    });
});
*/
