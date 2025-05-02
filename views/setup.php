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
    <title>Setup - Paid!</title>
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
    </style>
</head>
<body>
    <main class="container">
        <article>
            <header>
                <h1>Website Setup Wizard</h1>
                <p>This wizard will guide you through the setup process for the Paid! application.</p>
            </header>

            <?php if (isset($message) && $message): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="wizard-steps">
                <div class="wizard-step <?php echo $step === 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    1. Database Connection
                </div>
                <div class="wizard-step <?php echo $step === 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    2. Database Initialization
                </div>
                <div class="wizard-step <?php echo $step === 3 ? 'active' : ''; ?>">
                    3. Completion
                </div>
            </div>

            <!-- Step 1: Database Connection -->
            <section class="wizard-content <?php echo $step !== 1 ? 'hide' : ''; ?>" id="step1">
                <h2>Database Connection</h2>
                <p>Enter your database connection information to establish a connection to your database server.</p>
                
                <form action="<?php echo $actionUrl; ?>" method="post" id="connection-form">
                    <input type="hidden" name="wizard_step" value="1">
                    
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
                                   value="<?php echo htmlspecialchars($_POST['mysql_password'] ?? ($dbConfig['password'] ?? '')); ?>">
                        </label>
                    </div>
                    
                    <div class="nav-buttons">
                        <div></div> <!-- Placeholder for back button -->
                        <button type="submit" class="primary">Test Connection & Continue</button>
                    </div>
                </form>
                
                <p class="note">
                    <strong>Docker Connection Tips:</strong><br>
                    - If running Docker via Docker Desktop, try <code>host.docker.internal</code><br>
                    - For Docker Compose, use the service name (e.g., <code>mysql</code> or <code>db</code>)<br>
                    - You may need to find the container's IP with <code>docker inspect [container_name]</code>
                </p>
            </section>
            
            <!-- Step 2: Database Initialization -->
            <section class="wizard-content <?php echo $step !== 2 ? 'hide' : ''; ?>" id="step2">
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
                    <input type="hidden" name="wizard_step" value="2">
                    
                    <!-- Pass along all connection data for the next step -->
                    <input type="hidden" name="environment" value="<?php echo htmlspecialchars($dbConfig['environment'] ?? 'development'); ?>">
                    <input type="hidden" name="mysql_host" value="<?php echo htmlspecialchars($dbConfig['host'] ?? 'localhost'); ?>">
                    <input type="hidden" name="mysql_port" value="<?php echo htmlspecialchars($dbConfig['port'] ?? '3306'); ?>">
                    <input type="hidden" name="mysql_name" value="<?php echo htmlspecialchars($dbConfig['name'] ?? 'qrtransfer'); ?>">
                    <input type="hidden" name="mysql_username" value="<?php echo htmlspecialchars($dbConfig['username'] ?? 'root'); ?>">
                    <input type="hidden" name="mysql_password" value="<?php echo htmlspecialchars($dbConfig['password'] ?? ''); ?>">
                    
                    <div class="nav-buttons">
                        <button type="button" onclick="window.location.href='<?php echo $actionUrl; ?>'">Back</button>
                        <button type="submit" class="primary">Initialize Tables</button>
                    </div>
                </form>
            </section>
            
            <!-- Step 3: Completion -->
            <section class="wizard-content <?php echo $step !== 3 ? 'hide' : ''; ?>" id="step3">
                <h2>Setup Complete</h2>
                <p>Congratulations! The Paid! application has been successfully set up.</p>
                
                <div class="setup-summary">
                    <h3>Setup Summary</h3>
                    <ul>
                        <li><strong>Database Connection:</strong> ✓ Successful</li>
                        <li><strong>Database Tables:</strong> ✓ Created</li>
                        <li><strong>Configuration:</strong> ✓ Saved</li>
                    </ul>
                </div>
                
                <div class="nav-buttons">
                    <button type="button" onclick="window.location.href='/'">Go to Homepage</button>
                </div>
            </section>
            
            <footer>
                <p class="note">This page is for administrative purposes only and should not be exposed in production.</p>
            </footer>
        </article>
    </main>
</body>
</html>
