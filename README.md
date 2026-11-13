![alt text][logo]

[logo]: https://redooor.com/wp-content/uploads/2020/07/redminportal_redooor.jpg "RedminPortal"

# RedminPortal by Redooor

A Laravel package as a **backend** administrating tool for Content Management and Ecommerce sites. Gives you ability to add, edit and remove category, product, promotions and many more. Provides User Interface for administrating users and groups.

RedminPortal currently supports Laravel 13. See [Compatibility](#compatibility).

# Table of Content
1. [Compatibility](#compatibility)
2. [Breaking changes in v2](#breaking-changes-in-v2)
3. [Models and Features](#models-and-features)
4. [Installation guide for Users](#installation-guide-for-users)
5. [Showing pages on your own frontend](#showing-pages-on-your-own-frontend)
6. [Installation guide for Contributors](#installation-guide-for-contributors)
7. [Testing](#testing)
8. [Versioning](#versioning)
9. [Contributing](#contributing)
10. [Creator](#creator)
11. [License](#license)
12. [External Libraries Used](#external-libraries-used)
13. [Change log](#change-log)
14. [Upgrade Guide](#upgrade-guide)

# Compatibility

| Laravel | RedminPortal | Branch | Status |
|:--------|:-------------|:-------|:-------|
| 13.x    | 2.x          | [v2](https://github.com/redooor/redminportal/tree/v2) | Active |
| 5.8     | 0.58.x       | [v0.58](https://github.com/redooor/redminportal/tree/v0.58) | Inactive |
| 5.1     | 0.3.x        | [v0.3](https://github.com/redooor/redminportal/tree/v0.3) | Inactive |
| 5.0     | 0.2.x        | [v0.2](https://github.com/redooor/redminportal/tree/v0.2) | Inactive |
| 4.2     | 0.1.x        | [v0.1](https://github.com/redooor/redminportal/tree/v0.1) | Inactive |

The focus of the development will be on branch v2, which supports Laravel 13.

Development for branch v0.1, v0.2, v0.3, and v0.58 has stopped. Please upgrade to v2.

# Upgrading

Refer to [UPGRADE.md](UPGRADE.md) for upgrading instructions from any previous version.

# Breaking changes in v2

Besides the move to Laravel 13 and PHP 8.3, these changes can affect an existing installation. Each one has a migration step in [UPGRADE.md](UPGRADE.md).

| Area | What changed | What you need to do | Step |
|---|---|---|---|
| Admin actions | The admin routes verify the CSRF token. Delete, remove image, activate/deactivate user, change order status, media duration and log out are now `POST`, not `GET` links. | Custom views: add `@csrf` to forms, use `data-method="post"` or the confirm modal on links, send the token with Ajax calls. Old `GET` links return 404/405. | 12 |
| Uploads | Media uploads only accept the extensions in the new `media` config and are limited by `media.max_size`; file names are cleaned. Images are stored with an extension that matches their real type (jpg, png, gif). | Add any other media types you need to `media.allowed_extensions` (publish the config). | 13 |
| Permissions | Activate/deactivate, remove image, media upload and duration, product variant edit need **update** (variant create needs **create**, variant delete needs **delete**) instead of **view**. Adding or deleting a purchase also needs the matching orders permission. Image removal only works through the owning module. | Give groups and users the `update`, `create` or `delete` permissions they now need. Explicit entries such as `admin.users.deactivate.view` become `admin.users.deactivate.update`. | 14 |
| Users and groups | You can only grant permissions you hold and assign groups you fully cover, and you cannot edit, activate, deactivate or delete an account (or group) that has more access than you. | Use an administrator with full access for these tasks. | 15 |
| Default administrator | The seeder no longer creates `admin@admin.com` with the password `admin`. It creates the administrator from `REDMIN_ADMIN_EMAIL` and `REDMIN_ADMIN_PASSWORD`, or prints a generated password once. It also no longer deletes existing users and groups. | See [Seeding the first administrator](#seeding-the-first-administrator). Change the password of any account that still uses `admin`. | 15 |
| Sign-in and passwords | Sign-in is limited to 5 failed attempts per minute for each email and IP address. New passwords need at least 10 characters (`password.min_length`). Changing a password signs out that user's other sessions; changing your own password in My Account no longer signs you out. | Update scripts or tests that use short passwords. | 15 |

# Models and Features

## User Management
* User
* Group
* Mailinglist

## Content Management
* Announcement
* Page
* Portfolio
* Post
* Promotion

## Online Store (Physical Products)
* Bundle
* Category
* Coupon
* Order
* Product (now supports variations)

## Membership Subscription (Digital Products)
* Bundle
* Category
* Coupon
* Media
* Membership
* Module
* ModuleMediaMembership
* Order
* ~~Purchase~~ **_(deprecated, replaced by Order)_**
* Pricelist

## Morphs
* Image
* Revision (new! Now changes to Orders are tracked)
* Tag
* Translation

## Traits
* Permissible
* Revisionable

## Classes
* File
* Volume
* Weight
* Imagine
* Redminportal (use as alias in blade template)

## Helpers
* RHelper
* ~~RImage~~ **_(replaced by Classes/Imagine, retained for backward compatibility)_**

## Facades
* Redminportal

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

You can create a new Laravel 13 application using the command:

    composer create-project laravel/laravel myproject

1. Add Redminportal to composer.json of a new Laravel application, under "require". Like this:

        "require": {
            "laravel/framework": "^13.0",
            "redooor/redminportal": "^2.0"
        },

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
5. **Important: remove Laravel's default `users` migration first.**

    Redminportal creates its own `users` table (with extra columns such as `first_name`, `last_name`, `permissions` and `activated`). Its migration is skipped when a `users` table already exists, so if Laravel's default one has been migrated, the later seeding step fails with `table users has no column named first_name`.

    In a new Laravel application, delete `database/migrations/0001_01_01_000000_create_users_table.php` **before** running any migration. If you have already migrated, delete the file and run `php artisan migrate:fresh` (this drops all tables).

    That default migration also creates the `sessions` table. If you keep `SESSION_DRIVER=database`, add the `sessions` table back yourself, or set `SESSION_DRIVER=file` in your `.env`.

    If your application already has its own `users` table, Redminportal will not alter it. You must add the missing columns yourself, or use a separate database for Redminportal.

    Then run the following commands in a terminal to perform database migration for Redminportal:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" [--force]

        php artisan migrate --path=/database/migrations/vendor/redooor/redminportal

    **CAUTION: using --force will overwrite existing files**
    
6. Run the following in a terminal to seed the database with the first administrator (see [Seeding the first administrator](#seeding-the-first-administrator)):

        REDMIN_ADMIN_EMAIL=you@example.com REDMIN_ADMIN_PASSWORD='choose-a-long-password' php artisan db:seed --class="Database\Seeders\RedminSeeder"

    If you leave out `REDMIN_ADMIN_PASSWORD`, a random password is generated and printed once. Copy it from the terminal output.

7. Publish package assets by running this in a terminal:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" [--force]
        
    **CAUTION: using --force will overwrite existing files**

8. Publish package config by running this in a terminal:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="config" [--force]
        
    **CAUTION: using --force will overwrite existing files**

    IMPORTANT:

    The package will append neccesary guards and providers to your `auth` config. If you've added a guard with the same name as `redminguard` and provider as `redminprovider`, they'll be replaced by the package's setting.

    If you're going to use your own `auth` config but would like to use Redminportal for authentication, you need to add the a guard in your `auth` config as below:

    ```
    'defaults' => [
        'guard' => 'web', // or it could be 'redminguard'
        'passwords' => 'redminpasswords',
    ],
    ```

    ```
    'guards' => [
        'redminguard' => [
            'driver' => 'session',
            'provider' => 'redminprovider',
        ],
        'web' => [
            'driver' => 'session',
            'provider' => 'redminprovider',
        ],
    ],
    ```

    ```
    'providers' => [
        'redminprovider' => [
            'driver' => 'eloquent',
            'model' => Redooor\Redminportal\App\Models\User::class,
        ],
    ],
    ```

    ```
    'passwords' => [
        'redminpasswords' => [
            'provider' => 'redminprovider',
            'table' => 'password_reset_tokens',
            'expire' => 60,
        ],
    ],
    ```
    
9. _**Optional:**_ Publish package views by running this in a terminal:

    Only do this if you want to modify Redminportal views without editing the source code.

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="views" [--force]
        
    **CAUTION: using --force will overwrite existing files**

## Seeding the first administrator

`Database\Seeders\RedminSeeder` creates the `Admin` and `User` groups and one active administrator. It does nothing when the `users` table already has records, and it never deletes users or groups.

| Variable | Meaning |
|---|---|
| `REDMIN_ADMIN_EMAIL` | Email of the administrator. Defaults to `admin@example.com`. |
| `REDMIN_ADMIN_PASSWORD` | Password of the administrator (at least 10 characters, otherwise the seeder stops without creating anything). When it is not set, a random 20-character password is generated and printed once in the terminal. It is not stored or logged anywhere else. |

For local development and demos only, `Database\Seeders\RedminDemoSeeder` creates `admin@admin.com` with the password `admin`. It only runs when `APP_ENV` is `local`, `development` or `testing` (a missing `APP_ENV` counts as `production`). If a site was seeded with the old seeder, change the password of `admin@admin.com` (or deactivate that account) after signing in.

# Showing pages on your own frontend

RedminPortal is an admin dashboard only. It stores your content, and your Laravel application decides how to show it. This example shows pages created in **Admin > Pages** at the site root, so a page with slug `about` appears at `/about` (not `/page/about`).

If you would rather not build this yourself, the optional [RedminStore](https://github.com/redooor/redminstore) package does exactly this out of the box.

1. In the admin, create a page: set a **Title**, **Content** and **Slug** (for example `about`), and leave **Private** unchecked. Private pages should never be shown publicly.

2. Create a controller, `app/Http/Controllers/PageController.php`:

        <?php

        namespace App\Http\Controllers;

        use Illuminate\Http\Request;
        use Redooor\Redminportal\App\Models\Page;

        class PageController extends Controller
        {
            public function show(Request $request)
            {
                $slug = trim($request->path(), '/');

                // Only single-segment URLs such as /about; anything else is a normal 404.
                abort_if(str_contains($slug, '/'), 404);

                $page = Page::where('slug', $slug)
                    ->where('private', false)   // never show pages marked Private in the admin
                    ->firstOrFail();

                return view('page', ['page' => $page]);
            }
        }

3. Create the view, `resources/views/page.blade.php`. Use your own layout here:

        <h1>{{ $page->title }}</h1>

        {{-- Page content is HTML written by trusted admins in RedminPortal's editor. --}}
        {!! $page->content !!}

4. Register a fallback route at the **end** of `routes/web.php`:

        use App\Http\Controllers\PageController;

        Route::fallback([PageController::class, 'show']);

A fallback route runs only when no other route matches, so it never takes over your own routes or RedminPortal's `/admin` and `/login`, whatever order they are registered in. A page with the same slug as a real route (for example `login`) is therefore never shown. Unknown URLs return your normal 404 page. Use a controller rather than a closure so that `php artisan route:cache` keeps working.

Notes:

- Slugs are stored with spaces replaced by underscores. Use letters, numbers, `-` and `_` in slugs.
- `$page->content` is raw HTML, so only print it unescaped (`{!! !!}`) if only trusted admins can edit pages.
- A page also has `category`, `tags`, `images` and `translations` relationships, for example `$page->images` and `$page->tags`.
- Posts work the same way: use the `Post` model with `where('private', false)`, and give them a prefix such as `/blog/{slug}` to avoid clashing with page slugs.

# Installation guide for Contributors

It is recommended that contributors use Laravel Homestead for development because it will provide the same development environment for all of us. Read more about Laravel Homestead [here](http://laravel.com/docs/master/homestead).

For Mac users, you may want to try out [Laravel Herd](https://herd.laravel.com/).

1. Install Laravel 13 using [this guide](https://laravel.com/docs/13.x/installation). We'll call this the [root].

You can create a new Laravel 13 application using the command:

    composer create-project laravel/laravel myproject

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

6. Then cd to [root]'s folder and run:

    `composer update --prefer-dist -vvv --profile --no-dev`

    **NOTE: the [root]'s phpunit dependency will clash with the package's phpunit. "`--no-dev`" ensures that it is not installed on [root]. You can also choose to remove phpunit from `require` inside the [root]'s composer.json.**
    
7. Now, edit your [root]\config\app.php providers and alias array like this:

        'providers' => array(
            Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
            ... omitted ...
            
            // Add this line
            Redooor\Redminportal\RedminportalServiceProvider::class,
        ),

8. Run the following commands in a terminal to perform database migration for Redminportal inside the [root] folder:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" [--force]

        php artisan migrate --path=/database/migrations/vendor/redooor/redminportal
        
    **CAUTION: using --force will overwrite existing files**

9. Run the following in a terminal to seed the database with a demo administrator for local development (never use this on a shared or production site; it only runs when `APP_ENV` is `local`, `development` or `testing`):

        php artisan db:seed --class="Database\Seeders\RedminDemoSeeder"

        Username/password: admin@admin.com/admin

10. Publish package assets by running this in a terminal:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" [--force]
        
    **CAUTION: using --force will overwrite existing files**

11. Publish package config by running this in a terminal:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="config" [--force]
        
    **CAUTION: using --force will overwrite existing files**

12. _**Optional:**_ Publish package views by running this in a terminal:

    Only do this if you want to modify Redminportal views without editing the source code.

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="views" [--force]
        
    **CAUTION: using --force will overwrite existing files**
        
## Build assets

1. Install [Node.js](https://nodejs.org/) 22.12 or later.
2. cd to packages/redooor/redminportal
3. Run `npm ci` to install the build dependencies.
4. Run `npm run build` to compile the Sass in `src/resources/assets/scss` and copy the vendored assets into `src/public`.
5. From your Laravel application's root folder (where `artisan` lives), publish the assets with:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force

# Testing

* In packages\redooor\redminportal folder, run 

        composer update --prefer-dist -vvv --profile
        
        vendor/bin/phpunit

    **NOTE: If you run out of memory while running the full tests, try running the tests by sub-folders.**
    
        vendor/bin/phpunit tests/models/
        
        vendor/bin/phpunit tests/controllers/
        
        vendor/bin/phpunit tests/relationships/

        ./vendor/bin/phpunit --testsuite "suite1","suite2"
        
        ./vendor/bin/phpunit --testsuite "suite3","suite4"

# Versioning

For transparency into our release cycle and in striving to maintain backward compatibility, Redooor RedminPortal will adhere to the [Semantic Versioning guidelines](http://semver.org/) whenever possible.

# Contributing

Thank you for considering contributing to RedminPortal.
Before any submission, please spend some time reading through the [CONTRIBUTING.md](CONTRIBUTING.md) document.

# Creator

Andrews Ang

* [http://github.com/kongnir](http://github.com/kongnir)

# License

RedminPortal is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

# External Libraries Used

* [Bootstrap 5.3](https://getbootstrap.com) with the [Redmaterials](https://github.com/redooor/redmaterials) theme
* [Font Awesome Free 7](https://fontawesome.com)
* jQuery 3.7 and jQuery UI 1.14
* [Jodit 4](https://xdsoft.net/jodit/) (rich text editor)
* [Tempus Dominus 6](https://getdatepicker.com/) and Popper (coupon date picker)
* typeahead.js and bootstrap-tagsinput (tags)
* [illuminate/html](https://github.com/illuminate/html)
* [maatwebsite/excel](https://github.com/Maatwebsite/Laravel-Excel)
* [Imagine](https://github.com/avalanche123/Imagine)
* [Plupload](http://www.plupload.com/)
* [getID3](http://www.getid3.org/)

# Change log

Refer to [CHANGELOG.md](CHANGELOG.md)

# Upgrade Guide

Refer to [UPGRADE.md](UPGRADE.md)
