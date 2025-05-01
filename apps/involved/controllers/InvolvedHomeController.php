<?php
/**
 * Placeholder home controller for the Involved! app.
 */
class InvolvedHomeController {
    public function index($params) {
        $currentApp = 'involved';
        require_once __DIR__ . '/../views/involved_home.php';
    }
}
