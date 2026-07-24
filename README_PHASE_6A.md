# CLPMIS Phase 6A — Notifications and Inbox

This ZIP is a direct Laravel project overlay.

## Installation

1. Back up or commit the current CLPMIS project.
2. Extract the ZIP directly into the Laravel project root.
3. Allow Windows to merge the folders and replace `bootstrap/providers.php`.
4. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
php artisan migrate
npm run build
```

5. Verify:

```powershell
php artisan route:list --name=notifications
```

## Direct test URL

```text
http://127.0.0.1:8000/notifications
```

## Included functionality

- Private notification inbox for every authenticated user
- Unread, read, type, and text filters
- Mark one notification read or unread
- Mark all notifications read
- Safe internal notification links
- Unread notification count
- Reusable `<x-notification-bell />` component for the later dashboard redesign
- Automatic notifications for:
  - Child profile submitted
  - Child profile returned
  - Child profile approved
  - Child profile archived or restored
  - Audit schedule assignment
  - Audit evaluation finalization
  - Intervention creation and status changes
  - Profile document uploads
- Activity-log records when a notification is opened or all are marked read

## Important provider file

This overlay includes:

```text
bootstrap/providers.php
```

It keeps `AppServiceProvider` and adds:

```php
App\Providers\NotificationServiceProvider::class
```

If your project already registered another custom provider, add it back to the array after extraction.

## Dashboard integration

Dashboard and sidebar links are intentionally not modified yet.

During the dashboard design phase, place this component in the top bar:

```blade
<x-notification-bell />
```

A sidebar link can later point to:

```blade
route('notifications.index')
```

## No queue worker required

Notifications use the database channel synchronously. A queue worker is not required for Phase 6A.
