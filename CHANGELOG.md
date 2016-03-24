# Change log

[![Build Status](https://travis-ci.org/redooor/redminportal.svg?branch=develop-v0.2)](https://travis-ci.org/redooor/redminportal)

* [Version 0.3.3 and 0.2.3](#version-033-and-023)
* [Version 0.3.2 and 0.2.2](#version-032-and-022)
* [Version 0.3.1 and 0.2.1](#version-031-and-021)
* [Version 0.3.0](#version-030)
* [Version 0.2.0](#version-020)
* [Version 0.1](#version-01)

#Compatibility

| Laravel | RedminPortal | Branch |
|:-------:|:------------:|:------:|
| 4.2     | 0.1.x        | [v0.1](https://github.com/redooor/redminportal/tree/v0.1) |
| 5.0     | 0.2.x        | [v0.2](https://github.com/redooor/redminportal/tree/v0.2) |
| 5.1     | 0.3.x        | [v0.3](https://github.com/redooor/redminportal/tree/v0.3) |

Version 0.2 and 0.3 are developed in parallel. The only difference between them is the Laravel version they support. However, this may change in future.

Development for branch v0.1 has stopped. Please upgrade to v0.2 or v0.3 instead.

## Version 0.3.3 and 0.2.3
This update introduces some new tables and lots of changes.

**IMPORTANT:** Refer to [UPGRADE.md](UPGRADE.md) for the upgrading instructions.

### New features:
1. Added product variations. You can now add variations to each product. (issue #125)
2. Added reusable partial templates for Modal Window and Language selector tab. (issue #130)
3. Added shipping properties to products: Weight, Length, Width, Height, units. (issue #126)
4. Allow Tinymce content to follow front end CSS. (issue #143)
5. Moved pagination to config file so developer can set the default pagination size. (issue #121)
6. Usable Redminportal Facade for HTML helpers.
7. Added Sorting capability for (issue #15):
    - Orders
    - Announcements
    - Pages
    - Posts
    - Portfolios
    - Promotions
    - Products
    - Memberships
    - Modules
    - Medias
8. Added capability to sort by Category for Bundle. (issue #15)
9. Improve Tagging (issue #147)
    - Added Typeahead for tag suggestion
    - Use label visual for tags
10. Added API for retrieving tag names
    - /api/tag: Get JSON list of tags with id and name
    - /api/tag/name: Get JSON list of tags with name only
    - Check [list of API on github Wiki page](https://github.com/redooor/redminportal/wiki/Public-API)
11. Able to add user to multiple groups. (issue #53)
12. Introduced HTML and Form helpers
    - HTML and Form helpers provide a shortcut to partial views.
    - Check [list of helpers on github Wiki page](https://github.com/redooor/redminportal/wiki/HTML-and-Form-Helpers)
13. Added tag to Page and Post (issue #146)
14. Added API for retrieving emails for authenticated admin users only
    - /api/email: Unused, returns an empty JSON list
    - /api/email/all: Get JSON list of emails
    - Check [list of Private API on github Wiki page](https://github.com/redooor/redminportal/wiki/Private-API)
15. UI: Group permission management, able to edit group permission for any route. (issue #69)
16. UI: User permission management, able to edit user permission for any route. (issue #69)
17. Search order by transaction id and more! (issue #137)
18. Search users by group, first name, last name, email and more!
19. Able to change Order statuses with tracking changes in Order model. (issue #138)
20. Added reusable Revision and Revisionable trait for tracking of changes in any model. (issue #138)
21. Added UI for viewing Revision History for Orders.
22. Allow User to change their password and names (issue #159)
23. Create new Order with product quantities (issue #165)

### Enhancements:
1. Category model missing relationships with other models. (issue #140)
2. Fixed UI: Category hierarchy list word wrap. (issue #133)
3. Developer: use grunt to publish assets to public folder automatically. (issue #139)
4. Moved static text to translation file. (issue #102)
5. Add getTotaldiscount method to Order. (issue #142)
6. Improved Tinymce editor to match RedminPortal look and feel.
7. Updated Tinymce to version 4.3.2.
8. Create Category Select reusable form (issue #130)
9. Rearranged menu to push User Management to bottom.
10. Increase default pagination to 50. (issue #121)
11. Introduced SorterController trait to reduce code for sortable pages.
12. Introduced partial Blade template for sortable header.
13. Overall Line count decreased from 23031 to 22805 with trait and partial template.
14. Improve Usability: after delete action should go back to last visited page (issue #131)
    - Announcement
    - Bundle
    - Category
    - Coupon
    - Group
    - Mailinglist
    - Media
    - Membership
    - Module
    - Order
    - Page
    - Portfolio
    - Post
    - Product
    - Promotion
    - Purchase
    - User
15. Improved Permission management, allow granular control for every page and action. (issue #69)
    - When users are deactivated after they login, they should be logged out and require re-login.
    - Various actions for create, update and delete are added in the permission checker.
16. Improved search engine to be reusable.
17. Payment Statuses can now be edited via config file. See [UPGRADE.md](UPGRADE.md) for instructions.
18. Product variant should inherit category from main product (issue #160)
19. Move printMenu to Html class and partial template (issue #155)
20. Add field automatically_apply to Coupon model (issue #162)
21. UI: Orders Items list should group into same product and show quantity (issue #166)

### Bug fixes:
1. User deletion: prevent user from deleting or deactivating own account while they are logged in. (issue #136)
2. UI: Page and Post cannot change category back to No Category. (issue #145)
3. Fix Tinymce editor not showing Bootstrap components correctly by adding container-fluid class to body.
4. Fix error message when deleting Category in used. Deleting Category will delete all related data. (issue #135)
5. Fix Module on change category doesn't load media. (issue #150)
6. Click menu overlay to close doesn't work on iPad. (issue #151)
7. Fix Build error for PHP7 due to getID3 package. Switch to JamesHeinrich/getID3. (issue #152)
8. Added delete image functionality to Bundles. (issue #149)
9. Fixed Typeahead styling messed up for email suggestions. (issue #156)
10. Upload image with same name will now be appended with number. (issue #154)
11. Product variant create/edit form on error doesn't populate previous inputs. (issue #134)
12. UI: menu unable to scroll on mobile screens (issue #161)

## Version 0.3.2 and 0.2.2
Code clean up, new features and UI improvements.

This update is generally v0.3.* compatible but there's a change with UserPricelist. Refer to [UPGRADE.md](UPGRADE.md) for the upgrading instructions.

### Enhancement:
1. Move Purchases into Orders (issue #95).
2. Download Orders report as Excel format xlsx (issue #114).
3. Search user by email or name (issue #68).
4. Users pagination increased to 50 per page (issue #121).
5. Improve UI: move breadcrumb to unused menu bar area (issue #103).

### Bug fixes:
1. (legacy support) Purchase export to excel not working. Purchase has deprecated. Use Orders instead. (issue #115)
2. User creation to check unique email and prompt error (issue #120).

## Version 0.3.1 and 0.2.1
New Bundle module and improvement to UI.

### New feature:
1. Bundle module allow bundling physical and digital products on a single price (issue #94, #104).
2. Get total value of a bundle (issue #97).
3. Able to add multiple coupons to an order (issue #106, #107, #109).
4. Coupon can be applied to bundles (issue #108).
5. Verify and save coupon against all products in an order (issue #110).
6. Order will now check coupon for multiple_coupons flag (issue #111).
7. Cleaner UI.

### Enhancement:
1. Added migrations to upgrade database from v0.1 to v0.2/v0.3 (issue #89).
2. Sidebar is now off-canvas (issue #91).
3. Exclude tests and non-production files from release package (issue #96).

### Bug fixes:
1. Resolves TokenMismatchException issue on login (issue #88).
2. User config file will override the default package config file (issue #90).
3. Add Remember_token column users table never run in migration (issue #105).

### Upgrade scripts:
1. Add migration scripts to help migrate database and folder structure from v0.1 (issue #104).

## Version 0.3.0
Focus on supporting Laravel 5.1.

### Important:
Version 0.3.0 should be backward compatible to Version 0.2.0.

If you're upgrading from v0.2.*, please refer to the [Upgrade Guide](#upgrade-guide).

### New feature:
Support Laravel 5.1 (issue #87).

## Version 0.2.0
Focus on supporting Laravel 5.0 and making sure all models and controllers work.

Version 0.2.0 is **SOMEWHAT** backward compatible.

If you're upgrading from v0.1.*, please refer to the [branch v0.2 Upgrade Guide](https://github.com/redooor/redminportal/blob/v0.2/UPGRADE.md).

Refer to [branch v0.2 CHANGELOG.md](https://github.com/redooor/redminportal/blob/v0.2/CHANGELOG.md)

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

## Version 0.1
Supports Laravel 4.2.

Refer to [branch v0.1 README.md Changelog](https://github.com/redooor/redminportal/blob/v0.1/README.md#change-log)
