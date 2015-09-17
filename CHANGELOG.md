# Change log

* [Version 0.3.3 and 0.2.3](#version-033-and-023)
* [Version 0.3.2](#version-032)
* [Version 0.3.1](#version-031)
* [Version 0.3.0](#version-030)
* [Version 0.2](#version-02)
* [Version 0.1](#version-01)


* Version 0.3 supports Laravel 5.1 only.
* Looking for RedminPortal for Laravel 5.0? Visit the [v0.2 Branch](https://github.com/redooor/redminportal/tree/v0.2).
* Looking for RedminPortal for Laravel 4.2? Visit the [v0.1 Branch](https://github.com/redooor/redminportal/tree/v0.1).

Version 0.2 and 0.3 are developed in parallel. The only difference between them is the Laravel version they support. However, this may change in future.

## Version 0.3.3 and 0.2.3
Code clean up, new features and UI improvements.

This update introduces some new tables. Refer to [UPGRADE.md](UPGRADE.md) for the upgrading instructions.

### New features:
1. Added product variations. You can now add variations to each product. (issue #125)

### Enhancements:

### Bug fixes:

## Version 0.3.2
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

## Version 0.3.1
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

## Version 0.2
Focus on supporting Laravel 5.0 and making sure all models and controllers work.

Version 0.2.0 is **SOMEWHAT** backward compatible.

If you're upgrading from v0.1.*, please refer to the [branch v0.2 Upgrade Guide](https://github.com/redooor/redminportal/blob/v0.2/UPGRADE.md).

Refer to [branch v0.2 CHANGELOG.md](https://github.com/redooor/redminportal/blob/v0.2/CHANGELOG.md)

## Version 0.1
Supports Laravel 4.2.

Refer to [branch v0.1 README.md Changelog](https://github.com/redooor/redminportal/blob/v0.1/README.md#change-log)
