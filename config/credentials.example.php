<?php
/**
 * Credentials Template
 * Copy this file to credentials.php and fill in your actual credentials
 * DO NOT commit credentials.php to version control
 */

return [
    'production' => [
        'ftp' => [
            'host' => 'your-ftp-host',
            'username' => 'your-ftp-username',
            'password' => 'your-ftp-password',
            'port' => 21,
            'ssl' => true
        ],
        'database' => [
            'host' => 'your-db-host',
            'username' => 'your-db-username',
            'password' => 'your-db-password',
            'name' => 'your-db-name'
        ]
    ],
    'development' => [
        // Development environment credentials
    ]
];
