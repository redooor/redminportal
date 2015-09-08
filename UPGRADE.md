# Upgrade Guide

## Upgrading to v0.3.2 from v0.3.1

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

Version 0.3.2 introduced some new database tables and removed some.

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
    
## Upgrading to v0.3.1 from v0.3.0

New features and UI improvements.

### Publish assets

Version 0.3.1 introduced some UI improvements. You need to run the following command to re-publish the assets.

**Caution**: This action will overwrite any changes made to the `public/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `public/vendor/redooor/redminportal` folder.

    php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="public" --force

### Migrations

Version 0.3.1 introduced some new database tables. You need to run the following command to re-publish the migrations.

**Caution**: This action will overwrite any changes made to the `database/migrations/vendor/redooor/redminportal` folder.

As a general rule, do not save any customed files inside `database/migrations/vendor/redooor/redminportal` folder.

**Before you begin, _ALWAYS BACKUP_ your database.**

1. You can publish the migrations using:

        php artisan vendor:publish --provider="Redooor\Redminportal\RedminportalServiceProvider" --tag="migrations" --force

2. Then run the following in the root folder:

        php artisan migrate --path=/database/migrations/vendor/redooor/redminportal
        
## Upgrading to v0.3.1 from <= v0.1.*

Supports Laravel 5.1.

### Migrations

In Version 0.3.1, the migrations are generally designed to be forgiving. It will check for existance before creating the tables.

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

* Looking for RedminPortal for Laravel 5.0? Visit the [v0.2 Branch](https://github.com/redooor/redminportal/tree/v0.2).
* Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

Edit your [root]\config\app.php providers and alias array like this:

        'providers' => array(
            Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
            ... omitted ...
            
            // Edit this line
            Redooor\Redminportal\RedminportalServiceProvider::class,
        ),

## Upgrading to v0.2.0 from <= v0.1.*

Refer to [branch v0.2 UPGRADE.md](https://github.com/redooor/redminportal/blob/v0.2/UPGRADE.md)
