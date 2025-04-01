<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/controllers/Router.php';
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/GDPRController.php';
require_once __DIR__ . '/controllers/LanguageController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$router = new Router();

// Home routes
$router->get('/', 'HomeController@index');
$router->post('/generate-qr', 'HomeController@generateQR');

// GDPR routes
$router->get('/gdpr', 'GDPRController@index');

// Language routes
$router->post('/change-language', function() {
    $controller = LanguageController::getInstance();
    return $controller->changeLanguage();
});

// About route
$router->get('/about', 'HomeController@about');

try {
    // Remove query string from URI
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $router->dispatch($uri);
} catch (Exception $e) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
