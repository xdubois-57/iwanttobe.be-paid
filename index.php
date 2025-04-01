<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include router and routes
require_once __DIR__ . '/controllers/Router.php';
$router = new Router();
require_once __DIR__ . '/routes.php';

try {
    // Remove query string from URI
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $router->dispatch($uri);
} catch (Exception $e) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
