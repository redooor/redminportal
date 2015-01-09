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

A Laravel 4 package as a backend administrating tool for Content Management and Ecommerce sites. Gives you ability to add, edit and remove category, product, promotions and many more. Provides User Interface for administrating users and groups (via Cartalyst Sentry).

# Important note

If you're upgrading to version 0.1.4 or latest master, please refer to the [Upgrade Guide](#upgrade-guide).

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

## Version 0.1.4 (and latest master)
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

## Upgrading to v0.1.4 from <= v0.1.3

Version 0.1.4 removes the unique index of "name" column from "categories" table.
Run the following commands in a terminal to perform database migration for Redminportal:

For users, run:

        ?> php artisan migrate --package=redooor/redminportal

For contributors, run:

        ?> php artisan migrate --bench=redooor/redminportal
