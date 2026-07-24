# CLPMIS Phase 7A — Security Hardening and Access Protection

This ZIP is a direct Laravel project overlay.

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

5. Verify the route and command:

```powershell
php artisan route:list --name=security
php artisan clpmis:security-check
```

6. Open the administrator security page:

```text
http://127.0.0.1:8000/security/status
```

## Included protections

- Active-account enforcement
- Automatic logout after inactivity
- HTTP security headers
- Strong default password requirements
- Last login date and IP tracking
- HTTPS and secure-cookie readiness checks
- Private-document storage verification
- Database and migration readiness checks
- Storage permission checks
- Required PHP extension checks
- Administrator-only system security page
- Command-line security audit
- Security feature tests

## Migration

The included migration adds these optional user security fields:

```text
last_login_at
last_login_ip
password_changed_at
```

## Environment settings

The package includes:

```text
.env.security.example
```

Copy the settings you need into the project's real `.env`. Do not replace the real `.env` file.

Recommended local setting:

```dotenv
CLPMIS_IDLE_TIMEOUT_MINUTES=30
```

Recommended production settings include:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

## Content Security Policy

CSP enforcement is disabled by default:

```dotenv
CLPMIS_CSP_ENABLED=false
```

The existing Vite, Alpine, development-server, inline print-style, and chart requirements should be tested before enabling a strict CSP in production.

## Provider file

This overlay replaces `bootstrap/providers.php` with:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\NotificationServiceProvider::class,
    App\Providers\DashboardServiceProvider::class,
    App\Providers\SecurityServiceProvider::class,
];
```

If another custom provider has been added manually, add it back after extraction.

## Important scope note

This phase hardens the Laravel application layer. Server firewall rules, HTTPS certificates, operating-system patching, database-user permissions, off-site backups, antivirus, and infrastructure monitoring remain deployment responsibilities.
