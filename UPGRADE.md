# Upgrade Guide

## Upgrading to v0.3.0 from v0.2.*

Supports Laravel 5.1.
Version 0.3.0 should be backward compatible to Version 0.2.0 but not Version 0.1.*.

Looking for RedminPortal for Laravel 5.0? Visit the [v0.2 Branch](https://github.com/redooor/redminportal/tree/v0.2).
Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

Edit your [root]\config\app.php providers and alias array like this:

        'providers' => array(
            Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
            ... omitted ...
            
            // Edit this line
            Redooor\Redminportal\RedminportalServiceProvider::class,
        ),

## Upgrading to v0.2.0 from <= v0.1.*

Supports Laravel 5.0.
Version 0.2.0 is **NOT** backward compatible.

Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

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
