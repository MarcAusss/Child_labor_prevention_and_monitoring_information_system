# CLPMIS Phase 8A — Automated Testing and Quality Assurance

This ZIP is a direct Laravel project overlay.

## Remaining roadmap

After Phase 8A, two phases remain:

1. Phase 8B — Production Deployment and Operations
2. Phase 9A — Final Integration, UAT, and Documentation

## Important warning

The automated test suite uses `RefreshDatabase`.

Never configure `phpunit.clpmis.xml` to use the live CLPMIS database.

The supplied configuration uses:

```text
CLPMIS_testing
```

## Installation

1. Commit or back up the project.
2. Extract this ZIP directly into the Laravel project root.
3. Allow Windows to merge folders and replace files.
4. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
npm run build
```

No application migration is added by Phase 8A.

## Create the dedicated test database

Using phpMyAdmin, create:

```text
CLPMIS_testing
```

Or run the supplied SQL file:

```text
database/testing/create_clpmis_testing_database.sql
```

PowerShell/XAMPP example:

```powershell
Get-Content ".\database\testing\create_clpmis_testing_database.sql" |
    & "C:\xampp\mysql\bin\mysql.exe" -u root
```

When MySQL requires a password:

```powershell
Get-Content ".\database\testing\create_clpmis_testing_database.sql" |
    & "C:\xampp\mysql\bin\mysql.exe" -u root -p
```

## Test database credentials

The dedicated PHPUnit configuration defaults to:

```text
Host: 127.0.0.1
Port: 3306
Database: CLPMIS_testing
Username: root
Password: empty
```

Edit only the database values in `phpunit.clpmis.xml` when your local MySQL credentials differ.

## Run the dedicated tests

```powershell
vendor\bin\phpunit -c phpunit.clpmis.xml
```

Or:

```powershell
php artisan test --configuration=phpunit.clpmis.xml
```

## Run the complete QA pipeline

```powershell
php artisan clpmis:qa
```

This runs:

1. `php artisan optimize:clear`
2. `php artisan migrate:status`
3. `php artisan route:list --except-vendor`
4. Dedicated CLPMIS PHPUnit tests
5. `php artisan clpmis:security-check`
6. `npm run build`

Skip the frontend build during a quick backend run:

```powershell
php artisan clpmis:qa --skip-build
```

## QA dashboard

Open:

```text
http://127.0.0.1:8000/quality-assurance
```

Only Admin and Super Admin can open this page.

QA JSON reports are stored privately under:

```text
storage/app/private/quality-assurance
```

## Included tests

- Required database schema
- Authentication and inactive accounts
- Role access boundaries
- Profiling Officer profile scope
- Viewer profile scope
- Notification ownership and privacy
- Security headers
- Private document storage
- Authenticated cache protection
- Report access
- Administration route smoke testing
- Notification payloads
- Password policy and idle-timeout configuration

## Provider file

The overlay replaces `bootstrap/providers.php` with:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\NotificationServiceProvider::class,
    App\Providers\DashboardServiceProvider::class,
    App\Providers\SecurityServiceProvider::class,
    App\Providers\BackupServiceProvider::class,
    App\Providers\QualityAssuranceServiceProvider::class,
];
```

If another custom provider exists, add it back after extraction.
