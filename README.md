# Redooor RedminPortal

A Laravel 4 package as a backend administrating tool for Content Management and Ecommerce sites.
Gives you ability to add, edit and remove category, product, promotions and many more.
Provides User Interface for administrating users and groups (via Cartalyst Sentry).

# Installation guide for Users

1. Add Redminportal to composer.json of a new Laravel application, under "require". Like this:

        "require": {
            "laravel/framework": "4.2.*",
            "redooor/redminportal": "0.1.*"
        },

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

### Configuration
1. Menu view can be controlled via config/menu.php file.
2. Uploaded image size can be controlled via config/image.php file.
3. Translation capability can be turned on via config/translation.php file.

### Models
1. Price list for Media and Membership
2. Discount table
3. Media
4. Membership
5. Module
6. Mailinglist
7. Announcement
8. Product
9. Category
10. Promotion
11. Purchases (user_pricelists)
12. Portfolio

### Downloadable Reports
1. Downloadable CSV reports for Purchases and Mailinglist.
