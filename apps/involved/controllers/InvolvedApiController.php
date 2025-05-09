<?php
/**
 * Controller for AJAX requests.
 */
require_once __DIR__ . '/../../../controllers/LanguageController.php';

class InvolvedApiController {
    // incrementLikes and getLikes methods have been removed
    // These have been replaced by the generic emoji mechanism
    
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
        $logFile = __DIR__ . '/../../../logs/presence_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: updatePresence called\n", FILE_APPEND);
        
        // Only allow POST method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: Method not allowed: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Get URL and session ID from POST data
        $url = isset($_POST['url']) ? trim($_POST['url']) : '';
        
        if (empty($url)) {
            http_response_code(400);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: Empty URL\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            return;
        }
        
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: Invalid URL format: $url\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Invalid URL format']);
            return;
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionId = session_id();
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: Processing with session ID: $sessionId\n", FILE_APPEND);
        
        // Update presence
        $model = new OverlayPresenceModel();
        $result = $model->updatePresence($url, $sessionId);
        
        if ($result === false) {
            http_response_code(500);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: Failed to update presence\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Failed to update presence']);
            return;
        }
        
        // Get active users count
        $activeCount = $model->getActivePresenceCount($url);
        
        // Get the active URL for the event, if available
        $activeUrl = null;
        
        // Attempt to extract event code from the URL
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $pathSegments = explode('/', trim($path, '/'));
        
        // Check if this is a /involved/ URL with an event code (format: /lang/involved/eventkey/...)
        if (count($pathSegments) >= 3 && $pathSegments[1] === 'involved') {
            $eventCode = $pathSegments[2];
            
            // Get the active URL for this event
            require_once __DIR__ . '/../models/EventModel.php';
            $eventModel = new EventModel();
            $activeUrl = $eventModel->getActiveUrl($eventCode);
            
            // Log the active URL for debugging
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: Active URL for event {$eventCode}: " . ($activeUrl ?? 'null') . "\n", FILE_APPEND);
        }
        
        // Force the count to the actual value from the database for debugging
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: About to return presence count: $activeCount for URL: $url\n", FILE_APPEND);
        
        // Build and log the exact JSON response we're sending
        $response = [
            'success' => true, 
            'count' => $activeCount,
            'active_users' => $activeCount  // Add for backward compatibility
        ];
        
        // Add active_url to the response if available
        if ($activeUrl) {
            $response['active_url'] = $activeUrl;
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: JSON response: " . json_encode($response) . "\n", FILE_APPEND);
        
        echo json_encode($response);
    }
    
    /**
     * Get presence count for a URL
     * GET /ajax/presence?url=...
     * @param array $params
     */
    public function getPresence($params = []) {
        // CORS headers for AJAX
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');
        
        $logFile = __DIR__ . '/../../../logs/presence_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: getPresence called (GET method)\n", FILE_APPEND);
        
        // Only allow GET method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: Method not allowed in getPresence: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Get URL from GET data
        $url = isset($_GET['url']) ? trim($_GET['url']) : '';
        
        if (empty($url)) {
            http_response_code(400);
            Logger::getInstance()->error('InvolvedApiController: Empty URL in getPresence');
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            return;
        }
        
        Logger::getInstance()->info('InvolvedApiController: Getting presence for URL: ' . $url);
        
        // Get presence count
        $model = new EventItemModel();
        if (method_exists($model, 'getActivePresenceCount')) {
            $count = $model->getActivePresenceCount($url);
        } else {
            $count = 0;
            Logger::getInstance()->error('InvolvedApiController: EventItemModel::getActivePresenceCount not implemented');
        }
        
        Logger::getInstance()->info('InvolvedApiController: getPresence returning count: ' . $count . ' for URL: ' . $url);
        
        $response = [
            'success' => true,
            'count' => $count
        ];
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: getPresence JSON response: " . json_encode($response) . "\n", FILE_APPEND);
        echo json_encode($response);
    }
    
    /**
     * Set active URL for an event
     * POST /ajax/set_active_url
     * @param array $params
     */
    public function setActiveUrl($params = []) {
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
        
        // Get event code and active URL from POST data
        $eventCode = isset($_POST['event_code']) ? trim($_POST['event_code']) : '';
        $activeUrl = isset($_POST['active_url']) ? trim($_POST['active_url']) : '';
        
        if (empty($eventCode)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Event code is required']);
            return;
        }
        
        // Allow empty string for activeUrl to mean "clear the active URL"
        if ($activeUrl === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Active URL is required']);
            return;
        }
        if ($activeUrl === '') {
            // Clear the active URL in the database
            require_once __DIR__ . '/../models/EventModel.php';
            $model = new EventModel();
            $result = $model->setActiveUrl($eventCode, '');
            if ($result === false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to clear active URL']);
                return;
            }
            echo json_encode(['success' => true]);
            return;
        }
        
        // Validate URL format for non-empty URLs
        if (!filter_var($activeUrl, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid URL format']);
            return;
        }
        
        // Update the event's active URL in the database
        require_once __DIR__ . '/../models/EventModel.php';
        $model = new EventModel();
        $result = $model->setActiveUrl($eventCode, $activeUrl);
        
        if ($result === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to set active URL']);
            return;
        }
        
        echo json_encode(['success' => true]);
    }
    
    /**
     * Append an emoji to the queue for an event
     * POST /{lang}/involved/{eventCode}/emoji
     * Required POST params: emoji (optional: eventItemId)
     */
    public function appendEmoji($params = []) {
        $logFile = __DIR__ . '/../../../logs/presence_debug.log';
        // Log all incoming data for debugging
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - appendEmoji called\n", FILE_APPEND);
        file_put_contents($logFile, "  Incoming params: " . json_encode($params) . "\n", FILE_APPEND);
        file_put_contents($logFile, "  Incoming POST: " . json_encode($_POST) . "\n", FILE_APPEND);

        // Extract event code from route params (use only 'code')
        $eventCode = $params['code'] ?? '';
        
        // CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            file_put_contents($logFile, "  Error: Method not allowed\n", FILE_APPEND);
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        // Get emoji and optional event item ID
        $emoji = $_POST['emoji'] ?? '';
        $eventItemId = isset($_POST['eventItemId']) ? (int)$_POST['eventItemId'] : null;

        file_put_contents($logFile, "  eventCode: {$eventCode}, emoji: {$emoji}, eventItemId: " . var_export($eventItemId, true) . "\n", FILE_APPEND);

        if ($eventCode === '' || $emoji === '') {
            file_put_contents($logFile, "  Error: Event code and emoji required\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Event code and emoji required']);
            return;
        }

        // Allow only certain emojis
        $allowedEmojis = ['❤️', '😂', '👍', '🔥', '🎉', '😅'];
        if (!in_array($emoji, $allowedEmojis)) {
            file_put_contents($logFile, "  Error: Invalid emoji\n", FILE_APPEND);
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid emoji']);
            return;
        }

        // Log the emoji submission
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - InvolvedApiController: appendEmoji for event: {$eventCode}, emoji: {$emoji}" . ($eventItemId ? ", eventItemId: {$eventItemId}" : "") . "\n", FILE_APPEND);

        // Append emoji to EVENT_ITEM if eventItemId is provided
        if ($eventItemId) {
            require_once __DIR__ . '/../models/EventItemModel.php';
            $eventItemModel = new EventItemModel();
            $ok = $eventItemModel->appendEmojiToEventItem($eventItemId, $emoji);
            file_put_contents($logFile, "  Used EventItemModel for emoji append.\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "  Error: eventItemId required for emoji submission to EVENT_ITEM.\n", FILE_APPEND);
            $ok = false;
        }
        if ($ok) {
            file_put_contents($logFile, "  Success: Emoji appended\n", FILE_APPEND);
            echo json_encode(['success' => true]);
        } else {
            file_put_contents($logFile, "  Error: Failed to append emoji\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to append emoji']);
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
