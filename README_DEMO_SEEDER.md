# CLPMIS Complete Demonstration Seeder

This ZIP is a direct Laravel project overlay.

## What it seeds

- Four active roles
- Six usable role accounts
- Ten synthetic child laborer profiles
- Draft, Submitted, Returned, Approved, and Archived workflows
- Birth and residential information
- Parents or guardians
- Household members
- Education records
- Employment records
- Work hazards
- Health assessments
- Interventions
- Private demonstration documents
- Audit schedules and evaluations
- Activity logs
- Notifications
- Data distributed across several months for dashboard charts

All names and case information are synthetic demonstration records.

## Installation

1. Back up or commit the project.
2. Extract this ZIP directly into the Laravel project root.
3. Select **Merge folders** and **Replace files**.
4. Run:

```powershell
composer dump-autoload
php artisan optimize:clear
```

## Seed a clean database

This removes existing database records and rebuilds everything:

```powershell
php artisan migrate:fresh --seed
```

Use this only when it is safe to erase the current database.

## Seed without deleting existing records

```powershell
php artisan db:seed
```

Or run only the complete demonstration seeder:

```powershell
php artisan db:seed --class=CLPMISDemoSeeder
```

The demonstration seeder is safe to rerun. It updates fixed demo records instead of creating duplicate profiles.

## Login accounts

All accounts use:

```text
Password123!
```

| Role | Email |
|---|---|
| Super Admin | superadmin@clpmis.test |
| Admin | admin@clpmis.test |
| Admin / Audit | admin.audit@clpmis.test |
| Profiling Officer | profiling@clpmis.test |
| Profiling Officer 2 | profiling2@clpmis.test |
| Viewer | viewer@clpmis.test |

## Environment protection

`DatabaseSeeder` always creates roles and default accounts.

Complete demonstration records are created only when:

```dotenv
APP_ENV=local
```

or:

```dotenv
APP_ENV=testing
```

They are skipped when the application environment is production.

## PSGC location behavior

When PSGC records already exist, the seeder uses the first valid active barangay and its linked locality, province, and region.

When the PSGC tables are empty, it creates one clearly marked fallback demonstration location so the profiles can still be seeded.

## Demonstration files

Synthetic text files are created under the private `clpmis_documents` disk. They are not placed inside `public/`.
