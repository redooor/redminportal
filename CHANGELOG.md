# Change log

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
Code clean up, new features and UI improvements.

This update introduces some new tables. Refer to [UPGRADE.md](UPGRADE.md) for the upgrading instructions.

### New features:
1. Added product variations. You can now add variations to each product. (issue #125)
2. Added reusable partial templates for Modal Window and Language selector tab. (issue #130)
3. Added shipping properties to products: Weight, Length, Width, Height, units. (issue #126)

### Enhancements:
1. Category model missing relationships with other models. (issue #140)
2. Fixed UI: Category hierarchy list word wrap. (issue #133)
3. Developer: use grunt to publish assets to public folder automatically. (issue #139)
4. Moved static text to translation file. (issue #102)

### Bug fixes:
1. User deletion: prevent user from deleting or deactivating own account while they are logged in. (issue #136)

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
