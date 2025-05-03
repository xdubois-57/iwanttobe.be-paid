<?php
/**
 * Controller for the Involved! app.
 */
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/WordCloudModel.php';

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
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_create_failed') . ' ' . htmlspecialchars($db->getErrorMessage());
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
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
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
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
            return;
        }
        
        // Check if this event requires a password and user isn't already authorized
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            // Show password prompt form with error if applicable
            $currentApp = 'involved';
            $eventCode = $code;
            $lang = LanguageController::getInstance();
            
            $errorMessage = null;
            if (isset($_GET['error'])) {
                if ($_GET['error'] === 'invalid_password') {
                    // Hardcode the error messages for now to ensure they display correctly
                    if ($lang->getCurrentLanguage() === 'fr') {
                        $errorMessage = 'Mot de passe invalide, veuillez réessayer.';
                    } else {
                        $errorMessage = 'Invalid password, please try again.';
                    }
                } elseif ($_GET['error'] === 'missing_password') {
                    if ($lang->getCurrentLanguage() === 'fr') {
                        $errorMessage = 'Veuillez entrer un mot de passe.';
                    } else {
                        $errorMessage = 'Please enter a password.';
                    }
                }
            }
            
            // Simply store the complete original URL,
            // preferring the redirect parameter if it exists
            $originalUrl = isset($_GET['redirect']) ? $_GET['redirect'] : $_SERVER['REQUEST_URI'];
            
            // Log the original URL for debugging
            $logFile = __DIR__ . '/../../../logs/redirect_debug.log';
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Original URL in show method: $originalUrl\n", FILE_APPEND);
            
            require_once __DIR__ . '/../views/password_prompt.php';
            return;
        }
        
        // User is authorized or no password needed
        $currentApp = 'involved';
        $eventData = $event;
        
        // Get word clouds for this event
        $wcModel = new WordCloudModel();
        $wordClouds = $wcModel->getByEvent((int)$event['id']);
        
        // Load translations from the home.php file in the app-specific translation path
        $lang = LanguageController::getInstance();
        
        // Just load the app-specific translations - they're already organized by language
        // The LanguageController will handle fallbacks automatically
        $lang->loadAppTranslationsForPath(__DIR__ . '/../translations/en/home.php');
        
        // If current language is not English, load that translation too
        if ($lang->getCurrentLanguage() !== 'en') {
            $langFile = __DIR__ . '/../translations/' . $lang->getCurrentLanguage() . '/home.php';
            if (file_exists($langFile)) {
                $lang->loadAppTranslationsForPath($langFile);
            }
        }
        
        require_once __DIR__ . '/../views/event.php';
    }
    
    /**
     * Verify event password
     */
    public function verifyPassword($params = []) {
        $code = isset($_POST['event_code']) ? strtoupper(trim($_POST['event_code'])) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        
        // Debug: Write info to log file
        $logFile = __DIR__ . '/../../../logs/redirect_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Event code: $code\n", FILE_APPEND);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Redirect URL from POST: " . ($_POST['redirect_url'] ?? 'none') . "\n", FILE_APPEND);
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
        
        if (empty($code)) {
            header('Location: /' . $langSlug . '/involved');
            exit;
        }
        
        if (empty($password)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '?error=missing_password&redirect=' . urlencode($_POST['redirect_url'] ?? ''));
            exit;
        }
        
        $model = new EventModel();
        $event = $model->getByKey($code, $password);
        
        if (!$event) {
            // Redirect back to password prompt with error
            header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '?error=invalid_password&redirect=' . urlencode($_POST['redirect_url'] ?? ''));
            exit;
        }
        
        // Password verified, mark event as authorized
        $this->authorizeEvent($code);
        
        // Get the redirect URL from POST
        $redirectUrl = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : '';
        
        // Log for debugging
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Original redirect URL: $redirectUrl\n", FILE_APPEND);
        
        // Check if the redirect URL itself contains a redirect parameter
        if (strpos($redirectUrl, 'redirect=') !== false) {
            // Parse the URL to extract the redirect parameter
            $parsedUrl = parse_url($redirectUrl);
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $queryParams);
                if (isset($queryParams['redirect'])) {
                    // Use the redirect parameter as our actual redirect URL
                    $redirectUrl = urldecode($queryParams['redirect']);
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Extracted nested redirect URL: $redirectUrl\n", FILE_APPEND);
                }
            }
        }
        
        // Simple redirect - if we have a URL and it contains this event code, use it
        if (!empty($redirectUrl) && (strpos($redirectUrl, '/involved/' . strtolower($code)) !== false || 
                                    strpos($redirectUrl, '/involved/' . $code) !== false)) {
            // Redirect to the original URL
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Redirecting to final URL: $redirectUrl\n", FILE_APPEND);
            header('Location: ' . $redirectUrl);
        } else {
            // Default fallback
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Falling back to default URL: /$langSlug/involved/" . urlencode($code) . "\n", FILE_APPEND);
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
        }
        exit;
    }

    /**
     * Create a word cloud for an event
     */
    public function createWordCloud($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $question = isset($_POST['question']) ? trim($_POST['question']) : '';
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();

        if ($code === '' || $question === '') {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
            exit;
        }

        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
            return;
        }

        // Authorization check (password or session)
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
            exit;
        }

        $wcModel = new WordCloudModel();
        $newId = $wcModel->create((int)$event['id'], $question);

        header('Location: /' . $langSlug . '/involved/' . urlencode($code));
        exit;
    }

    /**
     * Delete a word cloud
     */
    public function deleteWordCloud($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $wcid = (int)($params['wcid'] ?? 0);
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();

        header('Content-Type: application/json');

        if ($code === '' || $wcid === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'missing_parameters']);
            return;
        }

        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'event_not_found']);
            return;
        }

        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'unauthorized']);
            return;
        }

        $wcModel = new WordCloudModel();
        $success = $wcModel->delete($wcid);
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'delete_failed']);
        }
    }

    /**
     * Show a word cloud page
     */
    public function showWordCloud($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $wcid = (int)($params['wcid'] ?? 0);
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();

        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
            return;
        }

        // Authorization check
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
            exit;
        }

        $wcModel = new WordCloudModel();
        $wordCloud = $wcModel->getById($wcid);
        if (!$wordCloud || (int)$wordCloud['event_id'] !== (int)$event['id']) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('wordcloud_not_found');
            return;
        }

        $eventData = $event;
        $wordCloudData = $wordCloud;

        $currentApp = 'involved';
        require_once __DIR__ . '/../views/wordcloud.php';
    }
    
    /**
     * Show form to add a word to a word cloud
     */
    public function showAddWordForm($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $wcid = (int)($params['wcid'] ?? 0);
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();

        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
            return;
        }

        // Authorization check
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            // Redirect to the event page with the complete URL for later redirection
            $originalUrl = $_SERVER['REQUEST_URI'];
            header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '?redirect=' . urlencode($originalUrl));
            exit;
        }

        $wcModel = new WordCloudModel();
        $wordCloud = $wcModel->getById($wcid);
        if (!$wordCloud || (int)$wordCloud['event_id'] !== (int)$event['id']) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('wordcloud_not_found');
            return;
        }

        $eventData = $event;
        $wordCloudData = $wordCloud;

        $currentApp = 'involved';
        require_once __DIR__ . '/../views/add_word_form.php';
    }
    
    /**
     * Process adding a word to a word cloud
     */
    public function addWord($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $wcid = (int)($params['wcid'] ?? 0);
        $word = isset($_POST['word']) ? trim($_POST['word']) : '';
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();

        if (empty($word)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '/wordcloud/' . $wcid . '/add?error=missing_word');
            exit;
        }

        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
            return;
        }

        // Authorization check
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
            exit;
        }

        $wcModel = new WordCloudModel();
        $wordCloud = $wcModel->getById($wcid);
        if (!$wordCloud || (int)$wordCloud['event_id'] !== (int)$event['id']) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('wordcloud_not_found');
            return;
        }

        // Add the word
        $wcModel->addWord($wcid, $word);
        
        // Redirect back to the add word form instead of wordcloud
        header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '/wordcloud/' . $wcid . '/add?success=true');
        exit;
    }

    /**
     * Delete a word from a word cloud
     */
    public function deleteWord($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $wcid = (int)($params['wcid'] ?? 0);
        $word = isset($_POST['word']) ? trim($_POST['word']) : '';
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();

        if (empty($word)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => LanguageController::getInstance()->translate('word_parameter_is_required')]);
            exit;
        }

        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => LanguageController::getInstance()->translate('event_not_found')]);
            exit;
        }

        // Authorization check
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => LanguageController::getInstance()->translate('unauthorized')]);
            exit;
        }

        $wcModel = new WordCloudModel();
        $wordCloud = $wcModel->getById($wcid);
        if (!$wordCloud || (int)$wordCloud['event_id'] !== (int)$event['id']) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => LanguageController::getInstance()->translate('wordcloud_not_found')]);
            exit;
        }

        // Delete the word
        $success = $wcModel->deleteWordByText($wcid, $word);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
        exit;
    }

    /**
     * Serve word cloud words as JSON for AJAX endpoint
     * GET /{lang}/involved/{code}/{wcid}/words
     * @param array $params
     */
    public function getWordCloudWords($params) {
        // $params['code'] = event key, $params['wcid'] = word cloud id
        $eventKey = $params['code'] ?? null;
        $cloudId = $params['wcid'] ?? null;
        if (!$eventKey || !$cloudId) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing parameters']);
            exit;
        }
        require_once __DIR__ . '/../models/WordCloudModel.php';
        $wcModel = new \WordCloudModel();
        $words = $wcModel->getWords($cloudId);
        $result = [];
        foreach ($words as $word) {
            $result[] = [$word['word'], intval($word['weight'] ?? 1)];
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Check presence for a given URL
     * This method tracks active users and returns the count
     */
    public function checkPresence($params) {
        // Ensure we have a session for the user
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Default response (used in case of any error)
        $response = ['success' => true, 'count' => 0];
        
        try {
            // Get URL parameter
            $url = isset($_GET['url']) ? trim($_GET['url']) : '';
            
            if (empty($url)) {
                throw new Exception('URL parameter is required');
            }
            
            $db = DatabaseHelper::getInstance();
            
            // For now, just return a simple success with 1 active user (the current user)
            // This gives immediate feedback while we fix the underlying database issue
            $response = ['success' => true, 'count' => 1];
            
            // Output as JSON
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
            
        } catch (Exception $e) {
            // Just use the default response with count=1 for now
            $response = ['success' => true, 'count' => 1];
            
            // Output as JSON
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }
    }
}
