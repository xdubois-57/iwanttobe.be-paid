<?php
/**
 * Setup View
 * 
 * This view provides a UI for various setup operations
 */

// Prevent direct access
if (!defined('QR_TRANSFER')) {
    http_response_code(403);
    exit('Direct access not permitted');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - iwantto.be</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        .wizard-steps {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid #ddd;
            padding-bottom: 1rem;
        }
        .wizard-step {
            flex: 1;
            text-align: center;
            padding: 1rem;
            position: relative;
        }
        .wizard-step.active {
            font-weight: bold;
            color: var(--primary);
        }
        .wizard-step.completed {
            color: var(--primary);
        }
        .wizard-step.completed::after {
            content: "✓";
            margin-left: 0.5rem;
        }
        .wizard-content {
            min-height: 300px;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        .hide {
            display: none;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .wizard-content .secondary {
            min-width: 160px;
            text-align: center;
        }
    </style>
</head>
<body>
    <main class="container">
        <article>
            <header>
                <h1>Website Setup Wizard</h1>
                <p>This wizard will guide you through the setup process for the iwantto.be application.</p>
            </header>
            <hr>
            <div class="wizard-steps">
                <div class="wizard-step <?php echo $step === 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    1. Website configuration info
                </div>
                <div class="wizard-step <?php echo $step === 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    2. Database Connection
                </div>
                <div class="wizard-step <?php echo $step === 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                    3. Database Initialization
                </div>
                <div class="wizard-step <?php echo $step === 4 ? 'active' : ''; ?>">
                    4. Completion
                </div>
            </div>
            <hr>
            <!-- Global Connectivity Status Header (all steps except step 3 when there's a message) -->
            <?php if (!($step === 3 && isset($message) && $message)): ?>
            <div class="alert <?php echo ($databaseExists && $databaseValid) ? 'alert-success' : 'alert-error'; ?>" style="margin-bottom:2rem;">
                <?php if ($databaseExists && $databaseValid): ?>
                    Database connectivity: <strong>Connected</strong> (all required tables found)
                <?php else: ?>
                    Database connectivity: <strong>Not connected or incomplete</strong>
                    <?php if (!empty($connectivityError)): ?>
                        <br><small>Error: <?php echo htmlspecialchars($connectivityError); ?></small>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($message) && $message): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Step 1: Website configuration info and DB summary -->
            <section class="wizard-content <?php echo $step !== 1 ? 'hide' : ''; ?>" id="step1">
                <h2>Website configuration info</h2>
                <div class="setup-summary">
                    <h3>Database Configuration</h3>
                    <ul>
                        <li>Host: <strong><?php echo htmlspecialchars($dbConfig['host'] ?? ''); ?></strong></li>
                        <li>Name: <strong><?php echo htmlspecialchars($dbConfig['name'] ?? ''); ?></strong></li>
                        <li>User: <strong><?php echo htmlspecialchars($dbConfig['username'] ?? ''); ?></strong></li>
                        <li>Port: <strong><?php echo htmlspecialchars($dbConfig['port'] ?? '3306'); ?></strong></li>
                    </ul>
                    <div id="db-tables-summary">
                        <!-- Table summary will be injected here -->
                        <?php if (isset($tableSummaryHtml)) echo $tableSummaryHtml; ?>
                    </div>
                </div>
                <form method="post" action="<?php echo htmlspecialchars($actionUrl); ?>" style="display: flex; gap: 1rem; align-items: center;">
                    <input type="hidden" name="wizard_step" value="1">
                    <input type="hidden" name="delete_confirmation" value="yes">
                    <button type="submit" class="primary" style="min-width: 160px;">Proceed with configuration</button>
                    <button type="submit" name="skip" value="1" class="secondary">Skip</button>
                </form>
            </section>

            <!-- Step 2: Database Connection -->
            <section class="wizard-content <?php echo $step !== 2 ? 'hide' : ''; ?>" id="step2">
                <h2>Database Connection</h2>
                <p>Enter your database connection information to establish a connection to your database server.</p>
                
                <form action="<?php echo $actionUrl; ?>" method="post" id="connection-form">
                    <input type="hidden" name="wizard_step" value="2">
                    
                    <div class="grid">
                        <label for="environment">
                            Environment
                            <select id="environment" name="environment" required>
                                <option value="development" <?php echo (isset($_POST['environment']) && $_POST['environment'] === 'development') ? 'selected' : ''; ?>>Development</option>
                                <option value="production" <?php echo (isset($_POST['environment']) && $_POST['environment'] === 'production') ? 'selected' : ''; ?>>Production</option>
                            </select>
                        </label>
                    </div>
                    
                    <div class="grid">
                        <label for="mysql_host">
                            MySQL Host
                            <input type="text" id="mysql_host" name="mysql_host" placeholder="Enter MySQL host" 
                                   value="<?php echo htmlspecialchars($_POST['mysql_host'] ?? ($dbConfig['host'] ?? 'localhost')); ?>" required>
                        </label>
                        
                        <label for="mysql_port">
                            MySQL Port
                            <input type="text" id="mysql_port" name="mysql_port" placeholder="Enter MySQL port" 
                                   value="<?php echo htmlspecialchars($_POST['mysql_port'] ?? ($dbConfig['port'] ?? '3306')); ?>" required>
                        </label>
                    </div>
                    
                    <div class="grid">
                        <label for="mysql_name">
                            Database Name
                            <input type="text" id="mysql_name" name="mysql_name" placeholder="Enter database name" 
                                   value="<?php echo htmlspecialchars($_POST['mysql_name'] ?? ($dbConfig['name'] ?? 'qrtransfer')); ?>" required>
                        </label>
                    </div>
                    
                    <div class="grid">
                        <label for="mysql_username">
                            Username
                            <input type="text" id="mysql_username" name="mysql_username" placeholder="Enter database username" 
                                   value="<?php echo htmlspecialchars($_POST['mysql_username'] ?? ($dbConfig['username'] ?? 'root')); ?>" required>
                        </label>
                        
                        <label for="mysql_password">
                            Password
                            <input type="password" id="mysql_password" name="mysql_password" placeholder="Enter database password" 
                                   value="<?php echo htmlspecialchars($dbConfig['password'] ?? ''); ?>">
                        </label>
                    </div>
                    
                    <div class="nav-buttons" style="display: flex; gap: 1rem; align-items: center;">
                        <button type="submit" name="previous" value="1" class="secondary outline">Previous</button>
                        <button type="submit" class="primary">Test Connection &amp; Save</button>
                        <button type="submit" name="skip" value="1" class="secondary">Skip</button>
                    </div>
                </form>
                
                <p class="note">
                    <strong>Docker Connection Tips:</strong><br>
                    - If running Docker via Docker Desktop, try <code>host.docker.internal</code><br>
                    - For Docker Compose, use the service name (e.g., <code>mysql</code> or <code>db</code>)<br>
                    - You may need to find the container's IP with <code>docker inspect [container_name]</code>
                </p>
            </section>
            
            <!-- Step 3: Database Initialization -->
            <section class="wizard-content <?php echo $step !== 3 ? 'hide' : ''; ?>" id="step3">
                <h2>Database Initialization</h2>
                <p>Now that we have a successful database connection, we can create the necessary database tables.</p>
                
                <div class="connection-summary">
                    <h3>Connection Summary</h3>
                    <ul>
                        <li><strong>Environment:</strong> <?php echo htmlspecialchars($dbConfig['environment'] ?? 'development'); ?></li>
                        <li><strong>Host:</strong> <?php echo htmlspecialchars($dbConfig['host'] ?? 'localhost'); ?></li>
                        <li><strong>Database:</strong> <?php echo htmlspecialchars($dbConfig['name'] ?? 'qrtransfer'); ?></li>
                    </ul>
                </div>
                
                <form action="<?php echo $actionUrl; ?>" method="post" id="initialization-form">
                    <input type="hidden" name="wizard_step" value="3">
                    
                    <!-- Pass along all connection data for the next step -->
                    <input type="hidden" name="environment" value="<?php echo htmlspecialchars($dbConfig['environment'] ?? 'development'); ?>">
                    <input type="hidden" name="mysql_host" value="<?php echo htmlspecialchars($dbConfig['host'] ?? 'localhost'); ?>">
                    <input type="hidden" name="mysql_port" value="<?php echo htmlspecialchars($dbConfig['port'] ?? '3306'); ?>">
                    <input type="hidden" name="mysql_name" value="<?php echo htmlspecialchars($dbConfig['name'] ?? 'qrtransfer'); ?>">
                    <input type="hidden" name="mysql_username" value="<?php echo htmlspecialchars($dbConfig['username'] ?? 'root'); ?>">
                    <input type="hidden" name="mysql_password" value="<?php echo htmlspecialchars($dbConfig['password'] ?? ''); ?>">
                    
                    <div class="nav-buttons" style="display: flex; gap: 1rem; align-items: center;">
                        <button type="submit" name="previous" value="1" class="secondary outline">Previous</button>
                        <button type="submit" class="primary">Initialize Database</button>
                        <button type="submit" name="skip" value="1" class="secondary">Skip</button>
                    </div>
                </form>
            </section>
            
            <!-- Step 4: Completion -->
            <section class="wizard-content <?php echo $step !== 4 ? 'hide' : ''; ?>" id="step4">
                <h2>Setup Complete</h2>
                <?php if (!empty($skipped)): ?>
                    <p class="alert alert-error">Setup was skipped. The database may not be initialized or may be incomplete.</p>
                <?php else: ?>
                    <p>Your database is ready and the setup is complete.</p>
                <?php endif; ?>
                <div class="setup-summary">
                    <h3>Setup Summary</h3>
                    <ul>
                        <li>Database Host: <strong><?php echo htmlspecialchars($dbConfig['host'] ?? ''); ?></strong></li>
                        <li>Database Name: <strong><?php echo htmlspecialchars($dbConfig['name'] ?? ''); ?></strong></li>
                        <li>Database User: <strong><?php echo htmlspecialchars($dbConfig['username'] ?? ''); ?></strong></li>
                        <li>Status: <strong><?php echo !empty($skipped) ? 'Skipped' : 'Initialized'; ?></strong></li>
                    </ul>
                </div>
                <div class="nav-buttons" style="gap: 1rem;">
                    <a href="/" class="primary">Go to Application</a>
                </div>
            </section>
            
            <footer>
                <p class="note">This page is for administrative purposes only and should not be exposed in production.</p>
            </footer>
        </article>
    </main>
</body>
</html>
