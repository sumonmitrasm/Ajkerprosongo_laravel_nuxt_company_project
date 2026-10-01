# Admin security maintenance

## Where the code lives

- `app/Models/Admin.php`: `hasModuleAccess()` checks a module permission;
  `canManageAccount()` checks whether the target account has more permissions.
- `app/Http/Middleware/AdminPermissionMiddleware.php`: applies both rules before
  account changes reach the controller.
- `app/Http/Controllers/AdminController.php`: validates input and saves records.
- `resources/views/admin/accounts/partials/admin-user-content.blade.php`: uses
  the same model rule to show the buttons. There is no `can_manage` database
  column or generated field.

## Account permissions

Active users with the matching Admin-module add/edit/delete permission can
manage ordinary accounts. They cannot assign Superadmin, change account types,
or modify accounts with permissions they do not have. The target's stored
permissions are checked even if the target is disabled. Only Superadmins can
manage Superadmin accounts or grant permissions. Other content permissions
continue to work as before. Route checks and visible buttons use the same rules.

A disabled admin is logged out on their next protected request. Users cannot
disable, delete or change the type of their own account.

## Production

Use HTTPS and point the server document root to `adminpanel/public`.
Set `APP_ENV=production`, `APP_DEBUG=false` and `SESSION_SECURE_COOKIE=true`
in the production environment. Production debug output is also disabled in
`config/app.php`. Keep secure cookies off when using plain HTTP locally.

The public `/clear-cache` URL was removed. Use terminal commands instead:

```sh
php artisan config:clear
php artisan view:clear
```

On deployment, build assets and cache configuration/views:

```sh
npm ci --ignore-scripts
npm run build
php artisan config:cache
php artisan view:cache
```

`npm run build` copies the locked SweetAlert, Moment, Quill and DOMPurify files
and their source maps into the template. Views use Laravel's standard `asset()`
function; the custom `$adminAsset` helper was removed. After deploying changed
static files, refresh the browser cache if old files are still being served.
This does not change Laravel's database/Redis query cache.
Scripts are served locally. Settings are queried only when
rendering an admin layout/login page, rather than during every API/CLI request.
NuGet.exe was moved out of public into `storage/app/template-tools` locally;
it is not needed in deployment.

## Editor advisory and limits

Quill 2.0.3 has an unresolved upstream HTML export advisory:
https://github.com/advisories/GHSA-v3m3-f69x-jf25

`editor-security.js` sanitizes `getSemanticHTML()` output with DOMPurify.
The post editor also sanitizes loaded HTML and HTML submitted through the form.
Only YouTube and Vimeo iframe embeds are kept. Sanitizing on load/submission,
rather than on every keystroke, avoids unnecessary work while typing.

This is an application mitigation, not an upstream package fix. The npm audit
still reports one low-severity advisory. Do not remove the sanitizer to hide
the audit warning. Browser sanitization is not server-side validation: future
frontend HTML rendering/API consumers must sanitize untrusted rich text too.

## Verification

```sh
php vendor/bin/phpunit tests/Feature/AdminSecurityTest.php
composer --no-plugins --no-scripts audit --locked
npm audit --offline=false
```

Open `tests/Browser/editor-security.html` in Chrome to run the local browser
smoke test for imports, formatting, list export, malicious HTML, trusted
video embeds, Moment and SweetAlert. Production server configuration and
all unused template plugins are outside these regression tests.

The original Breeze `/dashboard` and `/profile` routes were already commented
out before this security work. The full legacy suite still has failures for
those routes and expects `/` to return 200 instead of the admin-login redirect.
Those failures are separate from `AdminSecurityTest` and should not be hidden
by enabling unused account routes or deleting tests.
