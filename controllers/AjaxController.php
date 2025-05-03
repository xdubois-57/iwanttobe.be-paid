<?php
/**
 * Controller for AJAX requests.
 */
require_once __DIR__ . '/../apps/involved/models/OverlayObjectModel.php';
require_once __DIR__ . '/../apps/involved/models/OverlayPresenceModel.php';
require_once __DIR__ . '/../controllers/LanguageController.php';

class AjaxController {
    /**
     * Increment likes for a given URL
     * POST /ajax/like
     * @param array $params
     */
    public function incrementLikes($params = []) {
        // CORS headers for AJAX
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        // Only allow POST method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Get URL from POST data
        $url = isset($_POST['url']) ? trim($_POST['url']) : '';
        
        if (empty($url)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            return;
        }
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid URL format']);
            return;
        }
        
        // Process like
        $model = new OverlayObjectModel();
        $likes = $model->incrementLikes($url);
        
        if ($likes === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to increment likes']);
            return;
        }
        
        echo json_encode(['success' => true, 'likes' => $likes]);
    }
    
    /**
     * Get likes count for a given URL
     * GET /ajax/likes?url=...
     * @param array $params
     */
    public function getLikes($params = []) {
        // CORS headers for AJAX
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        // Only allow GET method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Get URL from query string
        $url = isset($_GET['url']) ? trim($_GET['url']) : '';
        
        if (empty($url)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            return;
        }
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid URL format']);
            return;
        }
        
        // Get likes
        $model = new OverlayObjectModel();
        $likes = $model->getLikes($url);
        
        echo json_encode(['success' => true, 'likes' => $likes]);
    }
    
    /**
     * Update user presence for a given URL
     * POST /ajax/presence
     * @param array $params
     */
    public function updatePresence($params = []) {
        // CORS headers for AJAX
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        // Only allow POST method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Get URL and session ID from POST data
        $url = isset($_POST['url']) ? trim($_POST['url']) : '';
        
        if (empty($url)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            return;
        }
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid URL format']);
            return;
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionId = session_id();
        
        // Update presence
        $model = new OverlayPresenceModel();
        $result = $model->updatePresence($url, $sessionId);
        
        if ($result === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update presence']);
            return;
        }
        
        // Get active users count
        $activeCount = $model->getActivePresenceCount($url);
        
        echo json_encode([
            'success' => true, 
            'active_users' => $activeCount
        ]);
    }
}
