<?php
/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Setup Controller
 *
 * Handles website setup operations including database initialization
 */
class SetupController {
    /**
     * Show the main setup page
     */
    public function index($params) {
        // Default to step 1
        $step = 1;
        $success = false;
        $message = '';
        $dbConfig = [];
        $actionUrl = '/setup/db';
        
        // Default values from credentials file if it exists
        $credentialsFile = __DIR__ . '/../config/credentials.php';
        if (file_exists($credentialsFile)) {
            $credentials = require $credentialsFile;
            $environment = 'development';
            if (isset($credentials[$environment]['database'])) {
                $dbConfig = $credentials[$environment]['database'];
                $dbConfig['environment'] = $environment;
            }
        }
        
        // Pass variables to view
        require_once __DIR__ . '/../views/setup.php';
    }
    
    /**
     * Handle database operation steps
     */
    public function initializeDatabase($params) {
        // Default values
        $success = false;
        $message = '';
        $actionUrl = '/setup/db';
        $credentialsFile = __DIR__ . '/../config/credentials.php';
        
        // Determine current step
        $step = isset($_POST['wizard_step']) ? (int)$_POST['wizard_step'] : 1;
        
        // Get existing credentials if available
        if (file_exists($credentialsFile)) {
            $credentials = require $credentialsFile;
        } else {
            $credentials = [
                'production' => [
                    'database' => []
                ],
                'development' => [
                    'database' => []
                ]
            ];
        }
        
        // Get environment from form or use default
        $environment = isset($_POST['environment']) ? $_POST['environment'] : 'development';
        
        // Create dbConfig from POST data
        $dbConfig = [
            'environment' => $environment,
            'host' => $_POST['mysql_host'] ?? 'localhost',
            'port' => $_POST['mysql_port'] ?? '3306',
            'name' => $_POST['mysql_name'] ?? 'qrtransfer',
            'username' => $_POST['mysql_username'] ?? 'root',
            'password' => $_POST['mysql_password'] ?? ''
        ];
        
        // Process based on wizard step
        if ($step === 1) {
            // Step 1: Test database connection and save credentials
            try {
                // Try to connect to MySQL server (without selecting a database)
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
                
                $pdo = new PDO(
                    $dsn, 
                    $dbConfig['username'], 
                    $dbConfig['password'], 
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Connection successful, save to credentials file
                if (!isset($credentials[$environment])) {
                    $credentials[$environment] = [];
                }
                
                // Save database config
                $credentials[$environment]['database'] = [
                    'host' => $dbConfig['host'],
                    'port' => $dbConfig['port'],
                    'name' => $dbConfig['name'],
                    'username' => $dbConfig['username'],
                    'password' => $dbConfig['password']
                ];
                
                // Generate PHP code
                $phpCode = "<?php\n/**\n * Credentials Configuration\n * Auto-generated on " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($credentials, true) . ";\n";
                
                // Write to file
                if (file_put_contents($credentialsFile, $phpCode)) {
                    $success = true;
                    $message = "Successfully connected to MySQL server at {$dbConfig['host']}:{$dbConfig['port']}. Credentials saved.";
                    $step = 2; // Move to next step
                } else {
                    $success = false;
                    $message = "Connection successful, but could not save credentials to file. Check file permissions.";
                }
                
            } catch (PDOException $e) {
                $success = false;
                $message = 'Database connection failed: ' . $e->getMessage();
                $step = 1; // Stay on current step
            }
        } elseif ($step === 2) {
            // Step 2: Initialize database
            try {
                // Connect to the server
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
                
                $pdo = new PDO(
                    $dsn, 
                    $dbConfig['username'], 
                    $dbConfig['password'], 
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Create database if not exists
                $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $dbConfig['name'] . '` 
                           CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                
                // Switch to the database
                $pdo->exec('USE `' . $dbConfig['name'] . '`');
                
                // Read SQL file
                $sql = file_get_contents(__DIR__ . '/../sql/init_db.sql');
                
                // Execute SQL
                $pdo->exec($sql);
                
                $success = true;
                $message = "Database '{$dbConfig['name']}' initialized successfully with all required tables!";
                $step = 3; // Move to completion step
                
            } catch (PDOException $e) {
                $success = false;
                $message = 'Database initialization failed: ' . $e->getMessage();
                $step = 2; // Stay on current step
            }
        }
        
        // Pass variables to view
        require_once __DIR__ . '/../views/setup.php';
    }
}
