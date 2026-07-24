# CLPMIS Complete UI/UX Modernization

This ZIP is a direct Laravel project overlay created from the latest uploaded CLPMIS project.

## Installation

1. Commit or back up the current project.
2. Extract the ZIP directly inside the Laravel project root.
3. Allow Windows to merge folders and replace files.
4. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
npm install
npm run build
```

During development, restart Vite when it is already running:

```powershell
npm run dev
```

No database migration is required.

## What was redesigned

- Shared authenticated application shell
- Responsive sidebar and mobile navigation
- Top bar, account menu, and notification control
- Login, registration, verification, recovery, reset, and confirmation screens
- Super Admin dashboard
- Admin dashboard
- Profiling Officer dashboard
- Viewer dashboard
- Operations dashboard inheritance
- Child laborer registry
- Account settings
- Forms, labels, inputs, selects, text areas, and validation messages
- Buttons, dropdowns, modals, links, alerts, badges, tables, pagination surfaces, and cards
- Reports and printable report color identity
- 403, 404, 419, and 500 error pages
- Shared page headers and empty-state presentation

All existing pages that use the shared CLPMIS shells inherit the new visual system, including profiles, birth information, addresses, guardians, households, education, employment, hazards, health, interventions, documents, audits, evaluations, reports, notifications, users, activity logs, backup, security, and quality assurance.

## Functional changes

The redesign preserves routes, forms, validation, policies, and workflows.

The four role dashboard controllers were extended only to provide useful summary counts and recent records for the redesigned dashboards.

## Verification completed

- 97 Blade views compiled and passed PHP lint after Blade compilation.
- 130 application routes compiled successfully.
- Modified PHP controllers and the workspace component passed PHP syntax validation.
- `tailwind.config.js` passed Node syntax validation.

The frontend production build could not be executed inside the Linux validation environment because the uploaded project contained Windows-specific Node modules. Run `npm install` and `npm run build` on the Windows project after extraction.

## Rollback

With Git:

```powershell
git restore .
```

Or restore the project backup made before extraction.
