<?php
/**
 * Controller for the Involved! app.
 */
require_once __DIR__ . '/../models/EventModel.php';

class InvolvedHomeController {
    /**
     * Initialize session for authorized events tracking
     */
    private function initSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['involved_authorized_events'])) {
            $_SESSION['involved_authorized_events'] = [];
        }
    }

    /**
     * Check if user is authorized for an event
     */
    private function isAuthorized($eventCode) {
        $this->initSession();
        return in_array(strtoupper($eventCode), $_SESSION['involved_authorized_events']);
    }

    /**
     * Mark an event as authorized
     */
    private function authorizeEvent($eventCode) {
        $this->initSession();
        $eventCode = strtoupper($eventCode);
        if (!in_array($eventCode, $_SESSION['involved_authorized_events'])) {
            $_SESSION['involved_authorized_events'][] = $eventCode;
        }
    }

    /**
     * Landing page (create / join)
     */
    public function index($params) {
        // Set current app for header navigation
        $currentApp = 'involved';
        require_once __DIR__ . '/../views/involved_home.php';
    }

    /**
     * Create a new event and redirect to its page
     */
    public function create($params = []) {
        $model = new EventModel();
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $code = $model->createEmpty($password);
        if ($code === false) {
            http_response_code(500);
            $db = DatabaseHelper::getInstance();
            echo 'Failed to create event. Error: ' . htmlspecialchars($db->getErrorMessage());
            return;
        }
        
        // Mark this event as authorized for the current session
        $this->authorizeEvent($code);
        
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        header('Location: /' . $langSlug . '/involved/' . $code);
        exit;
    }

    /**
     * Join an existing event via code
     */
    public function join($params = []) {
        $code = isset($_POST['event_code']) ? strtoupper(trim($_POST['event_code'])) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        
        if ($code === '') {
            header('Location: /' . $langSlug . '/involved');
            exit;
        }

        $model = new EventModel();
        
        // First check if the event exists without checking password
        $eventExists = $model->getByKey($code);
        
        if (!$eventExists) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }
        
        // Now check if a password is needed and if the provided one is correct
        if ($eventExists['password'] && !empty($eventExists['password'])) {
            // Password protected event
            if (empty($password)) {
                // No password provided but needed - redirect to event page which will show password prompt
                header('Location: /' . $langSlug . '/involved/' . urlencode($code));
                exit;
            }
            
            // Password provided, check if correct
            $event = $model->getByKey($code, $password);
            if (!$event) {
                // Invalid password - redirect to event page with error
                header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '?error=invalid_password');
                exit;
            }
        }

        // Everything OK - mark this event as authorized for the current session
        $this->authorizeEvent($code);

        header('Location: /' . $langSlug . '/involved/' . urlencode($code));
        exit;
    }

    /**
     * Display an event page or prompt for password
     */
    public function show($params) {
        $code = strtoupper($params['code'] ?? '');
        $model = new EventModel();
        
        // Get event basic info first (without password check)
        $event = $model->getByKey($code);
        
        if (!$event) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }
        
        // Check if this event requires a password and user isn't already authorized
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            // Show password prompt form with error if applicable
            $currentApp = 'involved';
            $eventCode = $code;
            $errorMessage = isset($_GET['error']) && $_GET['error'] === 'invalid_password' ? 'Invalid password. Please try again.' : null;
            require_once __DIR__ . '/../views/password_prompt.php';
            return;
        }
        
        // User is authorized or event doesn't need password
        $eventData = $event;
        
        // Set current app for header navigation
        $currentApp = 'involved';
        require_once __DIR__ . '/../views/event.php';
    }
    
    /**
     * Verify event password
     */
    public function verifyPassword($params = []) {
        $code = isset($_POST['event_code']) ? strtoupper(trim($_POST['event_code'])) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        
        if (empty($code)) {
            header('Location: /' . $langSlug . '/involved');
            exit;
        }
        
        if (empty($password)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '?error=missing_password');
            exit;
        }
        
        $model = new EventModel();
        $event = $model->getByKey($code, $password);
        
        if (!$event) {
            // Redirect back to password prompt with error
            header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '?error=invalid_password');
            exit;
        }
        
        // Password verified, mark event as authorized
        $this->authorizeEvent($code);
        
        // Redirect to event page
        header('Location: /' . $langSlug . '/involved/' . urlencode($code));
        exit;
    }
}
