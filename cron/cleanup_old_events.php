<?php
/**
 * GDPR Compliance - Automatic Event Cleanup
 * 
 * This script deletes events older than one month to comply with GDPR requirements.
 * It should be run regularly via a cron job (e.g., daily or weekly).
 * 
 * Example cron entry (daily at 3:15 AM):
 * 15 3 * * * php /path/to/qrtransfer/cron/cleanup_old_events.php
 * 
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 */

// Define application constant to prevent direct access to files
define('QR_TRANSFER', true);

// Set error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Enable logging to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/gdpr_cleanup.log');

// Record execution start
$startTime = microtime(true);
error_log('GDPR Cleanup: Starting automatic event cleanup process');

// Load required files
require_once __DIR__ . '/../apps/involved/models/EventModel.php';

try {
    // Initialize the event model
    $eventModel = new EventModel();
    
    // Delete events older than 1 month (GDPR compliance)
    $result = $eventModel->deleteOldEvents(1);
    
    // Log the results
    if ($result['success']) {
        $executionTime = round(microtime(true) - $startTime, 2);
        error_log("GDPR Cleanup: Successfully deleted {$result['deleted_count']} events older than {$result['cutoff_date']}. Execution time: {$executionTime}s");
        
        // Output result in JSON format
        echo json_encode([
            'status' => 'success',
            'deleted_count' => $result['deleted_count'],
            'execution_time' => $executionTime,
            'cutoff_date' => $result['cutoff_date']
        ]);
    } else {
        error_log("GDPR Cleanup ERROR: {$result['error']}");
        echo json_encode([
            'status' => 'error',
            'message' => $result['error']
        ]);
        exit(1);
    }
} catch (Exception $e) {
    error_log("GDPR Cleanup EXCEPTION: {$e->getMessage()}");
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit(1);
}

exit(0);
