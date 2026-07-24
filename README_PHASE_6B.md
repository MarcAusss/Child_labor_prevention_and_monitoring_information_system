# CLPMIS Phase 6B — Modern Dashboard and Navigation

This ZIP is a direct Laravel project overlay.

## Installation

1. Commit or back up the current project.
2. Extract the ZIP directly inside the Laravel project root.
3. Allow Windows to merge folders and replace `bootstrap/providers.php`.
4. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
npm run build
```

No migration is required.

Verify:

```powershell
php artisan route:list --name=workspace.dashboard
```

Open:

```text
http://127.0.0.1:8000/workspace
```

## Included

- Modern formal dashboard design
- Responsive dark sidebar
- Centralized module navigation
- Role-aware links
- Notification bell integration
- Profile workload summary
- Profile status distribution
- Six-month profile trend
- Recent profiles
- Recent notifications
- Upcoming audits for Admin and Super Admin
- Recent activity for authorized roles
- New reusable `<x-workspace-shell>` layout

## Existing modules are not overwritten

The package creates a new `/workspace` dashboard instead of replacing an existing role dashboard. This avoids breaking the Phase 1 dashboard routes.

After testing, the existing login redirect or dashboard route may be changed to:

```php
route('workspace.dashboard')
```

## Provider file

The overlay replaces `bootstrap/providers.php` with:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\NotificationServiceProvider::class,
    App\Providers\DashboardServiceProvider::class,
];
```

If the project has other custom providers, add them back after extraction.

## Route-aware navigation

Navigation links only appear when their named routes exist. The dashboard expects these route names when their modules are installed:

```text
child-laborers.index
child-laborers.create
child-laborers.show
notifications.index
audit-schedules.index
reports.child-laborers.index
reports.statistics.index
users.index
activity-logs.index
```

Missing routes are automatically hidden.
