<?php
/**
 * LandingController
 * Displays the landing page where the user can choose an application.
 */
class LandingController {
    public function index($params) {
        // Expose current app to header navigation
        $currentApp = 'landing';
        require_once __DIR__ . '/../views/landing.php';
    }
}
