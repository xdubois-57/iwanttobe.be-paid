<?php
/**
 * Main routing configuration file
 * 
 * This file sets up all the routes for the application, including:
 * - Page routes (GET requests for viewing pages)
 * - Language change routes (both GET and POST for changing the interface language)
 * - API routes (POST requests for AJAX operations)
 */

// Include necessary controller classes
require_once 'controllers/HomeController.php';
require_once 'controllers/AboutController.php';
require_once 'controllers/GDPRController.php';
require_once 'controllers/Router.php';
require_once 'controllers/LanguageController.php';

// Initialize the router
$router = new Router();

// Page routes
// These routes handle the main navigation of the site
$router->get('/', 'HomeController@index');          // Homepage with QR code generation form
$router->get('/about', 'HomeController@about');     // About page with project information
$router->get('/gdpr', 'GDPRController@index');     // GDPR/Privacy policy page

/**
 * Language Change Handler
 * 
 * This function handles both GET and POST requests for changing the interface language.
 * It works in two ways:
 * 1. Via GET request: /language/en (for direct links)
 * 2. Via POST request: /language with lang parameter (for form submissions)
 * 
 * @param array $params Contains the language code in 'lang' parameter
 */
$languageChangeHandler = function($params) {
    $langController = LanguageController::getInstance();
    
    // Get language code from either POST data or URL parameter
    $lang = $_POST['lang'] ?? ($params['lang'] ?? null);
    
    // Attempt to change the language if a valid language code is provided
    if ($lang && $langController->setLanguage($lang)) {
        // On success, redirect back to the previous page or home
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    } else {
        // On failure (invalid language code), redirect to homepage
        header('Location: /');
    }
    exit;
};

// Register language routes
$router->get('/language/{lang}', $languageChangeHandler);  // For direct language links
$router->post('/language', $languageChangeHandler);        // For form submissions

// API routes
// These routes handle AJAX requests and return JSON responses
$router->post('/generate-qr', 'HomeController@generateQR');     // Handles QR code generation requests
