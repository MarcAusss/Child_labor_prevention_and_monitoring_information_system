# CLPMIS Phase 7B — Backup and Recovery

This ZIP is a direct Laravel project overlay.

## Remaining roadmap

After Phase 7B, three phases remain:

1. Phase 8A — Automated Testing and Quality Assurance
2. Phase 8B — Production Deployment and Operations
3. Phase 9A — Final Integration, UAT, and Documentation

## Installation

1. Commit or back up the project.
2. Extract this ZIP directly inside the CLPMIS Laravel project root.
3. Allow Windows to merge folders and replace files.
4. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
php artisan migrate
npm run build
```

5. Copy any required values from `.env.backup.example` into the real `.env`.

6. Verify routes and commands:

```powershell
php artisan route:list --name=backups
php artisan list | findstr clpmis:backup
```

7. Open:

```text
http://127.0.0.1:8000/backups
```

## XAMPP MySQL utility paths

When `mysqldump` is not recognized, add these to `.env`:

```dotenv
CLPMIS_MYSQLDUMP_PATH="C:\xampp\mysql\bin\mysqldump.exe"
CLPMIS_MYSQL_PATH="C:\xampp\mysql\bin\mysql.exe"
```

Use the actual XAMPP or MySQL installation path on the computer.

## Commands

Create a complete backup:

```powershell
php artisan clpmis:backup:create
```

Verify a backup:

```powershell
php artisan clpmis:backup:verify BACKUP_ID
```

Clean up expired backups:

```powershell
php artisan clpmis:backup:cleanup
```

Restore the database:

```powershell
php artisan clpmis:backup:restore BACKUP_ID
```

The restore command asks for confirmation unless `--force` is supplied.

## Backup contents

Every completed archive includes:

```text
database.sql
manifest.json
files/private/...
```

The private files section covers Laravel's `storage/app/private` data while excluding the backup directory itself. This normally includes private profile documents and profile photographs.

## Important restoration behavior

Database restoration is command-line only.

Uploaded files are not automatically restored because overwriting the current document repository requires an administrator to review file retention and conflicts. Extract `files/private` manually into `storage/app/private` only after backing up the existing directory.

## Scheduled backups

Scheduling is disabled by default.

To enable it in `.env`:

```dotenv
CLPMIS_SCHEDULED_BACKUP_ENABLED=true
CLPMIS_SCHEDULED_BACKUP_TIME=01:30
CLPMIS_SCHEDULED_BACKUP_CLEANUP_ENABLED=true
```

The production server must also run Laravel's scheduler. For local testing:

```powershell
php artisan schedule:work
```

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
];
```

If another custom provider exists, add it back after extraction.

## Security

- Only Admin and Super Admin can use the web backup page.
- Stored archives remain under Laravel's private local disk.
- Each archive has a SHA-256 checksum.
- Downloads use an authorized controller and no-store cache headers.
- Database passwords are passed to MySQL utilities through the process environment rather than command arguments.
- Restore remains outside the web interface.
