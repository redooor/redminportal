# Change log

* [Version 0.2.2](#version-022)
* [Version 0.2.1](#version-021)
* [Version 0.2.0](#version-020)
* [Version 0.1](#version-01)


* Version 0.2 supports Laravel 5.0 only.
* Looking for RedminPortal for Laravel 5.1? Visit the [v0.3 Branch](https://github.com/redooor/redminportal/tree/v0.3).
* Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

Version 0.2 and 0.3 are developed in parallel. The only difference between them is the Laravel version they support. However, this may change in future.

## Version 0.2.2
Code clean up, new features and UI improvements.

This update is generally v0.2.* compatible but there's a change with UserPricelist. Refer to [UPGRADE.md](UPGRADE.md) for the upgrading instructions.

### Enhancement:
1. Move Purchases into Orders (issue #95).
2. Download Orders report as Excel format xlsx (issue #114).
3. Search user by email or name (issue #68).
4. Users pagination increased to 50 per page (issue #121).

### Bug fixes:
1. (legacy support) Purchase export to excel not working. Purchase has deprecated. Use Orders instead. (issue #115)

## Version 0.2.1
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

## Version 0.1
Supports Laravel 4.2.

Refer to [branch v0.1 README.md Changelog](https://github.com/redooor/redminportal/blob/v0.1/README.md#change-log)
