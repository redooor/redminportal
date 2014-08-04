# Redooor RedminPortal

A Laravel 4 package as a backend administrating tool for Content Management and Ecommerce sites.
Gives you ability to add, edit and remove category, product, promotions and many more.
Provides User Interface for administrating users and groups (via Cartalyst Sentry)

# Installation guide

1. This Package is dependant on Cartalyst Sentry. Install Sentry in your app by following the instruction [here](http://docs.cartalyst.com/sentry-2/installation/laravel-4).
2. This Package is dependant on [Maatwebsite/Laravel-Excel](http://www.maatwebsite.nl/laravel-excel/docs). This package is already included, so no action is required from you.
3. Then add Redooor\Redminportal to your app\config\app.php providers array like this:

        'providers' => array(
            'Illuminate\Foundation\Providers\ArtisanServiceProvider',
            ... omitted ...
            'Illuminate\Workbench\WorkbenchServiceProvider',
            'Cartalyst\Sentry\SentryServiceProvider',
            'Redooor\Redminportal\RedminportalServiceProvider',
        ),

4. Then run `php composer update` in workspace\redoooor\redminportal folder.

# Versioning

For transparency into our release cycle and in striving to maintain backward compatibility, Redooor RedminPortal will adhere to the [Semantic Versioning guidelines](http://semver.org/) whenever possible.

# Contributing

Thank you for considering contributing to RedminPortal.
Before any submission, please spend some time reading through the [CONTRIBUTING.md](https://github.com/redooor/redminportal/blob/master/CONTRIBUTING.md) document.

# License

RedminPortal is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

# Change log

## Version 0.1.0
1. Supports Laravel 4.1
2. Added PHPUnit testing and fixed Model test errors.
3. Menu view can be controlled via config/menu.php file.
4. UI improvement.
5. Added Purchase history page.
6. Media supports huge file upload (via a plugin).
7. Able to activate user from admin portal.
8. Added ability to insert picture in Announcement page.
9. Added downloadable CSV reports for Purchases and Mailinglist.
10. Many bug fixes.

### Added the following new Models
1. Price list for Media and Membership
2. Discount table
3. Media
4. Membership
5. Module
6. Mailinglist

## Version 0.0.3
1. Bump up version to 1
2. Upgraded Bootstrap to 3.1.1
3. Upgraded jQuery to 1.11.1
4. Changed Composer to get stable versions
5. Branch Version 2 from here

## Version 0.0.2
1. Added HTML WYSIWYG editor for Portfolio Create page Description field
2. Added HTML WYSIWYG editor for Product Create and Edit page Description field
3. Fixed image issue for Announcements/News page
4. Fixed private Announcements/News still showing issue
5. Refractoring: clean up routes, move login logic out of User Controller into Login Controller
6. Upgraded Bootstrap to 3.0.3

## Version 0.0.1
1. Fixed image issue for Announcements/News page
2. Fixed private Announcements/News still showing issue
3. Added HTML WYSIWYG editor for Portfolio Description field
4. Added function to delete image from Annoncements/News

## Version 0.0.0
1. Separated RedminPortal code to its own package
2. Coined the term "RedminPortal"
3. Upgraded to Bootstrap v3
4. Better user interface such as using tabs to show different languages
5. Added pagnation for products, categories and promotions pages
6. Added date picker and file uploader javascripts
7. Added WYSIWYG editor for promotion description
8. Verify password for user creation
9. Able to add and edit users, groups, categories, products and promotions
10. Translation capability for Simplified Chinese; user can enter Chinese translation to the categories, products and promotions.
