# Upgrade Guide

## Upgrading to v0.58

This version is focused on upgrading the code to support Laravel 5.8. Most of the models should remain unchanged.

### Guard 'redminguard'

There's an addition of a new guard 'redminguard' to all authentication.

Change any Auth calls to include the guard `redminguard`.

E.g.

    Auth::check();

Should be changed to:

    Auth::guard('redminguard')->check();

### New auth config

There's a new auth config file. If you've published the config files, remember to publish again to get the latest changes. If you're not modifying the config files, you can remove them from your source. The package will use its own config files if it cannot find published configs.

IMPORTANT:

The package will append neccesary guards and providers to your `auth` config. If you've added a guard with the same name as `redminguard` and provider as `redminprovider`, they'll be replaced by the package's setting.

### Views update

There have been some changes to how we check for errors, hence some views were modified. Please publish the views again to get the latest changes.

## Upgrading to v0.3.3.1/v0.2.3.1 from v0.3.3/v0.2.3

Version 0.2 and 0.3 are developed in parallel. The only difference between them is the Laravel version they support. However, this may change in future.

### Public assets

You need to run the following command to re-publish the assets.

**Caution**: This action will overwrite any changes made to the `public/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `public/vendor/redooor/redminportal` folder.

    php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force
    
### Views (optional)

**This is optional.** Skip this step if you haven't publish the views before.

You need to run the following command to re-publish the views.

**Caution**: This action will overwrite any changes made to the `resources/views/vendor/redooor/redminportal` folder.

As a general rule, submit your existing files to a version control system and then compare the difference.

    php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="views" --force
    

## Upgrading to v0.3.3/v0.2.3 from v0.3.2/v0.2.2

### Access Permission

In the past any user who is within the group "Admin" are allowed to access the admin dashboard and all actions such as creation, edit and deletion.

With the recent change, we will now be checking the permission level of user and their groups.

This permission can be set on user level as will as group level.

Permission hierarchy as follow:
- User level permission supersedes Group level permission.
- ANY group deny will result in forceful deny.
- At least 1 group allow (if no deny) will result in allow.
- Sub route permission supersedes Main route permission.

If you have been using the group creation UI since version 0.2 then you should be fine.

The default seed for Admin and User group are as such:

Admin
```
{
  "admin.view":true,
  "admin.create":true,
  "admin.delete":true,
  "admin.update":true
}
```

User
```
{
  "admin.view":false,
  "admin.create":false,
  "admin.delete":false,
  "admin.update":false
}
```

From now on, Permission is tied to route, using 3 integers to indicate rights:

- 1: Allow
- -1: Deny (supersedes any allow from other groups)
- 0: Inherit (or not allowed if none is found)

So this:

`{"admin.view": 1}`

means that the user/group has access to route 'admin' and all sub-route.

And this:

`{"admin.users.view": -1}`

means that the user/group is denied from route 'admin/users'.

This:
```
{
  "admin.users.view":1,
  "admin.users.delete":-1,
  "admin.users.update":-1
}
```
means that the user/group can view but not delete or update record.

#### Permission Usage

To check if the user is allowed to view the page, use the hasAccess method in User model.

NOTE: You need to specify the guard 'redminguard'.

Example:
```
    public function getIndex(Request $request)
    {
        $user = \Auth::guard('redminguard')->user();
        if ($user->hasAccess($request)) {
            // Do something
            return view('has_access');
        }
        
        return view('access_denied');
    }
```

#### Permission Config file

You can edit the list of routes for permission management via the config file `src/config/permissions.php`.

Copy the file `src/config/permissions.php` to your root folder's `config/vendor/redooor/redminportal/permissions.php`.

### Run Dump-Autoload

Due to the additions of HTML and Form helpers, you need to run the following command:

For Users
```shell
    cd <your_app_root>
    composer dump-autoload
```

For Contributors
```shell
    cd <your_app_root>/packages/redooor/redminportal
    composer dump-autoload
```

### Migrations

Version 0.3.3 and v0.2.3 introduced some new database tables.

You need to run the following command to re-publish the migrations.

**Caution**: This action will overwrite any changes made to the `database/migrations/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `database/migrations/vendor/redooor/redminportal` folder.

**Before you begin, _ALWAYS BACKUP_ your database.**

1. You can publish the migrations using:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" --force

2. Then run the following in the root folder:

        php artisan migrate --path=/database/migrations/vendor/redooor/redminportal

### Public assets

You need to run the following command to re-publish the assets.

**Caution**: This action will overwrite any changes made to the `public/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `public/vendor/redooor/redminportal` folder.

    php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force
    
### Pagination Config file

We've moved the default pagination size for all pages to a config file `src/config/pagination.php`.

Copy the file `src/config/pagination.php` to your root folder's `config/vendor/redooor/redminportal/pagination.php`.

You can change the value to any desired number to be the pagination size. The default is 50.

### Payment Statues Config file

You can now change the payment statuses in a config file `src/config/payment_statuses.php` for your project.

Copy the file `src/config/pagination.php` to your root folder's `config/vendor/redooor/redminportal/payment_statuses.php`.

### Relocation of Redminportal Facade

This shouldn't really affect your existing installation because previously it was not working.

The facade file has been moved from

`src/facades/Redminportal.php`

to

`src/App/Facades/Redminportal.php`

It is important to note the uppercase in Facades because without it autoloading will fail in most Linux and Mac OS environment.

### Minimum-stability 'dev' not required

After changing to JamesHeinrich/getID3, it is no longer a requirement to change the minimum-stability to 'dev'.
You can choose to set it back to 'stable' like this:

    "minimum-stability": "stable",
    "prefer-stable": true

### Get user emails in JSON format

The following 2 links have been removed.

`url('admin/orders/emails');`
`url('admin/purchases/emails');`

Use this instead:

`url('admin/api/email/all)`

### RHelper::printMenu is moved

For compatibility, RHelper::printMenu() is still available.

However, please change it to Redooor\Redminportal\App\UI\Html->printMenu() instead.

On the blade template, for example,

instead of

```php
{{ \Redooor\Redminportal\App\Helpers\RHelper::printMenu(config('redminportal::menu'), 'nav nav-sidebar') }}
```

use this

```php
{!! Redminportal::html()->printMenu(config('redminportal::menu'), 'nav nav-sidebar') !!}
```

**Take note of the change in curly brackets {{ }} to {!! !!}.**

## Upgrading to v0.3.2/v0.2.2 from v0.3.1/v0.2.1

New features and UI improvements.

### UserPricelist _(deprecated)_

Table **user_pricelists** has been moved to **orders** and **order_pricelist** so that it's consistent with products and bundles. Pricelist orders should be managed via admin/orders from now onwards.

#### Query change required

UserPricelist model has been kept for backward compatibility.
However, the column 'pricelist_id' no longer exists. So in your queries, if you have conditions checking for 'pricelist_id', you need to change them. For example:

Existing query:

```php
    UserPricelist::where('user_id', $user->id)
        ->where('pricelist_id', $pricelist->id)
        ->get();
```

New query:

```php
    UserPricelist::join('order_pricelist', 'orders.id', '=', 'order_pricelist.id')
        ->where('orders.user_id', $user->id)
        ->where('order_pricelist.pricelist_id', $pricelist->id)
        ->get();
```

**NOTE:** UserPricelist now refers to database table **orders**.

#### user_pricelists migration
    
The migration script will automatically transfer existing data from user_pricelists to orders and order_pricelist. No additional work is required from you (other than the front-end query code change mentioned above).

That being said, always **BACKUP** your database before executing any migration.

#### Replace all UserPricelist model with Order

The best way to get purchased pricelists are done through Order now. If you can, change all UserPricelist to Order.

To get the purchased pricelist in Order, you can do the same like this:

```php
    Order::join('order_pricelist', 'orders.id', '=', 'order_pricelist.id')
        ->where('orders.user_id', $user->id)
        ->where('order_pricelist.pricelist_id', $pricelist->id)
        ->get();
```

### Migrations

Version 0.3.2/0.2.2 introduced some new database tables and removed some.

You need to run the following command to re-publish the migrations.

**Caution**: This action will overwrite any changes made to the `database/migrations/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `database/migrations/vendor/redooor/redminportal` folder.

**Before you begin, _ALWAYS BACKUP_ your database.**

1. You can publish the migrations using:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" --force

2. Then run the following in the root folder:

        php artisan migrate --path=/database/migrations/vendor/redooor/redminportal

### Public assets

You need to run the following command to re-publish the assets.

**Caution**: This action will overwrite any changes made to the `public/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `public/vendor/redooor/redminportal` folder.

    php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force
    
## Upgrading to v0.3.1/v0.2.1 from v0.3.0/0.2.0

New features and UI improvements.

### Publish assets

Version 0.3.1 introduced some UI improvements. You need to run the following command to re-publish the assets.

**Caution**: This action will overwrite any changes made to the `public/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `public/vendor/redooor/redminportal` folder.

    php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force

### Migrations

Version 0.3.1/0.2.1 introduced some new database tables. You need to run the following command to re-publish the migrations.

**Caution**: This action will overwrite any changes made to the `database/migrations/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `database/migrations/vendor/redooor/redminportal` folder.

**Before you begin, _ALWAYS BACKUP_ your database.**

1. You can publish the migrations using:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" --force

2. Then run the following in the root folder:

        php artisan migrate --path=/database/migrations/vendor/redooor/redminportal
        
## Upgrading to v0.3.1/0.2.1 from <= v0.1.*

Version 0.3.1 supports Laravel 5.1. Version 0.2.1 supports Laravel 5.0.

### Migrations

In Version 0.3.1/0.2.1, the migrations are generally designed to be forgiving. It will check for existance before creating the tables.

**Before you begin, _ALWAYS BACKUP_ your database and `public\assets\img folder`.**

To support foreign keys, we need to convert these tables to **InnoDB** type.

* products
* categories
* pricelists
* medias
* modules
* memberships
* coupons

You can include an upgrade migration to convert these tables automatically for you. _(ONLY works with MySQL database)_

1. Run this command to copy all migrations from Redminportal to your app.

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" --force
    
2. Copy this file

        [root]\vendor\redooor\redminportal\src\database\upgrades\2015_05_21_000000_upgrade_innodb_table.php
        
3. Paste it into

        [root]\database\migrations\vendor\redooor\redminportal\
        
4. Then use the following command to run the full migrtions

        php artisan migrate --path=/database/migrations/vendor/redooor/redminportal


Additionally, you may run these migrations to upgrade the tags and images table in Version 0.1.* to the new one.

**WARNING: the images migration involves moving of files to the new folder structure. Do this when your site is least busy.**

1. Copy the following files in `vendor\redooor\redminportal\database\upgrades`

        [root]\vendor\redooor\redminportal\database\upgrades\2015_08_03_000000_upgrade_tags_table.php
        [root]\vendor\redooor\redminportal\database\upgrades\2015_08_04_000000_upgrade_images_table.php
        [root]\vendor\redooor\redminportal\database\upgrades\2015_08_31_000000_upgrade_translations_table.php

2. Paste them in [root]\database\migrations.
3. Run `php artisan migrate` at the [root] directory.

## Upgrading to v0.3.0 from v0.2.*

Supports Laravel 5.1.

Version 0.3.0 should be backward compatible to Version 0.2.0 but not Version 0.1.*.

Edit your [root]\config\app.php providers and alias array like this:

        'providers' => array(
            Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
            ... omitted ...
            
            // Edit this line
            Redooor\Redminportal\RedminportalServiceProvider::class,
        ),

To upgrade from version v0.1.*, you need to use v0.3.1/0.2.1 onwards.

## Version 0.1
Supports Laravel 4.2.

Refer to [branch v0.1 README.md Upgrade Guide](https://github.com/redooor/redminportal/blob/v0.1/README.md#upgrade-guide)
