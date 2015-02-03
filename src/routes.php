<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::controller('login', 'Redooor\Redminportal\LoginController');
Route::get('logout', 'Redooor\Redminportal\LoginController@getLogout');

Route::group(array('prefix' => 'admin', 'before' => 'auth.sentry'), function()
{
    Route::get('/', function(){
        return View::make('redminportal::pages/home');
    });
    Route::controller('users',            'Redooor\Redminportal\UserController');
    Route::controller('groups',           'Redooor\Redminportal\GroupController');
    Route::controller('categories',       'Redooor\Redminportal\CategoryController');
    Route::controller('products',         'Redooor\Redminportal\ProductController');
    Route::controller('promotions',       'Redooor\Redminportal\PromotionController');
    Route::controller('announcements',    'Redooor\Redminportal\AnnouncementController');
    Route::controller('portfolios',       'Redooor\Redminportal\PortfolioController');
    Route::controller('medias',           'Redooor\Redminportal\MediaController');
    Route::controller('modules',          'Redooor\Redminportal\ModuleController');
    Route::controller('memberships',      'Redooor\Redminportal\MembershipController');
    Route::controller('purchases',        'Redooor\Redminportal\PurchaseController');
    Route::controller('discounts',        'Redooor\Redminportal\DiscountController');
    Route::controller('mailinglists',     'Redooor\Redminportal\MailinglistController');
    Route::controller('reports',          'Redooor\Redminportal\ReportController');
    // ------------------------------------
    // Legacy support for v0.1.4 and below
    // This will be removed in v0.2.0
    // ------------------------------------
    Route::get('pricelists', function() {
        return Redirect::to('admin/discounts');
    });
});
