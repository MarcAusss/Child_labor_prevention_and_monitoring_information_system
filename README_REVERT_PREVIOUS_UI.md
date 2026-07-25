# CLPMIS Revert to Previous UI

This direct overlay removes the Lavender Gray & Navy night-mode implementation by restoring the UI files from the previous CLPMIS modernization package.

It also restores the matching `WorkspaceShell` component, fixing the `Undefined array key "pattern"` error caused by the incompatible navigation data structure.

## Install

1. Extract this ZIP directly into the Laravel project root.
2. Choose **Merge folders** and **Replace files**.
3. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
php artisan view:clear
npm run build
```

For development:

```powershell
npm run dev
php artisan serve
```

## Optional cleanup

The following two files from the night-mode package are no longer referenced and may be deleted manually:

```text
resources/views/components/theme-toggle.blade.php
resources/views/components/theme-script.blade.php
```

Leaving them in the project does not affect the application.

No routes, controllers, models, migrations, or database records are changed.
