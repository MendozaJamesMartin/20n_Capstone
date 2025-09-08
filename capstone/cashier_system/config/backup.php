<?php
return [

    /*
     * Number of days to keep backups before auto-deletion.
     */
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    /*
     * Enable/disable automatic daily backups.
     */
    'automatic' => env('BACKUP_AUTOMATIC', true),

    /*
     * Time of day (HH:MM in 24h format) to run automatic backups.
     */
    'time' => env('BACKUP_TIME', '00:00'),

    /*
     * Whether backups should be encrypted with a password.
     */
    'encryption_password' => env('BACKUP_ENCRYPTION_PASSWORD', null),
];
