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
