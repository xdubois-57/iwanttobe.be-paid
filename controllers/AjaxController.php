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
     * @deprecated
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
        
        // Normalize URL for word cloud pages
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $pathSegments = explode('/', trim($path, '/'));
        
        // Check if this is a wordcloud URL format: /lang/involved/eventkey/wordcloud/wcid[/add]
        if (count($pathSegments) >= 5 && $pathSegments[1] === 'involved' && $pathSegments[3] === 'wordcloud') {
            // Normalize URL to always match the main wordcloud URL
            if (isset($pathSegments[5]) && $pathSegments[5] === 'add') {
                // This is an "add word" page - normalize to the main wordcloud URL
                $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
                $host = $parsedUrl['host'] ?? '';
                $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
                
                // Rebuild the URL up to the wordcloud ID (removing /add)
                $url = "$scheme://$host$port/{$pathSegments[0]}/{$pathSegments[1]}/{$pathSegments[2]}/{$pathSegments[3]}/{$pathSegments[4]}";
                error_log("Normalized URL for like increment: " . $url);
            }
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
     * @deprecated
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
        
        // Dedicated log file
        $logFile = __DIR__ . '/../logs/presence_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: updatePresence called\n", FILE_APPEND);
        
        // Only allow POST method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Method not allowed: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Get URL and session ID from POST data
        $url = isset($_POST['url']) ? trim($_POST['url']) : '';
        
        if (empty($url)) {
            http_response_code(400);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Empty URL\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            return;
        }
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Invalid URL format: $url\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Invalid URL format']);
            return;
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionId = session_id();
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Processing with session ID: $sessionId\n", FILE_APPEND);
        
        // Update presence
        $model = new OverlayPresenceModel();
        $result = $model->updatePresence($url, $sessionId);
        
        if ($result === false) {
            http_response_code(500);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Failed to update presence\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Failed to update presence']);
            return;
        }
        
        // Get active users count
        $activeCount = $model->getActivePresenceCount($url);
        
        // Force the count to the actual value from the database for debugging
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: About to return presence count: $activeCount for URL: $url\n", FILE_APPEND);
        
        // Build and log the exact JSON response we're sending
        $response = [
            'success' => true, 
            'count' => $activeCount,
            'active_users' => $activeCount  // Add for backward compatibility
        ];
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: JSON response: " . json_encode($response) . "\n", FILE_APPEND);
        
        echo json_encode($response);
    }
    
    /**
     * Get presence count for a URL
     * GET /ajax/presence
     * @param array $params
     */
    public function getPresence($params = []) {
        // CORS headers for AJAX
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        $logFile = __DIR__ . '/../logs/presence_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: getPresence called (GET method)\n", FILE_APPEND);
        
        // Only allow GET method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Method not allowed in getPresence: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Get URL from GET data
        $url = isset($_GET['url']) ? trim($_GET['url']) : '';
        
        if (empty($url)) {
            http_response_code(400);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Empty URL in getPresence\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            return;
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: Getting presence for URL: $url\n", FILE_APPEND);
        
        // Get presence count
        $model = new OverlayPresenceModel();
        $count = $model->getActivePresenceCount($url);
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: getPresence returning count: $count for URL: $url\n", FILE_APPEND);
        
        $response = [
            'success' => true,
            'count' => $count
        ];
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - AjaxController: getPresence JSON response: " . json_encode($response) . "\n", FILE_APPEND);
        echo json_encode($response);
    }
    
    /**
     * Append an emoji to the queue for a URL
     * POST /ajax/emoji
     * Required POST params: url, emoji
     */
    public function appendEmoji($params = []) {
        // CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $url   = $_POST['url']   ?? '';
        $emoji = $_POST['emoji'] ?? '';

        if ($url === '' || $emoji === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'url and emoji required']);
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid URL']);
            return;
        }

        // Append via model
        $model = new OverlayObjectModel();
        $ok = $model->appendEmoji($url, $emoji);
        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'DB error']);
        }
    }

    /**
     * Get (and pop) the next emojis from the queue
     * GET /ajax/emoji?url=...&max=10
     */
    public function getEmojis($params = []) {
        // CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $url = $_GET['url'] ?? '';
        $max = isset($_GET['max']) ? (int)$_GET['max'] : 10;

        if ($url === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'url required']);
            return;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid URL']);
            return;
        }

        $model  = new OverlayObjectModel();
        $emojis = $model->popQueuedEmojis($url, $max);
        echo json_encode(['success' => true, 'emojis' => $emojis]);
    }
}
