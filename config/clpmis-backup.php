<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Storage
    |--------------------------------------------------------------------------
    */

    'disk' => env(
        'CLPMIS_BACKUP_DISK',
        'local'
    ),

    'directory' => env(
        'CLPMIS_BACKUP_DIRECTORY',
        'backups'
    ),

    /*
    |--------------------------------------------------------------------------
    | Database Utilities
    |--------------------------------------------------------------------------
    |
    | Leave these as command names when MySQL is available in PATH.
    | XAMPP users may set an absolute path, for example:
    | C:\xampp\mysql\bin\mysqldump.exe
    |
    */

    'mysqldump_path' => env(
        'CLPMIS_MYSQLDUMP_PATH',
        'mysqldump'
    ),

    'mysql_path' => env(
        'CLPMIS_MYSQL_PATH',
        'mysql'
    ),

    /*
    |--------------------------------------------------------------------------
    | Included Private Data
    |--------------------------------------------------------------------------
    |
    | The complete storage/app/private directory is included, except for
    | the backup directory itself. This covers uploaded documents and
    | profile photographs stored on Laravel's private local disk.
    |
    */

    'include_paths' => [
        storage_path('app/private'),
    ],

    'exclude_paths' => [
        storage_path(
            'app/private/'
            .env(
                'CLPMIS_BACKUP_DIRECTORY',
                'backups'
            )
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention and Scheduling
    |--------------------------------------------------------------------------
    */

    'retention_days' => (int) env(
        'CLPMIS_BACKUP_RETENTION_DAYS',
        30
    ),

    'scheduled_backup_enabled' => (bool) env(
        'CLPMIS_SCHEDULED_BACKUP_ENABLED',
        false
    ),

    'scheduled_backup_time' => env(
        'CLPMIS_SCHEDULED_BACKUP_TIME',
        '01:30'
    ),

    'scheduled_cleanup_enabled' => (bool) env(
        'CLPMIS_SCHEDULED_BACKUP_CLEANUP_ENABLED',
        false
    ),
];
