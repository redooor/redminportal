<?php

use Illuminate\Support\Facades\Route;
use Redooor\Redminportal\App\Http\Controllers\AnnouncementController;
use Redooor\Redminportal\App\Http\Controllers\BundleController;
use Redooor\Redminportal\App\Http\Controllers\CategoryController;
use Redooor\Redminportal\App\Http\Controllers\CouponController;
use Redooor\Redminportal\App\Http\Controllers\GroupController;
use Redooor\Redminportal\App\Http\Controllers\HomeController;
use Redooor\Redminportal\App\Http\Controllers\ImageController;
use Redooor\Redminportal\App\Http\Controllers\LoginController;
use Redooor\Redminportal\App\Http\Controllers\MailinglistController;
use Redooor\Redminportal\App\Http\Controllers\MediaController;
use Redooor\Redminportal\App\Http\Controllers\MembershipController;
use Redooor\Redminportal\App\Http\Controllers\ModuleController;
use Redooor\Redminportal\App\Http\Controllers\MyaccountController;
use Redooor\Redminportal\App\Http\Controllers\OrderController;
use Redooor\Redminportal\App\Http\Controllers\PageController;
use Redooor\Redminportal\App\Http\Controllers\PortfolioController;
use Redooor\Redminportal\App\Http\Controllers\PostController;
use Redooor\Redminportal\App\Http\Controllers\ProductController;
use Redooor\Redminportal\App\Http\Controllers\PromotionController;
use Redooor\Redminportal\App\Http\Controllers\PurchaseController;
use Redooor\Redminportal\App\Http\Controllers\ReportController;
use Redooor\Redminportal\App\Http\Controllers\UserController;
use Redooor\Redminportal\App\Http\API\EmailApi;
use Redooor\Redminportal\App\Http\API\TagApi;

/*
|--------------------------------------------------------------------------
| Package Routes
|--------------------------------------------------------------------------
*/

// Login controller
Route::group(
    [
        'middleware' => 'redminsession',
        'prefix' => 'login'
    ],
    function () {
        Route::get('/', [LoginController::class, 'getIndex']);
        Route::post('login', [LoginController::class, 'postLogin']);
        Route::get('unauthorized', [LoginController::class, 'getUnauthorized']);
    }
);

// Logout
Route::group(
    [
        'middleware' => 'redminsession',
        'prefix' => 'logout'
    ],
    function () {
        Route::get('/', [LoginController::class, 'getLogout']);
    }
);

// My Account controller
Route::group(
    [
        'middleware' => 'redminsession',
        'prefix' => 'myaccount'
    ],
    function () {
        Route::get('/', [MyaccountController::class, 'getIndex']);
        Route::post('store', [MyaccountController::class, 'postStore']);
    }
);

Route::group(
    [
        'middleware' => ['redminsession', 'redminauth'],
        'prefix' => 'admin'
    ],
    function () {
        Route::get('/', function () {
            return redirect('admin/dashboard');
        });
        Route::get('dashboard', [HomeController::class, 'home']);

        // Announcements
        // -------------
        Route::group(['prefix' => 'announcements'], function () {
            Route::get('/', [AnnouncementController::class, 'getIndex']);
            Route::get('create', [AnnouncementController::class, 'getCreate']);
            Route::get('edit/{sid}', [AnnouncementController::class, 'getEdit']);
            Route::post('store', [AnnouncementController::class, 'postStore']);
            Route::get('delete/{sid}', [AnnouncementController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [AnnouncementController::class, 'getSort']);
            //
            Route::get('imgremove/{sid}', [AnnouncementController::class, 'getImgremove']);
        });

        // Bundles
        // -------
        Route::group(['prefix' => 'bundles'], function () {
            Route::get('/', [BundleController::class, 'getIndex']);
            Route::get('create', [BundleController::class, 'getCreate']);
            Route::get('edit/{sid}', [BundleController::class, 'getEdit']);
            Route::post('store', [BundleController::class, 'postStore']);
            Route::get('delete/{sid}', [BundleController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [BundleController::class, 'getSort']);
        });

        // Categories
        // ----------
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [CategoryController::class, 'getIndex']);
            Route::get('create', [CategoryController::class, 'getCreate']);
            Route::get('edit/{sid}', [CategoryController::class, 'getEdit']);
            Route::post('store', [CategoryController::class, 'postStore']);
            Route::get('delete/{sid}', [CategoryController::class, 'getDelete']);
            //
            Route::get('imgremove/{sid}', [CategoryController::class, 'getImgremove']);
            Route::get('detail/{sid}', [CategoryController::class, 'getDetail']);
        });

        // Coupons
        // -------
        Route::group(['prefix' => 'coupons'], function () {
            Route::get('/', [CouponController::class, 'getIndex']);
            Route::get('create', [CouponController::class, 'getCreate']);
            Route::get('edit/{sid}', [CouponController::class, 'getEdit']);
            Route::post('store', [CouponController::class, 'postStore']);
            Route::get('delete/{sid}', [CouponController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [CouponController::class, 'getSort']);
            //
            Route::get('categories', [CouponController::class, 'getCategories']);
        });

        // Groups
        // ------
        Route::group(['prefix' => 'groups'], function () {
            Route::get('/', [GroupController::class, 'getIndex']);
            Route::get('create', [GroupController::class, 'getCreate']);
            Route::get('edit/{sid}', [GroupController::class, 'getEdit']);
            Route::post('store', [GroupController::class, 'postStore']);
            Route::get('delete/{sid}', [GroupController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [GroupController::class, 'getSort']);
        });

        // Images
        // ------
        Route::group(['prefix' => 'images'], function () {
            Route::get('/', [ImageController::class, 'getIndex']);
            Route::get('delete/{sid}', [ImageController::class, 'getDelete']);
        });

        // Mailinglists
        // ------------
        Route::group(['prefix' => 'mailinglists'], function () {
            Route::get('/', [MailinglistController::class, 'getIndex']);
            Route::get('create', [MailinglistController::class, 'getCreate']);
            Route::get('edit/{sid}', [MailinglistController::class, 'getEdit']);
            Route::post('store', [MailinglistController::class, 'postStore']);
            Route::get('delete/{sid}', [MailinglistController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [MailinglistController::class, 'getSort']);
        });

        // Medias
        // ------
        Route::group(['prefix' => 'medias'], function () {
            Route::get('/', [MediaController::class, 'getIndex']);
            Route::get('create', [MediaController::class, 'getCreate']);
            Route::get('edit/{sid}', [MediaController::class, 'getEdit']);
            Route::post('store', [MediaController::class, 'postStore']);
            Route::get('delete/{sid}', [MediaController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [MediaController::class, 'getSort']);
            //
            Route::get('imgremove/{sid}', [MediaController::class, 'getImgremove']);
            Route::get('duration/{sid}', [MediaController::class, 'getDuration']);
            Route::get('uploadform/{sid}', [MediaController::class, 'getUploadform']);
            Route::post('upload/{sid}', [MediaController::class, 'postUpload']);
        });

        // Membership
        // ----------
        Route::group(['prefix' => 'memberships'], function () {
            Route::get('/', [MembershipController::class, 'getIndex']);
            Route::get('create', [MembershipController::class, 'getCreate']);
            Route::get('edit/{sid}', [MembershipController::class, 'getEdit']);
            Route::post('store', [MembershipController::class, 'postStore']);
            Route::get('delete/{sid}', [MembershipController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [MembershipController::class, 'getSort']);
        });

        // Modules
        Route::group(['prefix' => 'modules'], function () {
            Route::get('/', [ModuleController::class, 'getIndex']);
            Route::get('create', [ModuleController::class, 'getCreate']);
            Route::get('edit/{sid}', [ModuleController::class, 'getEdit']);
            Route::post('store', [ModuleController::class, 'postStore']);
            Route::get('delete/{sid}', [ModuleController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [ModuleController::class, 'getSort']);
            //
            Route::get('imgremove/{sid}', [ModuleController::class, 'getImgremove']);
            Route::get('medias/{sid}', [ModuleController::class, 'getMedias']);
            Route::get('editmedias/{sid}/{module_id}', [ModuleController::class, 'getEditmedias']);
        });

        // Orders
        // ------
        Route::group(['prefix' => 'orders'], function () {
            Route::get('/', [OrderController::class, 'getIndex']);
            Route::get('create', [OrderController::class, 'getCreate']);
            Route::get('edit/{sid}', [OrderController::class, 'getEdit']);
            Route::post('store', [OrderController::class, 'postStore']);
            Route::get('delete/{sid}', [OrderController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [OrderController::class, 'getSort']);
            //
            Route::get('update/{field?}/{sid?}/{status?}', [OrderController::class, 'getUpdate']);
            Route::post('search', [OrderController::class, 'postSearch']);
            Route::get('search-all/{search?}', [OrderController::class, 'getSearchAll']);
            Route::get('search/{field?}/{search?}', [OrderController::class, 'getSearch']);
        });

        // Pages
        // -----
        Route::group(['prefix' => 'pages'], function () {
            Route::get('/', [PageController::class, 'getIndex']);
            Route::get('create', [PageController::class, 'getCreate']);
            Route::get('edit/{sid}', [PageController::class, 'getEdit']);
            Route::post('store', [PageController::class, 'postStore']);
            Route::get('delete/{sid}', [PageController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [PageController::class, 'getSort']);
            //
            Route::get('imgremove/{sid}', [PageController::class, 'getImgremove']);
        });

        // Portfolio
        // ---------
        Route::group(['prefix' => 'portfolios'], function () {
            Route::get('/', [PortfolioController::class, 'getIndex']);
            Route::get('create', [PortfolioController::class, 'getCreate']);
            Route::get('edit/{sid}', [PortfolioController::class, 'getEdit']);
            Route::post('store', [PortfolioController::class, 'postStore']);
            Route::get('delete/{sid}', [PortfolioController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [PortfolioController::class, 'getSort']);
            //
            Route::get('imgremove/{sid}', [PortfolioController::class, 'getImgremove']);
        });

        // Posts
        // -----
        Route::group(['prefix' => 'posts'], function () {
            Route::get('/', [PostController::class, 'getIndex']);
            Route::get('create', [PostController::class, 'getCreate']);
            Route::get('edit/{sid}', [PostController::class, 'getEdit']);
            Route::post('store', [PostController::class, 'postStore']);
            Route::get('delete/{sid}', [PostController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [PostController::class, 'getSort']);
            //
            Route::get('imgremove/{sid}', [PostController::class, 'getImgremove']);
        });

        // Products
        // --------
        Route::group(['prefix' => 'products'], function () {
            Route::get('/', [ProductController::class, 'getIndex']);
            Route::get('create', [ProductController::class, 'getCreate']);
            Route::get('edit/{sid}', [ProductController::class, 'getEdit']);
            Route::post('store', [ProductController::class, 'postStore']);
            Route::get('delete/{sid}', [ProductController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [ProductController::class, 'getSort']);
            Route::get('imgremove/{sid}', [ProductController::class, 'getImgremove']);
            // ---------------
            // Product variant
            // ---------------
            Route::get('create-variant/{product_id}', [ProductController::class, 'getCreateVariant']);
            Route::get('edit-variant/{product_id}/{sid}', [ProductController::class, 'getEditVariant']);
            Route::get('view-variant/{sid}', [ProductController::class, 'getViewVariant']);
            Route::get('list-variants/{sid}', [ProductController::class, 'getListVariants']);
            Route::get('delete-variant-json/{sid}', [ProductController::class, 'getDeleteVariantJson']);
            Route::get('variant-imgremove/{product_id}/{sid}', [ProductController::class, 'getVariantImgremove']);
        });

        // Promotions
        // ----------
        Route::group(['prefix' => 'promotions'], function () {
            Route::get('/', [PromotionController::class, 'getIndex']);
            Route::get('create', [PromotionController::class, 'getCreate']);
            Route::get('edit/{sid}', [PromotionController::class, 'getEdit']);
            Route::post('store', [PromotionController::class, 'postStore']);
            Route::get('delete/{sid}', [PromotionController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [PromotionController::class, 'getSort']);
            //
            Route::get('imgremove/{sid}', [PromotionController::class, 'getImgremove']);
        });

        // Purchases
        // ---------
        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', [PurchaseController::class, 'getIndex']);
            Route::get('create', [PurchaseController::class, 'getCreate']);
            Route::get('edit/{sid}', [PurchaseController::class, 'getEdit']);
            Route::post('store', [PurchaseController::class, 'postStore']);
            Route::get('delete/{sid}', [PurchaseController::class, 'getDelete']);
        });

        // Reports
        // -------
        Route::group(['prefix' => 'reports'], function () {
            Route::get('/', [ReportController::class, 'getIndex']);
            Route::post('mailinglist', [ReportController::class, 'postMailinglist']);
            Route::post('purchases', [ReportController::class, 'postPurchases']);
            Route::post('orders', [ReportController::class, 'postOrders']);
        });

        // Users
        // -----
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'getIndex']);
            Route::get('create', [UserController::class, 'getCreate']);
            Route::get('edit/{sid}', [UserController::class, 'getEdit']);
            Route::post('store', [UserController::class, 'postStore']);
            Route::get('delete/{sid}', [UserController::class, 'getDelete']);
            Route::get('sort/{sortBy?}/{orderBy?}', [UserController::class, 'getSort']);
            //
            Route::get('activate/{sid}', [UserController::class, 'getActivate']);
            Route::get('deactivate/{sid}', [UserController::class, 'getDeactivate']);
            Route::post('search', [UserController::class, 'postSearch']);
            Route::get('search-all/{search?}', [UserController::class, 'getSearchAll']);
            Route::get('search/{field?}/{search?}', [UserController::class, 'getSearch']);
        });
    }
);

Route::group(
    [
        'middleware' => ['redminsession', 'redminauth'],
        'prefix' => 'admin/api'
    ],
    function () {
        Route::get('email', [EmailApi::class, 'getIndex']);
        Route::get('email/all', [EmailApi::class, 'getAll']);
    }
);

Route::group(
    [
        'middleware' => 'redminsession',
        'prefix' => 'api'
    ],
    function () {
        Route::get('/', function () {
            return redirect('/');
        });
        Route::get('tag', [TagApi::class, 'getIndex']);
        Route::get('tag/name', [TagApi::class, 'getName']);
    }
);
