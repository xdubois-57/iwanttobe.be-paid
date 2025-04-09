<?php
/**
 * WhyUsController handles the "Why Us" page
 * Displays the key features and advantages of using QR Transfer
 * Features a comparison table with other payment solutions
 */
class WhyUsController {
    /**
     * Renders the "Why Us" page view
     * Shows the comparison table and feature highlights
     */
    public function index() {
        require_once __DIR__ . '/../views/why-us.php';
    }
}
