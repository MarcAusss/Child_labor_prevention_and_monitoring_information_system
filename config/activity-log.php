<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity Logging
    |--------------------------------------------------------------------------
    */

    'enabled' => env(
        'ACTIVITY_LOG_ENABLED',
        true
    ),

    /*
     * Console operations such as seeders and Artisan commands
     * are not logged by default.
     */
    'log_console' => env(
        'ACTIVITY_LOG_CONSOLE',
        false
    ),

    /*
     * These values are replaced with "[REDACTED]" whenever
     * they appear in old values, new values, or metadata.
     */
    'redacted_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'remember_token',
        'token',
        'api_token',
        'secret',
        'duplicate_key',
        'checksum_sha256',
        'file_path',
        'stored_name',
    ],

    /*
     * Fields ignored by the automatic model observer.
     */
    'ignored_model_fields' => [
        'created_at',
        'updated_at',
        'deleted_at',
        'duplicate_key',
        'checksum_sha256',
        'download_count',
        'last_downloaded_at',
    ],

    /*
     * Prevent extremely large text values from making
     * activity-log rows unnecessarily large.
     */
    'maximum_string_length' => 5000,
];