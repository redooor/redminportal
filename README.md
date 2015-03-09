# Table of Content
1. [RedminPortal by Redooor](#redminportal-by-redooor)
2. [Models and Features](#models-and-features)
3. [Installation guide for Users](#installation-guide-for-users)
4. [Installation guide for Contributors](#installation-guide-for-contributors)
5. [Testing](#testing)
6. [Versioning](#versioning)
7. [Contributing](#contributing)
8. [Creator](#creator)
9. [License](#license)
10. [Change log](#change-log)
11. [Upgrade Guide](#upgrade-guide)

# RedminPortal by Redooor

A Laravel 4 package as a **backend** administrating tool for Content Management and Ecommerce sites. Gives you ability to add, edit and remove category, product, promotions and many more. Provides User Interface for administrating users and groups (via Cartalyst Sentry).

# Important note

If you're upgrading from version 0.1.4 or below, please refer to the [Upgrade Guide](#upgrade-guide).

# Models and Features

## User Management
* User
* Group

## Content Management
* Announcement
* Portfolio

## Online Store (Physical Products)
* Category
* Discount
* Product
* Promotion

## Membership Subscription (Downloadable Products)
* Category
* Discount
* Media
* Membership
* Module
* ModuleMediaMembership
* Purchase

## Customer Management
* Mailinglist

## Morphs
* Image
* Tag

## Downloadable Reports
1. Downloadable CSV reports for Purchases and Mailinglist.

# Installation guide for Users

1. Add Redminportal to composer.json of a new Laravel application, under "require". Like this:

        "require": {
            "laravel/framework": "4.2.*",
            "redooor/redminportal": "0.1.*"
        },

Due to the use of getID3 package, we need to set the minimum-stability to "dev" but prefer-stable to "true". Like this:

        "minimum-stability": "dev",
        "prefer-stable": true

2. Then run `php composer install` in a terminal.
3. Then add Redooor\Redminportal to your app\config\app.php providers array like this:

        'providers' => array(
            'Illuminate\Foundation\Providers\ArtisanServiceProvider',
            ... omitted ...
            'Illuminate\Workbench\WorkbenchServiceProvider',
            'Cartalyst\Sentry\SentryServiceProvider',
            'Redooor\Redminportal\RedminportalServiceProvider',
        ),

4. Then run `php artisan dump-autoload` in a terminal.
5. Run the following commands in a terminal to perform database migration for both Redminportal and Sentry:

        ?> php artisan migrate --package=cartalyst/sentry
        ?> php artisan migrate --package=redooor/redminportal

6. Run the following in a terminal to seed the database with initial admin username and password:

        ?> php artisan db:seed --class="RedminSeeder"
        Username/password: admin@admin.com/admin

7. Publish package assets by running this in a terminal:

        php artisan asset:publish redooor/redminportal

8. Publish package config by running this in a terminal:

        php artisan config:publish redooor/redminportal

# Installation guide for Contributors

It is recommended that contributors use Laravel Homestead for development because it will provide the same development environment for all of us. Read more about Laravel Homestead [here](http://laravel.com/docs/master/homestead).

1. Clone the Redooor\Redminportal repository into workbench\redooor\redminportal folder.
2. Then add Redooor\Redminportal to your app\config\app.php providers array like this:

        'providers' => array(
            'Illuminate\Foundation\Providers\ArtisanServiceProvider',
            ... omitted ...
            'Illuminate\Workbench\WorkbenchServiceProvider',
            'Cartalyst\Sentry\SentryServiceProvider',
            'Redooor\Redminportal\RedminportalServiceProvider',
        ),

3. Then run `php composer update` in workspace\redoooor\redminportal folder.
4. Then run `php artisan dump-autoload` in a terminal.
5. This Package is dependant on Cartalyst Sentry. In order to do the database migration and seeding, we'll need to add it to the main application's composer.json file, under "require":

        "require": {
            "laravel/framework": "4.2.*",
            "cartalyst/sentry": "2.1.*"
        },

Due to the use of getID3 package, we need to set the minimum-stability to "dev" but prefer-stable to "true". Like this:

        "minimum-stability": "dev",
        "prefer-stable": true
        
6. Then run `php composer update` in the main app folder.
7. Run the following commands in a terminal to perform database migration for both Redminportal and Sentry:

        ?> php artisan migrate --package=cartalyst/sentry
        ?> php artisan migrate --bench=redooor/redminportal

8. Run the following in a terminal to seed the database with initial admin username and password:

        ?> php artisan db:seed --class="RedminSeeder"
        Username/password: admin@admin.com/admin

9. Publish package assets by running this in a terminal:

        php artisan asset:publish --bench=redooor/redminportal

10. Publish package config by running this in a terminal:

        php artisan config:publish --path="workbench/redooor/redminportal/src/config" redooor/redminportal

## Install Grunt and Bower dependencies

1. You need to have nodejs installed
3. cd to workbench/redooor/redminportal
2. Run _npm install_
3. Run _bower install_
4. To build all assets, run _grunt_
5. To compile just the less css, run _grunt less-compile_

# Testing

* Run vendor/bin/phpunit within the package folder.

# Versioning

For transparency into our release cycle and in striving to maintain backward compatibility, Redooor RedminPortal will adhere to the [Semantic Versioning guidelines](http://semver.org/) whenever possible.

# Contributing

Thank you for considering contributing to RedminPortal.
Before any submission, please spend some time reading through the [CONTRIBUTING.md](https://github.com/redooor/redminportal/blob/master/CONTRIBUTING.md) document.

# Creator

Andrews Ang

* [http://twitter.com/kongnir](http://twitter.com/kongnir)
* [http://github.com/kongnir](http://github.com/kongnir)

# License

RedminPortal is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

# Change log

## Under Development, Version 0.2.0 (latest master)
Focus on supporting Laravel 5.0.

## Version 0.1.5
The focus of this update was on cleaning up the code and making sure all tests pass. Improve assets management via Grunt and Bower. Add Coupon module.

### Important:
If you're upgrading from <= v0.1.4, please refer to the [Upgrade Guide](#upgrade-guide).

### Bug fixes:
1. PurchaseControllerTest is producing an error (issue #48).
2. PHPUnit test run out of memory issue, increase from 256MB to 400MB (issue #49).
3. orchestra/testbench is using developer branch (issue #50).
4. PricelistController has been changed to DiscountController (issue #55).
5. admin/discounts does not show correct display count (issue #33).
6. Unable to delete discount from UI due to fix for issue #55 (issue #57).
7. Sorting capability for Users page (issue #70).
8. Sorting capability for Group page (issue #71).
9. Add relationship in classes User and Group (issue #72).
10. Removed redundant model Purchase and database table purchases (issue #60).

### New feature:
Discount module is restricted to membership/module (pricelist) and does not allow usage limit.
Coupon module was created to replace Discount module and to provide greater flexibility.

1. New Coupon module will replace Discount module (issue #62).
2. Ability to add, edit and delete Coupon module (issue #62).
3. Coupon module supports adding coupon for category, product and membership/module (pricelist).
4. Coupon module to allow per coupon limit (issue #18).
5. Coupon module to allow per user limit (issue #19).
6. Coupon module to include a start date (issue #56).
7. Coupon end date should allow specifying time (issue #58).

### Note for Contributors
All assets are now managed via Grunt and Bower. Please refer to [Install Grunt and Bower dependencies](#install-grunt-and-bower-dependencies).

## Version 0.1.4
Released for a major bug fix related to MySQL database and a new feature to allow same sub-category names under different parent.

### Important:
If you're upgrading from <= v0.1.3, please refer to the [Upgrade Guide](#upgrade-guide).

### Bug fixes:
1. Error occurs when create new category, MySQL cannot accept 0 as the foreign key (issue #37).
2. Categories is not showing in the products and modules creation sections (issue #38, related to the fix for issue #37).

### New feature:
1. Allow same sub-category names under different parent (issue #40).

## Version 0.1.3
The focus of this update was on cleaning up the code and making sure all tests pass.

### New features:
1. Create and Delete Purchases (issue #23).
2. Add Edit and Delete functions to GroupController (issue #30).
3. Add Edit function to UserController (issue #31).

### Enhancements:
1. Use Laravel lists for select input instead of foreach (issue #6).
2. Category view page change to hierarchical tree view (issue #12).
3. MediaController sort by created_at before parent and name (issue #16).
4. Need test for MailinglistController sort function (issue #17).
5. Remove the Javascript which add Bootstrap pagination class to ul.pagination (issue #20).
6. Users view page has no pagination (issue #21).
7. Groups view page has no pagination (issue #22).
8. Change test folder names for clarity (issue #26).
9. Create test cases for all models (issue #27).
10. Create test cases for all controllers (issue #28).

### Bug fixes:
1. Category view when empty not showing correct message (issue #13).
2. AJAX UI delete button doesn't prompt warning (issue #14).
3. Prevent same user and pricelist to be added to Purchases (issue #24).
4. All view pages where no record is found, don't show table (issue #25).

## Version 0.1.2

### Enhancements:
1. Temporarily increase memory to 256MB for testing.
2. Users View page: show Groups instead of Permission.
3. Added section head in master layout blade. Can be used by Views to add in styles or scripts in the header without changing the Layout.
4. Consolidated Tinymce into a separate view under Plugins folder (issue #1).
5. Minor UI change: autofocus email input on login page.
6. Category select list changed to hierarchical list (issue #11).

### Bug fixes:
1. Resolved missing translation text on user login and creation page.
2. Hide menu from unauthorised user.
3. Resolved issue #3 where login catch exception not working.
4. Resolved issue #10 where media controller change category unable to move existing files.

### Pull requests:
1. Fixed migration foreign reference by @tusharvikky

## Version 0.1.1
1. Supports Laravel 4.2.
2. Moved Twitter Bootstrap to require-dev.
3. Dynamically creates in-memory sqlite database for test.
4. Fixed an issue when accessing admin/login directly.
5. Added translation zh-tw and zh-cn to login page and main menus.
6. Resolved issue where package Config cannot be overriden.

## Version 0.1.0
1. Supports Laravel 4.2
2. PHPUnit testing and fixed Model test errors.
3. Media supports huge file upload (via a plugin).
4. Able to activate user from admin portal.
5. Able to insert picture in Announcement page.

### External libraries used
* Bootstrap v3.1.*
* jQuery v1.11.1
* [maatwebsite/excel](https://github.com/Maatwebsite/Laravel-Excel)
* [Imagine](https://github.com/avalanche123/Imagine)
* [Sentry](http://docs.cartalyst.com/sentry-2/overview)
* [Jasny Bootstrap](http://jasny.github.io/bootstrap/)
* [Plupload](http://www.plupload.com/)
* [getID3](http://www.getid3.org/)

### Configuration
1. Menu view can be controlled via config/menu.php file.
2. Uploaded image size can be controlled via config/image.php file.
3. Translation capability can be turned on via config/translation.php file.

# Upgrade Guide

## Upgrading to v0.1.5 from <= v0.1.4

### Change config:menu

Version 0.1.5 changed the route from 'admin/pricelists' to 'admin/discounts' due to the change of class name from PricelistController to DiscountController. And added a new route for CouponController.

Change the menu configuration file at 
**app/config/packages/redooor/redminportal/menu.php**

#### For discounts

from:

    array(
        'name' => 'discounts',
        'path' => 'admin/pricelists',
        'hide' => false
    ),

to:

    array(
        'name' => 'discounts',
        'path' => 'admin/discounts',
        'hide' => false
    ),

#### For coupons

add:

    array(
        'name' => 'coupons',
        'path' => 'admin/coupons',
        'hide' => false
    ),

If you didn't change the config file, you can choose to run the following command.

**Caution**: This command will overwrite all your changes to the files in the folder app/config/packages/redooor/redminportal.

For users, run:
        
    php artisan config:publish redooor/redminportal
        
For contributors, run:

    php artisan config:publish --path="workbench/redooor/redminportal/src/config" redooor/redminportal

### Publish assets

Version 0.1.5 changes some structure of the JavaScript and CSS files. You need to run the following command to publish the assets to use the new locations.

**Caution**: This action will overwrite any changes made to the public/packages/redooor/redminportal/assets folder.

For users:

    php artisan asset:publish redooor/redminportal
        
For contributors:

    php artisan asset:publish --bench=redooor/redminportal

### Run migrate

Version 0.1.5 adds a new "coupons" table.

**Caution**: Always backup your database before running this type of command.

Run the following commands in a terminal to perform database migration for Redminportal:

For users, run:

    ?> php artisan migrate --package=redooor/redminportal

For contributors, run:

    ?> php artisan migrate --bench=redooor/redminportal

### Run dump-autoload

Due to the changes in route, you may need to run this command to get the routing work.

    ?> php artisan dump-autoload

If you get an error message saying that DiscountController is missing, open 

For users:

    vendor/redooor/redminportal/src/routes.php 

For contributors:

    workbench/redooor/redminportal/src/routes.php 

and comment off those lines with DiscountController and CouponController. 

Then run dump-autoload again. Once it's done, go back to routes.php and undo the comment.

## Upgrading to v0.1.4 from <= v0.1.3

Version 0.1.4 removes the unique index of "name" column from "categories" table.

**Caution**: Always backup your database before running this type of command.

Run the following commands in a terminal to perform database migration for Redminportal:

For users, run:

    ?> php artisan migrate --package=redooor/redminportal

For contributors, run:

    ?> php artisan migrate --bench=redooor/redminportal
