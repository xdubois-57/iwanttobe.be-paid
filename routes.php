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
require_once 'controllers/GDPRController.php';
require_once 'controllers/WhyUsController.php';
require_once 'controllers/Router.php';
require_once 'controllers/LanguageController.php';
require_once 'controllers/QRController.php';
require_once 'controllers/SupportController.php';

// Initialize the router
$router = new Router();

// Page routes
// These routes handle the main navigation of the site
$router->get('/', 'HomeController@index');          // Homepage with QR code generation form
$router->get('/gdpr', 'GDPRController@index');     // GDPR/Privacy policy page
$router->get('/why-us', 'WhyUsController@index');     // Why Us page
$router->get('/support', 'SupportController@index');     // Support/Buy me a coffee page

// Language routes
// These routes handle language changes via both GET and POST requests
$router->post('/language/{lang}', 'LanguageController@change');  // For direct language links
$router->post('/language', 'LanguageController@change');        // For form submissions

// API routes
// These routes handle AJAX requests and return JSON responses
$router->post('/generate-qr', 'QRController@generate');     // Handles QR code generation requests
