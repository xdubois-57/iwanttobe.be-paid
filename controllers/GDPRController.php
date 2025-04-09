<?php
/**
 * GDPRController handles the GDPR (General Data Protection Regulation) page
 * Displays the privacy policy and data protection information
 * Ensures compliance with EU data protection laws
 */
class GDPRController {
    /**
     * Renders the GDPR policy page view
     * Shows the privacy policy and data handling information
     */
    public function index() {
        include __DIR__ . '/../views/gdpr.php';
    }
}
