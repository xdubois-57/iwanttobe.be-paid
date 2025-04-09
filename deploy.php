<?php
/**
 * Deployment Script
 * Securely deploys the project to production via FTP
 */

// Load credentials
if (!file_exists(__DIR__ . '/config/credentials.php')) {
    die("Error: credentials.php not found. Copy credentials.example.php to credentials.php and add your credentials.\n");
}

$credentials = require_once __DIR__ . '/config/credentials.php';
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
function uploadDirectory($conn, $localPath, $remotePath, $excludePatterns, $ftp) {
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
            uploadDirectory($conn, $localFile, $remoteFile, $excludePatterns, $ftp);
        } else {
            echo "Uploading {$localFile}... ";
            
            // Special handling for font files
            if (pathinfo($localFile, PATHINFO_EXTENSION) === 'ttf') {
                // Use cURL for font files
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "ftp://{$ftp['host']}{$remoteFile}");
                curl_setopt($ch, CURLOPT_UPLOAD, 1);
                curl_setopt($ch, CURLOPT_USERPWD, "{$ftp['username']}:{$ftp['password']}");
                curl_setopt($ch, CURLOPT_INFILE, fopen($localFile, 'rb'));
                curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFile));
                curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
                curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $result = curl_exec($ch);
                curl_close($ch);
                
                if ($result) {
                    echo "OK (via cURL)\n";
                } else {
                    echo "Failed (via cURL)\n";
                }
            } else {
                // Use regular FTP for other files
                if (ftp_put($conn, $remoteFile, $localFile, FTP_ASCII)) {
                    echo "OK\n";
                } else {
                    echo "Failed\n";
                }
            }
        }
    }
    closedir($handle);
}

// Start deployment
echo "Starting deployment...\n";
uploadDirectory($conn, __DIR__, '/', $excludePatterns, $ftp);

// Close connection
ftp_close($conn);
echo "Deployment completed!\n";