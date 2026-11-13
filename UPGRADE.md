# Upgrade Guide

## Upgrading to Laravel 13 (v2.x)

This release upgrades the package from Laravel 5.8 to Laravel 13. The changes
are extensive internally, but most applications need only a handful of steps.

### Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.3 |
| Laravel | ^13.0 |

---

### Step 1 — Update your application to Laravel 13

Follow the [official Laravel upgrade guides](https://laravel.com/docs/upgrade)
for each major version your application passes through on the way to 13.

The most common breaking changes encountered along the way:

- **Laravel 9**: `Input` facade removed (use `Request`), `str_random()` /
  `array_get()` helpers removed, route `namespace` group key removed.
- **Laravel 10**: Anonymous migration classes required, `password_resets` table
  renamed to `password_reset_tokens`.
- **Laravel 11**: `AuthorizesRequests`, `DispatchesJobs`, `ValidatesRequests`
  traits removed from the base controller skeleton.
- **Laravel 13**: `Route::controller()` removed (not used by this package),
  `Model::paginate()` default page size changed from 15 to 25 (this package
  always passes an explicit size from config, so no impact), job serialisation
  format changed — drain queues before deploying.

---

### Step 2 — Replace `laravelcollective/html` with `rdx/laravelcollective-html`

The original `laravelcollective/html` package is no longer maintained and does
not support Laravel 13. The drop-in replacement keeps identical class names so
no view changes are required.

Remove the old package and add the new one:

```bash
composer remove laravelcollective/html
composer require rdx/laravelcollective-html:^6.10
```

If you registered the service provider or aliases manually in
`config/app.php`, **no changes are needed** — the class names are identical:

```php
// These remain exactly the same
Collective\Html\HtmlServiceProvider::class
'Form' => Collective\Html\FormFacade::class
'HTML' => Collective\Html\HtmlFacade::class
```

---

### Step 3 — Remove `orchestra/imagine` (if required by your app)

`orchestra/imagine` is no longer maintained and is not a dependency of this
package. If your own `composer.json` requires it, remove it:

```bash
composer remove orchestra/imagine
```

The underlying `imagine/imagine` library is already pulled in directly. No
code changes are needed if you were only using it through this package.

The package no longer registers the Orchestra `Imagine` facade alias. If your
own code calls `Imagine::...`, use the `Imagine\...` classes from
`imagine/imagine` directly, or register your own alias.

---

### Step 4 — Update your `password_resets` table (existing installs)

Laravel 10+ uses `password_reset_tokens` as the default table name. Fresh
installs will get the new table automatically via the updated migration.

**Existing installs** must either rename the table:

```sql
ALTER TABLE password_resets RENAME TO password_reset_tokens;
```

Or override the table name in your published auth config
(`config/vendor/redooor/redminportal/auth.php`) to keep using the old name:

```php
'redminpasswords' => [
    'provider' => 'redminprovider',
    'table'    => 'password_resets',   // keep your existing table name
    'expire'   => 60,
],
```

---

### Step 5 — Re-publish the config (recommended)

Re-publish to pick up the updated `auth.php` (removed deprecated `email` key,
updated table name):

```bash
php artisan vendor:publish \
    --provider="Redooor\Redminportal\RedminportalServiceProvider" \
    --tag=config --force
```

Merge any local customisations back in afterwards.

---

### Step 6 — Re-publish the migrations (if previously published)

Re-publish to get the updated anonymous-class migrations and the updated
password reset migration:

```bash
php artisan vendor:publish \
    --provider="Redooor\Redminportal\RedminportalServiceProvider" \
    --tag=migrations --force
```

---

### Step 7 — Remove `Session::token()` from a published login view

If you published and customised the login view, remove any explicit CSRF hidden
field that uses `Session::token()`. The `Form::open()` call already injects the
token automatically:

```blade
{{-- Remove this line if present --}}
<input type="hidden" name="_token" value="{{ Session::token() }}" />
```

---

### Step 8 — Admin UI moved from Bootstrap 3 to Bootstrap 5.3

The admin interface now uses **Bootstrap 5.3**, the Redmaterials 0.2 theme, and Font Awesome Free 7.
Applications that never published or customised the portal's views or assets only need to re-publish the assets (Step 9).
If you published views, override Blade templates, or reuse the portal's CSS/JS classes, read the rest of this step.

**What changed for end users of the admin:** the look is the same Material style, but the panels are now cards, icons are
Font Awesome, the file chooser, date picker and rich text editor are new components (see Step 11), and the layout breakpoints moved
(see below).

#### Class and attribute mapping (for published or custom views)

| Bootstrap 3 | Bootstrap 5.3 |
|---|---|
| `panel panel-default`, `panel-heading`, `panel-title`, `panel-body`, `panel-footer` | `card`, `card-header`, `card-title`, `card-body`, `card-footer` |
| `form-group` | `mb-3` |
| `control-label` | `form-label` (horizontal forms: `row mb-3` + `col-form-label col-*`) |
| `btn-default`, `btn-xs`, `btn-block` | `btn-secondary`, `btn-sm`, `w-100` |
| `label label-success` (…) | `badge text-bg-success` |
| `help-block`, `input-group-addon` | `form-text`, `input-group-text` |
| `pull-left`, `pull-right`, `text-right`, `text-left` | `float-start`, `float-end`, `text-end`, `text-start` |
| `table-condensed`, `img-responsive`, `sr-only` | `table-sm`, `img-fluid`, `visually-hidden` |
| `well` | `card card-body bg-body-tertiary` |
| `hidden-xs`, `visible-xs` | `d-none d-md-block`, `d-md-none` |
| `.checkbox` / `.radio` wrappers | `.form-check` with `form-check-input` / `form-check-label` |
| `.dropdown-menu > li > a` | add `dropdown-item` to every link; `dropdown-menu-end` replaces `pull-right` |
| `.nav-tabs > li.active` | `.nav-item` / `.nav-link`, and `.active` goes on the `<a class="nav-link">` |
| `<select class="form-control">` | `<select class="form-select">` |
| `close` button with `&times;` | `<button class="btn-close" data-bs-dismiss="modal" aria-label="Close">` |
| `data-toggle`, `data-target`, `data-dismiss`, `data-placement`, `data-parent` | `data-bs-toggle`, `data-bs-target`, `data-bs-dismiss`, `data-bs-placement`, `data-bs-parent` |
| `navbar navbar-default navbar-fixed-top` | `navbar navbar-expand-md navbar-light bg-body fixed-top` |
| `media`, `media-left`, `media-body` | `d-flex`, `flex-shrink-0`, `flex-grow-1` |

**Breakpoints shifted by one step.** Bootstrap 3's `sm/md/lg` (768/992/1200px) are Bootstrap 5's `md/lg/xl`.
`col-sm-6 col-md-4` becomes `col-md-6 col-lg-4`; `col-md-push-9` / `col-md-pull-3` become `order-lg-last` / `order-lg-first`;
`col-md-offset-3` becomes `offset-lg-3`.

**Glyphicons are gone**, and so is Font Awesome 4. Use Font Awesome Free 7 with the `fa-solid` prefix:

| Glyphicon | Font Awesome 7 |
|---|---|
| `glyphicon-remove` | `fa-solid fa-xmark` |
| `glyphicon-ok` | `fa-solid fa-check` |
| `glyphicon-edit` | `fa-solid fa-pen-to-square` |
| `glyphicon-eye-open` / `eye-close` | `fa-solid fa-eye` / `fa-eye-slash` |
| `glyphicon-menu-left` / `menu-right` | `fa-solid fa-chevron-left` / `fa-chevron-right` |
| `glyphicon-option-horizontal` / `option-vertical` | `fa-solid fa-ellipsis` / `fa-ellipsis-vertical` |
| `glyphicon-log-out`, `glyphicon-cog`, `glyphicon-user` | `fa-solid fa-right-from-bracket`, `fa-gear`, `fa-user` |
| `glyphicon-calendar`, `glyphicon-link`, `glyphicon-time` | `fa-solid fa-calendar`, `fa-link`, `fa-clock` |
| `fa fa-search` (FA 4) | `fa-solid fa-magnifying-glass` |

`Category::printCategory()` now emits `<i class='fa-solid fa-chevron-right'>` instead of a glyphicon span.
If you test its HTML output, update the expectation.

**Pagination.** Lists render the paginator with Laravel's `pagination::bootstrap-5` view
(`$models->render('pagination::bootstrap-5')`). Laravel 13's default paginator is Tailwind, which the admin never styled.

**JavaScript.** Bootstrap 5 does not need jQuery, but the portal still loads jQuery 3.7 (jQuery UI, typeahead and tags input need it).
jQuery must load before `bootstrap.bundle.min.js` for `$(el).modal('show')` to keep working. Event names (`shown.bs.tab`, …) are unchanged.

---

### Step 9 — Re-publish the assets and remove obsolete files

```bash
php artisan vendor:publish --tag=public --force
```

`--force` overwrites files but **never deletes** files that no longer exist. Delete these from
`public/vendor/redooor/redminportal/` (any that exist):

```
css/jasny-bootstrap.css          css/jasny-responsive.css
css/font-awesome.min.css         css/datetimepicker/
css/redmin-tinymce.css           css/redmin-tinymce.css.map     css/redmin-tinymce.min.css
fonts/                           (Glyphicons and Font Awesome 4; fonts now live in webfonts/)
js/bootstrap.min.js              js/bootstrap-fileupload.js
js/datetimepicker/               js/moment/                     js/tinymce/
js/jquery/jquery.js              js/jquery/jquery.min.map       js/bootstrap-tagsinput.js.map
```

New files appear under `css/jodit/`, `css/tempus-dominus/`, `js/jodit/`, `js/popper/`, `js/tempus-dominus/`,
`js/redmin-fileinput.min.js`, `webfonts/` and `licenses/` (third-party licence notices).

Then clear compiled views: `php artisan view:clear`.

If you published the views (`resources/views/vendor/redooor/redminportal`), they take precedence over the package and **still contain
Bootstrap 3 markup**. Either delete your published copy to use the new views, or re-publish with `--force` and re-apply your changes
using the mapping in Step 8. Compare with `git diff` before overwriting.

---

### Step 10 — Published config

`config/vendor/redooor/redminportal/tinymce.php` keeps its name and now lists the **content stylesheets of the rich text editor**.
Old values still work: an entry ending in `redmin-tinymce.min.css` is mapped to `redmin-editor.min.css` automatically. Update your copy
to `vendor/redooor/redminportal/css/redmin-editor.min.css` when convenient.

---

### Step 11 — Replaced third-party components

| Was | Now | What to check |
|---|---|---|
| TinyMCE 4.3.8 (end of life) | **Jodit 4** (MIT) via `plugins/editor` | `@include('redminportal::plugins/editor', ['editorImages' => $model->images])`. `plugins/tinymce` still works but is deprecated, and `tinyImages` is still accepted. TinyMCE plugins and `tinymce.init` options no longer apply. Images from the record are inserted with the new "insert uploaded image" toolbar button. |
| Jasny `fileupload` | First-party `redmin-fileinput` | Markup `data-redmin-fileinput`; load `js/redmin-fileinput.min.js`. The form still posts a single file input named `image`. Jasny CSS/JS and its `data-provides` / `data-dismiss="fileupload"` attributes are gone. |
| eonasdan datetimepicker + moment (Coupons) | **Tempus Dominus 6** + Popper | Dates still post as `d/m/Y h:i A`; the locale is fixed to `en-US` so AM/PM and digits are never localised. |
| jQuery UI datepicker, bootstrap-tagsinput, typeahead.js | unchanged | Tags now render as `badge text-bg-info`. |

The editor makes **no CDN requests** (source mode uses a plain textarea).

---

### Step 12 — Actions that change data now use POST with a CSRF token

The admin routes now verify the CSRF token. Actions that used to be plain links (`GET`) are now `POST`:
logout, every `delete/{id}`, every `imgremove/{id}`, products `delete-variant-json/{id}` and `variant-imgremove/{product}/{id}`,
users `activate/{id}` and `deactivate/{id}`, orders `update/{field}/{id}/{status}`, and medias `duration/{id}`.
Requests that arrive from the admin pages themselves are accepted by the browser's `Sec-Fetch-Site: same-origin` header or by the token.

If you published or wrote your own admin views, check them:

| Your view has | Change it to |
|---|---|
| A `<form method="POST">` written by hand | Add `@csrf` as its first child. Forms made with `Form::open` already have the token. |
| A link such as `<a href="admin/pages/delete/1">` | Keep the link and add `class="btn-confirm"` (needs `@include('redminportal::partials.confirm-delete')`), or add `data-method="post"`. |
| `$.get` / `$.getJSON` to one of the routes above | `$.post(url, { _token: window.redminCsrfToken() }, ...)` |
| Other Ajax calls that `POST` | Send the `X-CSRF-TOKEN` header or a `_token` field. |

Layouts must contain `<meta name="csrf-token" content="{{ csrf_token() }}">` and load `js/redmin-post.min.js`
(the three package layouts already do; re-publish assets with `php artisan vendor:publish --tag=public --force`).
Links that still use `GET` for these routes now return `404`/`405`.

---

### Step 13 — Upload file names and types are validated

Media uploads now accept only the file extensions listed in the new `media` config (`allowed_extensions`, `max_size`),
and stored file names are cleaned (letters, digits, `.`, `_`, `-`). Image uploads are stored with an extension that
matches the real image type (jpg, png, gif). If you need other media types, re-publish the config
(`php artisan vendor:publish --tag=config`) and add them to `media.allowed_extensions`.

---

### Step 14 — Permission checks for actions that used to count as "view"

These actions now need **update** instead of **view**: `activate`, `deactivate`, `imgremove`, `variant-imgremove`, media `uploadform`, `upload` and `duration`, product `edit-variant`; `create-variant` needs **create**; `delete-variant-json` needs **delete**.
Groups or users that were allowed these actions only through the `view` permission must now also be given `update` (or `create`/`delete`).
Explicit entries such as `admin.users.deactivate.view` no longer apply to these routes; use `admin.users.deactivate.update`.
Deleting a purchase (`admin/purchases/delete`) also needs `admin.orders.delete`, and adding one (`admin/purchases/store`) also needs `admin.orders.create`, because purchases are rows of the orders table. `store` on orders and purchases always needs the `create` permission, whatever `id` is sent.
Custom controllers that read `Request::get('id')` should use `Request::input('id')`, so they see the same value as the permission check.

---

### Step 15 — Accounts: administrator seeding, privilege checks, sign-in limits

**Seeder.** `RedminSeeder` no longer creates `admin@admin.com` / `admin` and no longer deletes existing users and groups (it does nothing when users exist).
For a new site set `REDMIN_ADMIN_EMAIL` and `REDMIN_ADMIN_PASSWORD` when you seed, or read the generated password that is printed once:

    REDMIN_ADMIN_EMAIL=you@example.com REDMIN_ADMIN_PASSWORD='choose-a-long-password' php artisan db:seed --class="Database\Seeders\RedminSeeder"

For local development use the new demo seeder, which creates `admin@admin.com` / `admin` and refuses to run when `APP_ENV=production`:

    php artisan db:seed --class="Database\Seeders\RedminDemoSeeder"

If you already have a site that was seeded with the old seeder, sign in and change the password of `admin@admin.com` (or deactivate the account).
Scripts, CI jobs and tests that expect `admin@admin.com` / `admin` from `RedminSeeder` should call `RedminDemoSeeder`, or set the two variables above.
After updating the package run `composer dump-autoload` so the new seeder class is found.

**Privilege checks.** On the users and groups pages:

- you can only grant permissions you hold yourself, and only assign groups whose permissions you fully hold (values `0` and `-1` can always be set);
- you cannot edit, activate, deactivate or delete a user, or edit or delete a group, that holds more access than you.

Use an administrator with full access for those tasks. Custom code can use `Permissable::hasPermission($path, $action)`, `canGrant(array $permissions)` and `coversUser($user)`.

**Sign-in.**

- After 5 failed attempts within a minute for the same email and IP address, sign-in is refused until the minute has passed.
- New passwords (new users, user edits and My Account) need at least 10 characters. Change it with the new `password.min_length` config (publish the config with `--tag=config`). Existing passwords keep working.
- Changing a user's password (by an administrator or by the user) signs out that user's other sessions on their next request. Changing your own password in My Account keeps you signed in; before, it signed you out.

---

### Upgrade checklist

Database: **no table other than `password_reset_tokens` changed.** Your existing
data and the other 44 migrations need no changes, and already-run migrations
will not run again.

- [ ] Application is on PHP 8.3+ and Laravel 13 (Step 1)
- [ ] `laravelcollective/html` replaced with `rdx/laravelcollective-html` (Step 2)
- [ ] `orchestra/imagine` removed, and any `Imagine::` facade calls replaced (Step 3)
- [ ] `password_reset_tokens` table created or renamed from `password_resets` (Step 4)
- [ ] Config re-published, so `auth.php` points to `password_reset_tokens` (Step 5)
- [ ] Published migrations re-published, if you had published them (Step 6)
- [ ] `Session::token()` removed from a published login view (Step 7)
- [ ] Assets re-published with `--force`, obsolete files deleted, views cleared (Step 9)
- [ ] Published views either deleted or migrated from Bootstrap 3 to 5.3 markup (Step 8)
- [ ] Published `tinymce.php` config reviewed (Step 10); custom editor code moved to Jodit (Step 11)
- [ ] Seeder command updated to `db:seed --class="Database\Seeders\RedminSeeder"` (Step 15: set `REDMIN_ADMIN_EMAIL` / `REDMIN_ADMIN_PASSWORD`; use `RedminDemoSeeder` for local development)
- [ ] Custom views use `@csrf`, `data-method="post"` or `window.redminCsrfToken()` for the actions that now use `POST` (Step 12)
- [ ] Media types you need are listed in `media.allowed_extensions` (Step 13)
- [ ] Groups and users have the `update`, `create` or `delete` permissions the reclassified actions now need (Step 14)
- [ ] The password of any `admin@admin.com` account that still uses `admin` is changed (Step 15)
- [ ] If you subclass `Api` or the report controllers: they no longer use the
      `AuthorizesRequests`, `DispatchesJobs` or `ValidatesRequests` traits, and
      reports now use `Excel::download(new ...Export)` instead of `Excel::create`
- [ ] Unauthenticated visits to `/admin/*` now redirect to `login` without
      remembering the intended URL (behaviour change from `redirect()->guest()`)

Admin URLs, routes, models and table names are otherwise unchanged.

---

### Changelog summary

| Area | Old | New |
|---|---|---|
| PHP | ^7.1.3 | ^8.3 |
| Laravel | ^5.8 | ^13.0 |
| HTML/Form package | `laravelcollective/html` | `rdx/laravelcollective-html ^6.10` |
| Image processing | `orchestra/imagine` (wrapper) | `imagine/imagine ^1.4` (direct) |
| Excel exports | Laravel Excel 2.x (`Excel::create`) | Laravel Excel 3.1 (`Excel::download`) |
| Password reset table | `password_resets` | `password_reset_tokens` |
| Migrations | Named classes | Anonymous classes |
| CSRF in login view | `Session::token()` | Handled by `Form::open()` |
| Admin UI framework | Bootstrap 3.3 + Glyphicons | Bootstrap 5.3 + Redmaterials 0.2 + Font Awesome 7 |
| Build tooling | Grunt, Bower, LESS | npm, Sass (contributors only) |
| Data-changing admin actions | `GET` links, no CSRF check | `POST` with CSRF token |
| Media uploads | Any file name and type | Cleaned names, allowed extensions, size limit |
| Default administrator | `admin@admin.com` / `admin` from `RedminSeeder` | From `REDMIN_ADMIN_*` variables or generated; demo seeder for local use |
| Minimum password length | 6 | 10 (`password.min_length`) |
| Rich text editor | TinyMCE 4.3.8 | Jodit 4 |
| File chooser | Jasny fileupload | `redmin-fileinput` |
| Coupon date picker | eonasdan datetimepicker + moment | Tempus Dominus 6 |

---

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
