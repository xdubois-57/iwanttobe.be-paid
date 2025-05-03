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

require_once __DIR__ . '/../core/ConfigManager.php';

/**
 * Setup Controller
 *
 * Handles website setup operations including database initialization
 */
class SetupController {
    /**
     * Show the main setup page
     */
    public function index() {
        // Check if website is already initialized
        $configManager = ConfigManager::getInstance();
        if ($configManager->isInitialised()) {
            // Redirect to homepage if setup is already completed
            header('Location: /');
            exit;
        }
        
        $credentialsFile = __DIR__ . '/../config/credentials.php';
        $actionUrl = '/setup/db';
        $step = 1;
        $message = '';
        $success = false;
        $skipped = false;
        $databaseExists = false;
        $databaseValid = false;
        $connectivityError = '';
        
        // Initialize with default values
        $dbConfig = [
            'environment' => 'development',
            'host' => '127.0.0.1',
            'port' => '3306',
            'name' => 'qrtransfer',
            'username' => 'root',
            'password' => ''
        ];
        
        // Process the request based on the wizard step and method
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle POST request (form submission)
            if (isset($_POST['wizard_step'])) {
                $step = (int)$_POST['wizard_step'];
                
                // If we're returning from a later step (via Previous button)
                if (isset($_POST['previous']) && $_POST['previous'] === '1') {
                    // Go back one step
                    $step = max(1, $step - 1);
                } 
                // Check if moving forward from step 1
                else if ($step === 1 && isset($_POST['delete_confirmation']) && $_POST['delete_confirmation'] === 'yes') {
                    // Progress to step 2 when "Proceed with configuration" is clicked on step 1
                    $step = 2;
                }
                // Handle skipping
                else if (isset($_POST['skip']) && $_POST['skip'] === '1') {
                    $skipped = true;
                    $step++;
                }
                // Handle Go to Application button from step 4
                else if ($step === 4 && isset($_POST['go_to_app']) && $_POST['go_to_app'] === '1') {
                    // Redirect to the home page
                    header('Location: /');
                    exit;
                }
            }
        }
        
        // Handle form data if submitted
        if (isset($_POST['mysql_host'])) {
            $dbConfig = [
                'environment' => $_POST['environment'] ?? 'development',
                'host' => $_POST['mysql_host'] ?? '127.0.0.1',
                'port' => $_POST['mysql_port'] ?? '3306',
                'name' => $_POST['mysql_name'] ?? 'qrtransfer',
                'username' => $_POST['mysql_username'] ?? 'root',
                'password' => $_POST['mysql_password'] ?? ''
            ];
        }
        
        // Always load credentials from file if it exists (after form data to prioritize file on step 1)
        if (file_exists($credentialsFile)) {
            $credentials = require $credentialsFile;
            $environment = $dbConfig['environment'] ?? 'development';
            if (isset($credentials[$environment]['database'])) {
                // On step 1, always use file data; on other steps, merge with form data
                if ($step === 1) {
                    $dbConfig = $credentials[$environment]['database'];
                    $dbConfig['environment'] = $environment;
                } else {
                    // For passwords, only use from file if not already set in POST
                    if (empty($dbConfig['password']) && isset($credentials[$environment]['database']['password'])) {
                        $dbConfig['password'] = $credentials[$environment]['database']['password'];
                    }
                }
            }
        }
        
        // Try to connect and check if database exists - check EARLY, before any form processing
        try {
            // Check if database exists (connect without dbname)
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
            $pdo = new PDO(
                $dsn, 
                $dbConfig['username'], 
                $dbConfig['password'], 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$dbConfig['name']]);
            $databaseExists = $stmt->fetch() !== false;
            $pdo = null;
            
            // If database exists, check for all required tables
            if ($databaseExists) {
                $dsnWithDb = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
                $pdo = new PDO(
                    $dsnWithDb,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Check all tables from SQL file
                $allTablesExist = true;
                $sqlFile = __DIR__ . '/../sql/init_db.sql';
                $tableNames = [];
                
                if (file_exists($sqlFile)) {
                    $sqlContent = file_get_contents($sqlFile);
                    if (preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`/i', $sqlContent, $matches)) {
                        $tableNames = $matches[1];
                    }
                }
                
                foreach ($tableNames as $table) {
                    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                    $stmt->execute([$table]);
                    if ($stmt->fetch() === false) {
                        $allTablesExist = false;
                        break;
                    }
                }
                
                $databaseValid = $allTablesExist;
                
                // Generate table summary HTML
                $tableSummaryHtml = '<h3>Database Table Status</h3><ul>';
                foreach ($tableNames as $table) {
                    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                    $stmt->execute([$table]);
                    $exists = $stmt->fetch() !== false;
                    $tableSummaryHtml .= '<li>' . htmlspecialchars($table) . ': <strong style="color:' . 
                        ($exists ? 'green' : 'red') . ';">' . 
                        ($exists ? 'Exists' : 'Missing') . '</strong></li>';
                }
                $tableSummaryHtml .= '</ul>';
                
                $pdo = null;
            }
        } catch (PDOException $e) {
            $connectivityError = $e->getMessage();
            $tableSummaryHtml = '';
        }
        
        // Determine which step to show
        if ($databaseExists && $databaseValid) {
            // Database exists and is valid, show confirmation page
            $step = 1;
            $message = '';
        } else {
            // Either no database or invalid structure, proceed with setup
            $step = 1;
        }
        
        // Always reload and check credentials for connectivity status on every step
        if (isset($dbConfig)) {
            try {
                // Check if database exists (connect without dbname)
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
                $pdo = new PDO(
                    $dsn,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                $stmt->execute([$dbConfig['name']]);
                $databaseExists = $stmt->fetch() !== false;
                $pdo = null;
                if ($databaseExists) {
                    // Now connect with dbname to check all required tables from SQL file
                    $dsnWithDb = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
                    $pdo = new PDO(
                        $dsnWithDb,
                        $dbConfig['username'],
                        $dbConfig['password'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    // Check all tables from SQL file
                    $allTablesExist = true;
                    $sqlFile = __DIR__ . '/../sql/init_db.sql';
                    $tableNames = [];
                    
                    if (file_exists($sqlFile)) {
                        $sqlContent = file_get_contents($sqlFile);
                        if (preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`/i', $sqlContent, $matches)) {
                            $tableNames = $matches[1];
                        }
                    }
                    
                    foreach ($tableNames as $table) {
                        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                        $stmt->execute([$table]);
                        if ($stmt->fetch() === false) {
                            $allTablesExist = false;
                            break;
                        }
                    }
                    
                    $databaseValid = $allTablesExist;
                    
                    // Generate table summary HTML
                    $tableSummaryHtml = '<h3>Database Table Status</h3><ul>';
                    foreach ($tableNames as $table) {
                        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                        $stmt->execute([$table]);
                        $exists = $stmt->fetch() !== false;
                        $tableSummaryHtml .= '<li>' . htmlspecialchars($table) . ': <strong style="color:' . 
                            ($exists ? 'green' : 'red') . ';">' . 
                            ($exists ? 'Exists' : 'Missing') . '</strong></li>';
                    }
                    $tableSummaryHtml .= '</ul>';
                    
                    $pdo = null;
                }
            } catch (PDOException $e) {
                $connectivityError = $e->getMessage();
            }
        }

        // Process the request based on the wizard step and method
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle POST request (form submission)
            if (isset($_POST['wizard_step'])) {
                $step = (int)$_POST['wizard_step'];
                
                // If we're returning from a later step (via Previous button)
                if (isset($_POST['previous']) && $_POST['previous'] === '1') {
                    // Go back one step
                    $step = max(1, $step - 1);
                } 
                // Check if moving forward from step 1
                else if ($step === 1 && isset($_POST['delete_confirmation']) && $_POST['delete_confirmation'] === 'yes') {
                    // Progress to step 2 when "Proceed with configuration" is clicked on step 1
                    $step = 2;
                }
                // Handle skipping
                else if (isset($_POST['skip']) && $_POST['skip'] === '1') {
                    $skipped = true;
                    $step++;
                }
                // Handle Go to Application button from step 4
                else if ($step === 4 && isset($_POST['go_to_app']) && $_POST['go_to_app'] === '1') {
                    // Redirect to the home page
                    header('Location: /');
                    exit;
                }
            }
        }
        
        // Ensure database summary and connectivity check is performed on all steps
        $tableSummaryHtml = '';
        // Generate table summary for display
        if ($databaseExists) {
            try {
                $dsnWithDb = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
                $pdo = new PDO(
                    $dsnWithDb,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $sqlFile = __DIR__ . '/../sql/init_db.sql';
                $tableNames = [];
                if (file_exists($sqlFile)) {
                    $sqlContent = file_get_contents($sqlFile);
                    if (preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`/i', $sqlContent, $matches)) {
                        $tableNames = $matches[1];
                        
                        $tableSummaryHtml = '<h3>Database Table Status</h3><ul>';
                        foreach ($tableNames as $table) {
                            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                            $stmt->execute([$table]);
                            $exists = $stmt->fetch() !== false;
                            $tableSummaryHtml .= '<li>' . htmlspecialchars($table) . ': <strong style="color:' . 
                                ($exists ? 'green' : 'red') . ';">' . 
                                ($exists ? 'Exists' : 'Missing') . '</strong></li>';
                        }
                        $tableSummaryHtml .= '</ul>';
                    }
                }
                $pdo = null;
            } catch (PDOException $e) {
                $connectivityError = $e->getMessage();
            }
        }
        
        // Ensure these variables are always defined for the view
        if (!isset($databaseExists)) $databaseExists = false;
        if (!isset($databaseValid)) $databaseValid = false;
        if (!isset($connectivityError)) $connectivityError = '';

        // Pass variables to view
        require_once __DIR__ . '/../views/setup.php';
    }
    
    // Main method for the setup process
    public function initializeDatabase() {
        $credentialsFile = __DIR__ . '/../config/credentials.php';
        $step = 1;
        $message = '';
        $success = false;
        $skipped = false;
        $connectivityError = '';
        
        // Initialize with default values
        $dbConfig = [
            'environment' => 'development',
            'host' => '127.0.0.1',
            'port' => '3306',
            'name' => 'qrtransfer',
            'username' => 'root',
            'password' => ''
        ];
        
        // Process the request based on the wizard step and method
        if (isset($_POST['wizard_step'])) {
            $step = (int)$_POST['wizard_step'];
            
            // Previous button handling
            if (isset($_POST['previous']) && $_POST['previous'] === '1') {
                // Go back one step and render
                $step = max(1, $step - 1);
            }
            // Skip button handling
            else if (isset($_POST['skip']) && $_POST['skip'] === '1') {
                // Skip to next step and mark as skipped
                $skipped = true;
                $step++;
            }
            // Handle step 1 to step 2 progression (delete_confirmation)
            else if ($step === 1 && isset($_POST['delete_confirmation']) && $_POST['delete_confirmation'] === 'yes') {
                $step = 2;
            }
            // Normal form submission for database connection in step 2
            else if ($step === 2 && isset($_POST['mysql_host'])) {
                // Form submission with database details
                $environment = $_POST['environment'] ?? 'development';
                
                $dbConfig = [
                    'environment' => $environment,
                    'host' => $_POST['mysql_host'] ?? '127.0.0.1',
                    'port' => $_POST['mysql_port'] ?? '3306',
                    'name' => $_POST['mysql_name'] ?? 'qrtransfer',
                    'username' => $_POST['mysql_username'] ?? 'root',
                    'password' => $_POST['mysql_password'] ?? ''
                ];
                
                try {
                    // Try to connect to MySQL server
                    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
                    $pdo = new PDO(
                        $dsn, 
                        $dbConfig['username'], 
                        $dbConfig['password'], 
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    // Load or create credentials structure
                    if (file_exists($credentialsFile)) {
                        $credentials = require $credentialsFile;
                    } else {
                        $credentials = [
                            'production' => ['database' => []],
                            'development' => ['database' => []]
                        ];
                    }
                    
                    // Save database config
                    if (!isset($credentials[$environment])) {
                        $credentials[$environment] = [];
                    }
                    $credentials[$environment]['database'] = [
                        'host' => $dbConfig['host'],
                        'port' => $dbConfig['port'],
                        'name' => $dbConfig['name'],
                        'username' => $dbConfig['username'],
                        'password' => $dbConfig['password']
                    ];
                    
                    // Generate and save PHP code
                    $phpCode = "<?php\n/**\n * Credentials Configuration\n * Auto-generated on " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($credentials, true) . ";\n";
                    
                    if (file_put_contents($credentialsFile, $phpCode)) {
                        $success = true;
                        $message = "Successfully connected to MySQL server at {$dbConfig['host']}:{$dbConfig['port']}. Credentials saved.";
                        $step = 3; // Move to next step
                    } else {
                        $success = false;
                        $message = "Connection successful, but could not save credentials to file. Check file permissions.";
                    }
                } catch (PDOException $e) {
                    $connectivityError = $e->getMessage();
                    $success = false;
                    $message = 'Database connection failed: ' . $e->getMessage();
                }
            }
            // Database initialization in step 3
            else if ($step === 3) {
                try {
                    // Get credentials from file
                    if (file_exists($credentialsFile)) {
                        $credentials = require $credentialsFile;
                        $environment = $_POST['environment'] ?? 'development';
                        if (isset($credentials[$environment]['database'])) {
                            $dbConfig = $credentials[$environment]['database'];
                            $dbConfig['environment'] = $environment;
                        }
                    }
                    
                    // Try to connect to MySQL server
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
                    $step = 4; // Move to completion step
                    
                    // Mark website as initialized
                    $configManager = ConfigManager::getInstance();
                    $configManager->markAsInitialised();
                } catch (PDOException $e) {
                    $connectivityError = $e->getMessage();
                    $success = false;
                    $message = 'Database initialization failed: ' . $e->getMessage();
                }
            }
        }
        
        // Always load credentials from file if it exists (for displaying on any step)
        if (file_exists($credentialsFile)) {
            $credentials = require $credentialsFile;
            $environment = $dbConfig['environment'] ?? 'development';
            if (isset($credentials[$environment]['database'])) {
                // On step 1, always use file data; on other steps, merge with form data
                if ($step === 1) {
                    $dbConfig = $credentials[$environment]['database'];
                    $dbConfig['environment'] = $environment;
                } else {
                    // For passwords, only use from file if not already set in POST
                    if (empty($dbConfig['password']) && isset($credentials[$environment]['database']['password'])) {
                        $dbConfig['password'] = $credentials[$environment]['database']['password'];
                    }
                }
            }
        }
        
        // Check database connectivity status
        $databaseExists = false;
        $databaseValid = false;
        if (isset($dbConfig) && !empty($dbConfig['host'])) {
            try {
                // Check if database exists
                $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
                $pdo = new PDO(
                    $dsn, 
                    $dbConfig['username'], 
                    $dbConfig['password'], 
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
                $stmt->execute([$dbConfig['name']]);
                $databaseExists = $stmt->fetch() !== false;
                $pdo = null;
                
                // If database exists, check all required tables
                if ($databaseExists) {
                    $dsnWithDb = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']};charset=utf8mb4";
                    $pdo = new PDO(
                        $dsnWithDb,
                        $dbConfig['username'],
                        $dbConfig['password'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    $allTablesExist = true;
                    $sqlFile = __DIR__ . '/../sql/init_db.sql';
                    $tableNames = [];
                    
                    if (file_exists($sqlFile)) {
                        $sqlContent = file_get_contents($sqlFile);
                        if (preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`/i', $sqlContent, $matches)) {
                            $tableNames = $matches[1];
                        }
                    }
                    
                    foreach ($tableNames as $table) {
                        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                        $stmt->execute([$table]);
                        if ($stmt->fetch() === false) {
                            $allTablesExist = false;
                            break;
                        }
                    }
                    
                    $databaseValid = $allTablesExist;
                    
                    // Generate table summary HTML
                    $tableSummaryHtml = '<h3>Database Table Status</h3><ul>';
                    foreach ($tableNames as $table) {
                        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                        $stmt->execute([$table]);
                        $exists = $stmt->fetch() !== false;
                        $tableSummaryHtml .= '<li>' . htmlspecialchars($table) . ': <strong style="color:' . 
                            ($exists ? 'green' : 'red') . ';">' . 
                            ($exists ? 'Exists' : 'Missing') . '</strong></li>';
                    }
                    $tableSummaryHtml .= '</ul>';
                    
                    $pdo = null;
                }
            } catch (PDOException $e) {
                $connectivityError = $e->getMessage();
                $tableSummaryHtml = '';
            }
        }
        
        // Set action URL for form submission
        $actionUrl = '/setup/db';
        
        // Render view with all the data
        require_once __DIR__ . '/../views/setup.php';
    }
}
