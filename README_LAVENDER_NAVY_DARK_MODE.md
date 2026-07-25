# CLPMIS Lavender Gray & Navy UI + Night Mode

This is a direct project overlay.

## Design direction

The complete CLPMIS visual system now uses the selected fourth palette:

- Navy `#19243D`
- Lavender `#8178CA`
- Soft Violet `#EDEBFA`
- Mist Gray `#F5F7FB`
- Coral Red `#BE4A52`

The interface is minimalist, modern, light, formal, and appropriate for a government child labor prevention and monitoring system.

## Night mode

The theme control cycles through:

1. Light
2. Dark
3. System

The selection is saved in browser local storage under:

```text
clpmis-theme
```

The theme is applied before the page renders to prevent a bright flash when dark mode is active.

## Installation

1. Back up or commit the current project.
2. Extract this ZIP directly into the Laravel project root.
3. Merge folders and replace files.
4. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
npm install
npm run build
```

For development:

```powershell
npm run dev
php artisan serve
```

## Coverage

- Workspace sidebar and top bar
- Standard Breeze application navigation
- Login and authentication pages
- Dashboards
- Forms and input controls
- Tables and filters
- Modals
- Notifications
- Reports
- Audit pages
- Security, backup, and QA pages
- Mobile navigation
- Print behavior

No controller, route, model, migration, or database workflow was changed.
