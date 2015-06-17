# Change log

1. [Version 0.3.0](#version-030)
2. [Version 0.2.0](#version-020)
3. [Version 0.1.5](#version-015)
4. [Version 0.1.4](#version-014)
5. [Version 0.1.3](#version-013)
6. [Version 0.1.2](#version-012)
7. [Version 0.1.1](#version-011)
8. [Version 0.1.0](#version-010)

## Version 0.3.0
Focus on supporting Laravel 5.1.

* Looking for RedminPortal for Laravel 5.0? Visit the [v0.2 Branch](https://github.com/redooor/redminportal/tree/v0.2).
* Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

### Important:
Version 0.3.0 should be backward compatible to Version 0.2.0.

If you're upgrading from v0.2.*, please refer to the [Upgrade Guide](#upgrade-guide).

### New feature:
Support Laravel 5.1 (issue #87).

## Version 0.2.0
Focus on supporting Laravel 5.0 and making sure all models and controllers work.

Version 0.2.0 is **NOT** backward compatible.

Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

### New feature:
1. Support Laravel 5.0.
2. Removed dependancy of Cartalyst/Sentry.
3. Use Laravel's Authentication instead of Cartalyst/Sentry.
4. Users and Groups tables should work with previous schema. (But need to add column remember_token to users table)
5. Drop Discount module support from v0.2.0 onwards (issue #63).
6. Changed UI to use [Redmaterials](http://redmaterials.redooor.com) theme.
7. Removed Bootstrap from redminportal.css. It should be linked separately.
8. Consistent UI across all views (issue #76).
9. Add active column to Pricelists table (issue #78).
10. Add UI to views for additional active column in Pricelists table (issue #78).
11. Rearrange menu to split Physical product store and Digital product store (issue #52).
12. Promotions, Products and Portfolios now allow translation (issue #9).
13. Use Laravel authentication, removed Sentry dependency (issue #81).
14. Allow multiple Category images (issue #79).
15. Add Post Model and Controller (issue #73).
16. Revamp Translation implementation for Category, Media, Module, Portfolio, Product and Promotion (issue #85).
17. Add Page model and Controller (issue #74).
18. Save orders for products (issue #51).
19. Download orders in CSV format.

### Bug fixes:
1. Translation to support any language (issue #83).

## Version 0.1.5
The focus of this update was on cleaning up the code and making sure all tests pass. Improve assets management via Grunt and Bower. Add Coupon module.

### Important:
If you're upgrading from <= v0.1.4, please refer to the [Upgrade Guide](#upgrade-guide).

### Bug fixes:
1. PurchaseControllerTest is producing an error (issue #48).
2. PHPUnit test run out of memory issue, increase from 256MB to 400MB (issue #49).
3. orchestra/testbench is using developer branch (issue #50).
4. PricelistController has been changed to DiscountController (issue #55).
5. admin/discounts does not show correct display count (issue #33).
6. Unable to delete discount from UI due to fix for issue #55 (issue #57).
7. Sorting capability for Users page (issue #70).
8. Sorting capability for Group page (issue #71).
9. Add relationship in classes User and Group (issue #72).
10. Removed redundant model Purchase and database table purchases (issue #60).

### New feature:
Discount module is restricted to membership/module (pricelist) and does not allow usage limit.
Coupon module was created to replace Discount module and to provide greater flexibility.

1. New Coupon module will replace Discount module (issue #62).
2. Ability to add, edit and delete Coupon module (issue #62).
3. Coupon module supports adding coupon for category, product and membership/module (pricelist).
4. Coupon module to allow per coupon limit (issue #18).
5. Coupon module to allow per user limit (issue #19).
6. Coupon module to include a start date (issue #56).
7. Coupon end date should allow specifying time (issue #58).

### Note for Contributors
All assets are now managed via Grunt and Bower. Please refer to [Install Grunt and Bower dependencies](#install-grunt-and-bower-dependencies).

## Version 0.1.4
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

### Configuration
1. Menu view can be controlled via config/menu.php file.
2. Uploaded image size can be controlled via config/image.php file.
3. Translation capability can be turned on via config/translation.php file.
