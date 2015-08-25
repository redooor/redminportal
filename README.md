![alt text][logo]

[logo]: http://www.redooor.com/assets/img/redminportal_logo_sm.png "RedminPortal"

# RedminPortal by Redooor

A Laravel 5.1 package as a **backend** administrating tool for Content Management and Ecommerce sites. Gives you ability to add, edit and remove category, product, promotions and many more. Provides User Interface for administrating users and groups.

* Looking for RedminPortal for Laravel 5.0? Visit the [v0.2 Branch](https://github.com/redooor/redminportal/tree/v0.2).
* Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

# Table of Content
1. [Compatibility](#compatibility)
2. [Models and Features](#models-and-features)
3. [Installation guide for Users](#installation-guide-for-users)
4. [Installation guide for Contributors](#installation-guide-for-contributors)
5. [Testing](#testing)
6. [Versioning](#versioning)
7. [Contributing](#contributing)
8. [Creator](#creator)
9. [License](#license)
10. [External Libraries Used](#external-libraries-used)
11. [Change log](#change-log)
12. [Upgrade Guide](#upgrade-guide)

#Compatibility

| Laravel | RedminPortal |
|:-------:|:------------:|
| 4.2     | 0.1.x        |
| 5.0     | 0.2.x        |
| 5.1     | 0.3.x        |

# Important note

Version 0.4.0 **may break** your front-end due to the change in UserPricelist. Refer to [UPGRADE.md](UPGRADE.md) for the upgrading instructions.

Version 0.3.* is backward compatible to Version 0.2.0.

Version 0.2.0 is **NOT** backward compatible to Version 0.1.*.

* Looking for RedminPortal for Laravel 5.0? Visit the [v0.2 Branch](https://github.com/redooor/redminportal/tree/v0.2).
* Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

**Upgrading from v0.1.*?**

We've included a few database migrations to upgrade the database to support v0.2/v0.3. However, use this at your own risk. The upgrade scripts were not thoroughly tested and it may not be complete. If you find something missing, please report to us using the issue ticket. We welcome any contribution too.

Refer to [UPGRADE.md](UPGRADE.md) for the upgrading instructions.

# Models and Features

## User Management
* User
* Group
* Mailinglist

## Content Management
* Announcement
* Post
* Portfolio
* Promotion

## Online Store (Physical Products)
* Category
* Coupon
* Product
* Order
* Bundle

## Membership Subscription (Digital Products)
* Category
* Coupon
* Media
* Membership
* Module
* ModuleMediaMembership
* Order
* Purchase **_(deprecated, replaced by Order)_**
* Bundle

## Morphs
* Image
* Tag

## Downloadable Reports
1. Downloadable CSV reports for Purchases and Mailinglist.

## Translation options

There is an translation option in Category, Module, Media, Product, Promotion and Portfolio.

You can add more languages in the translation config file at path

    vendor\redooor\redminportal\src\config\translation.php

or if you have published it to your root

    root\config\packages\redooor\redminportal\translation.php
    
To use it, get the model's translations and use json_decode to convert content into an object, like this:

    foreach ($product->translations as $translation) {
        $lang = $translation->lang;
        $translated = json_decode($translation->content);
        var_dump($translated->name, $translated->short_description, $translated->long_description);
    }


# Installation guide for Users

You can install Laravel version 5.1 using the command:

    composer create-project laravel/laravel myproject 5.1.*

1. Add Redminportal to composer.json of a new Laravel application, under "require". Like this:

        "require": {
            "laravel/framework": "5.1.*",
            "redooor/redminportal": "0.3.*"
        },

    Due to the use of getID3 package, we need to set the minimum-stability to "dev" but prefer-stable to "true". Like this:

        "minimum-stability": "dev",
        "prefer-stable": true

2. Then run `php composer update [--prefer-dist]` in a terminal.

    Use `--prefer-dist` to include only essential files (i.e. exclude tests).

3. Now, edit your [root]\config\app.php providers and alias array like this:

        'providers' => array(
            Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
            ... omitted ...
            
            // Add this line
            Redooor\Redminportal\RedminportalServiceProvider::class,
        ),

4. Then run `php composer dump-autoload` in a terminal.
5. Run the following commands in a terminal to perform database migration for Redminportal:

        ?> php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" --force
        ?> php artisan migrate --path=/database/migrations/vendor/redooor/redminportal

    **NOTE: using --force will overwrite existing files**
    
6. Run the following in a terminal to seed the database with initial admin username and password:

        ?> php artisan db:seed --class="RedminSeeder"
        
        Username/password: admin@admin.com/admin

7. Publish package assets by running this in a terminal:

        ?> php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force
        
    **NOTE: using --force will overwrite existing files**

8. Publish package config by running this in a terminal:

        ?> php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="config" --force
        
    **NOTE: using --force will overwrite existing files**
    

# Installation guide for Contributors

It is recommended that contributors use Laravel Homestead for development because it will provide the same development environment for all of us. Read more about Laravel Homestead [here](http://laravel.com/docs/master/homestead).

1. Install Laravel 5.1 using [this guide](http://laravel.com/docs/5.1/installation). We'll call this the [root].

You can install Laravel version 5.1 using the command:

    composer create-project laravel/laravel myproject 5.1.*

2. Create a folder named "packages" inside the [root] folder.
3. Clone the Redooor\Redminportal repository into [root]\packages\redooor\redminportal folder.
4. Open a terminal, cd to [root]\packages\redooor\redminportal folder then run:
    
    `composer update --prefer-dist -vvv --profile`
    
5. Then add Redooor\Redminportal source to [root]'s composer.json under "autoload" like this:

        "autoload": {
            "classmap": [
                "database"
            ],
            "psr-4": {
                "App\\": "app/",
                "Redooor\\Redminportal\\": "packages/redooor/redminportal/src"
            }
        },

6. Due to the use of getID3 package, we need to set the minimum-stability to "dev" but prefer-stable to "true". Like this:

        "minimum-stability": "dev",
        "prefer-stable": true

7. Then cd to [root]'s folder and run:

    `composer update --prefer-dist -vvv --profile --no-dev`

    **NOTE: the [root]'s phpunit dependency will clash with the package's phpunit. "`--no-dev`" ensures that it is not installed on [root]. You can also choose to remove phpunit from `require` inside the [root]'s composer.json.**
    
8. Now, edit your [root]\config\app.php providers and alias array like this:

        'providers' => array(
            Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
            ... omitted ...
            
            // Add this line
            Redooor\Redminportal\RedminportalServiceProvider::class,
        ),

9. Run the following commands in a terminal to perform database migration for Redminportal inside the [root] folder:

        ?> php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" --force
        ?> php artisan migrate --path=/database/migrations/vendor/redooor/redminportal
        
    **NOTE: using --force will overwrite existing files**

10. Run the following in a terminal to seed the database with initial admin username and password:

        ?> php artisan db:seed --class="RedminSeeder"
        
        Username/password: admin@admin.com/admin

11. Publish package assets by running this in a terminal:

        ?> php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force
        
    **NOTE: using --force will overwrite existing files**

12. Publish package config by running this in a terminal:

        ?> php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="config" --force
        
    **NOTE: using --force will overwrite existing files**

        
## Install Grunt and Bower dependencies

1. You need to have nodejs installed
3. cd to workbench/redooor/redminportal
2. Run _npm install_
3. Run _bower install_
4. To build all assets, run _grunt_
5. To compile just the less css, run _grunt less-compile_

# Testing

* In packages\redooor\redminportal folder, run 

        ?> composer update --prefer-dist -vvv --profile
        ?> vendor/bin/phpunit

    **NOTE: If you run out of memory while running the full tests, try running the tests by sub-folders.**
    
        ?> vendor/bin/phpunit tests/models/
        ?> vendor/bin/phpunit tests/controllers/
        ?> vendor/bin/phpunit tests/relationships/

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

# External Libraries Used

* [Bootstrap v3.3.5](http://getbootstrap.com)
* [Font Awesome 4.4.0 by @davegandy](http://fontawesome.io)
* jQuery v2.1.4
* jQuery UI v1.11.4
* [illuminate/html](https://github.com/illuminate/html)
* [maatwebsite/excel](https://github.com/Maatwebsite/Laravel-Excel)
* [Imagine](https://github.com/avalanche123/Imagine)
* [Jasny Bootstrap](http://jasny.github.io/bootstrap/)
* [Plupload](http://www.plupload.com/)
* [getID3](http://www.getid3.org/)

# Change log

Refer to [CHANGELOG.md](CHANGELOG.md)

# Upgrade Guide

Refer to [UPGRADE.md](UPGRADE.md)
