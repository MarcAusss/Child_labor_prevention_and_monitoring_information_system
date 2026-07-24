# CLPMIS Testing Strategy

## Purpose

The CLPMIS test strategy validates the system's main design goals:

- Role-based access
- Data integrity
- Profile workflow correctness
- Protection of sensitive information
- Intervention and audit support
- Reporting availability
- Activity accountability
- Operational readiness

## Test levels

### Unit tests

Unit tests cover isolated notification payloads, security configuration, and other deterministic classes that do not need a database.

### Feature and integration tests

Feature tests boot Laravel, migrate the dedicated test database, create realistic roles and users, exercise HTTP routes, and verify middleware, policies, database relationships, reports, notifications, security headers, backup access, and dashboard scope.

### Smoke tests

Smoke tests open installed module routes and fail whenever a route is missing or returns an HTTP 500-level response.

### Security tests

Security tests cover inactive-account enforcement, secure response headers, private document storage, no-store caching, notification ownership, and role boundaries.

### Data-integrity tests

Schema tests confirm the required core tables and workflow columns are present after all migrations run.

## Dedicated database

`phpunit.clpmis.xml` is configured to use:

```text
CLPMIS_testing
```

The suite uses `RefreshDatabase`, so it must never point to the live or development CLPMIS database.

## Execution

Run only the dedicated CLPMIS suite:

```powershell
vendor\bin\phpunit -c phpunit.clpmis.xml
```

Run the complete QA pipeline:

```powershell
php artisan clpmis:qa
```

Run without rebuilding frontend assets:

```powershell
php artisan clpmis:qa --skip-build
```

## Pass criteria

A candidate release passes Phase 8A when:

1. Every migration completes on the dedicated MySQL test database.
2. Every named module route compiles.
3. The dedicated PHPUnit suite has zero failures, errors, warnings, and risky tests.
4. The CLPMIS security check has no critical failures.
5. `npm run build` completes successfully.
6. The generated QA JSON report has the status `passed`.
