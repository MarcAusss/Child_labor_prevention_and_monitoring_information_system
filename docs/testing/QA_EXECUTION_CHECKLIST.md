# CLPMIS QA Execution Checklist

## Preparation

- [ ] Commit or back up the project.
- [ ] Confirm the development database is not named `CLPMIS_testing`.
- [ ] Create the dedicated `CLPMIS_testing` database.
- [ ] Confirm `phpunit.clpmis.xml` points only to the test database.
- [ ] Run `composer install`.
- [ ] Run `npm install`.
- [ ] Run `php artisan optimize:clear`.

## Automated checks

- [ ] Run `php artisan clpmis:qa`.
- [ ] Confirm migration status passed.
- [ ] Confirm route compilation passed.
- [ ] Confirm the PHPUnit suite passed.
- [ ] Confirm the security audit passed.
- [ ] Confirm the production frontend build passed.
- [ ] Open `/quality-assurance`.
- [ ] Confirm the latest report displays PASSED.

## Role verification

- [ ] Super Admin can open administration modules.
- [ ] Admin can open administration modules.
- [ ] Profiling Officer is restricted from reports and administration.
- [ ] Viewer is restricted from user management, audits, security, backups, and QA.
- [ ] Viewer can access permitted read-only reports.
- [ ] Inactive accounts are logged out.

## Data protection

- [ ] Private documents are outside `public/`.
- [ ] Another user cannot open a private notification.
- [ ] Authenticated HTML responses include no-store caching.
- [ ] Security headers are present.
- [ ] Backup downloads require Admin or Super Admin.

## Release decision

- [ ] All automated steps passed.
- [ ] No unresolved critical defect remains.
- [ ] Test evidence has been saved.
- [ ] Database and private files have a verified backup.
- [ ] The release is ready for Phase 8B deployment preparation.
