# RedminPortal by Redooor

A Laravel 5.0 package as a **backend** administrating tool for Content Management and Ecommerce sites. Gives you ability to add, edit and remove category, product, promotions and many more. Provides User Interface for administrating users and groups.

Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

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

# Important note

Version 0.2.0 is **NOT** backward compatible.
Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

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

        ?> php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations"
        ?> composer dump-autoload
        ?> php artisan migrate

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
Before any submission, please spend some time reading through the [CONTRIBUTING.md](CONTRIBUTING.md) document.

# Creator

Andrews Ang

* [http://twitter.com/kongnir](http://twitter.com/kongnir)
* [http://github.com/kongnir](http://github.com/kongnir)

# License

RedminPortal is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

# Change log

Refer to [CHANGELOG.md](CHANGELOG.md)

# Upgrade Guide

Refer to [UPGRADE.md](UPGRADE.md)
