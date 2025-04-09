<?php
/**
 * SupportController handles the "Buy me a coffee" support page
 * Allows users to contribute to the project's development and hosting costs
 * Features a donation QR code and support information
 */
class SupportController {
    /**
     * Renders the support page view
     * Displays donation information and QR code for contributions
     */
    public function index() {
        require_once __DIR__ . '/../views/support.php';
    }
}
