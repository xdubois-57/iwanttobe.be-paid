<?php
/**
 * Placeholder home controller for the Drive app.
 */
class DriveHomeController {
    public function index($params) {
        $currentApp = 'drive';
        require_once __DIR__ . '/../views/drive_home.php';
    }
}
