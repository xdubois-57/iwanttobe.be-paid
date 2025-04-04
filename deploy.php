<?php
/**
 * Deployment Script
 * Securely deploys the project to production via FTP
 */

// Load credentials
if (!file_exists(__DIR__ . '/credentials.php')) {
    die("Error: credentials.php not found. Copy credentials.example.php to credentials.php and add your credentials.\n");
}

$credentials = require_once __DIR__ . '/credentials.php';
$ftp = $credentials['production']['ftp'];

// Files to exclude from deployment
$excludePatterns = [
    '.git',
    '.gitignore',
    'credentials.php',
    'credentials.example.php',
    'deploy.php',
    '.env',
    '.env.example',
    'README.md',
    'DESIGN.md',
    'node_modules',
    'vendor'
];

// Connect to FTP
$conn = ftp_connect($ftp['host']) or die("Could not connect to {$ftp['host']}\n");
$login = ftp_login($conn, $ftp['username'], $ftp['password']);

if (!$login) {
    die("FTP login failed\n");
}

echo "Connected to FTP server\n";

// Enable passive mode for better compatibility
ftp_pasv($conn, true);

/**
 * Recursively upload directory
 */
function uploadDirectory($conn, $localPath, $remotePath, $excludePatterns) {
    if (!is_dir($localPath)) {
        return;
    }

    // Create remote directory if it doesn't exist
    @ftp_mkdir($conn, $remotePath);

    $handle = opendir($localPath);
    while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        // Check if file/directory should be excluded
        foreach ($excludePatterns as $pattern) {
            if (strpos($file, $pattern) !== false) {
                echo "Skipping {$file} (excluded)\n";
                continue 2;
            }
        }

        $localFile = $localPath . '/' . $file;
        $remoteFile = $remotePath . '/' . $file;

        if (is_dir($localFile)) {
            uploadDirectory($conn, $localFile, $remoteFile, $excludePatterns);
        } else {
            echo "Uploading {$localFile}... ";
            if (ftp_put($conn, $remoteFile, $localFile, FTP_BINARY)) {
                echo "OK\n";
            } else {
                echo "Failed\n";
            }
        }
    }
    closedir($handle);
}

// Start deployment
echo "Starting deployment...\n";
uploadDirectory($conn, __DIR__, '/', $excludePatterns);

// Close connection
ftp_close($conn);
echo "Deployment completed!\n";