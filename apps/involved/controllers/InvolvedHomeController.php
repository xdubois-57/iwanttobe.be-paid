<?php
/**
 * Controller for the Involved! app.
 */
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/EventItemModel.php';

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
        
        // Store created event code for local storage
        if (!isset($_SESSION['remember_created_event'])) {
            $_SESSION['remember_created_event'] = $code;
            header('Location: /' . $langSlug . '/involved/' . $code . '?remember=true');
        } else {
            header('Location: /' . $langSlug . '/involved/' . $code);
        }
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
        $event = $model->getByKey($code);
        if (!$event) {
            header('Location: /' . $langSlug . '/involved?error=event_not_found');
            exit;
        }

        // Check for active URL
        if (!empty($event['active_url'])) {
            header('Location: ' . $event['active_url']);
            exit;
        }

        // If no active URL, redirect to waiting room
        header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '/wait');
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
     * Create an event item
     * 
     * Handles all event item types directly through EventItemModel
     * without any special routing or model-specific logic
     */
    public function createEventItem($params) {
        $code = strtoupper($params['code'] ?? '');
        $question = $_POST['question'] ?? '';
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        
        // Authorization check
        if (!$this->isAuthorized($code)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
            exit;
        }
        
        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            Logger::getInstance()->error('Event not found: ' . $code);
            echo 'Event not found';
            return;
        }
        
        // Get the event's position to determine the order of the new item
        $eventItemModel = new EventItemModel();
        $maxPosition = $eventItemModel->getMaxPositionForEvent($event['id']);
        $position = $maxPosition + 1;
        
        // Get the event item type (default to 'text' if not specified)
        $type = $_POST['event_item_type'] ?? 'text';
        
        // Validate the event item type
        $validTypes = ['text', 'horizontal_bar_chart', 'vertical_bar_chart', 'pie_chart', 'doughnut'];
        if (!in_array($type, $validTypes)) {
            $type = 'text'; // Default to text if invalid type
            Logger::getInstance()->warning('Invalid event item type specified, defaulting to text');
        }
        
        // Create the event item directly using EventItemModel
        $eventItemId = $eventItemModel->create((int)$event['id'], $question, $position, $type);
        
        if ($eventItemId) {
            Logger::getInstance()->info('Created new event item: ' . $eventItemId . ' (type: ' . $type . ') for event: ' . $code);
            // Redirect to the event page
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
            exit;
        } else {
            // Error
            http_response_code(500);
            Logger::getInstance()->error('Error creating event item for event: ' . $code);
            echo 'Error creating event item';
        }
    }
    
    /**
     * Show an event item page
     */
    public function showEventItem($params = []) {
        require_once __DIR__ . '/../models/EventAnswerModel.php';
        require_once __DIR__ . '/../models/EventItemModel.php';

        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
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

        $eventItemModel = new EventItemModel();
        $eventItem = $eventItemModel->getById($itemId);
        if (!$eventItem) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_item_not_found');
            return;
        }

        $currentApp = 'involved';
        require_once __DIR__ . '/../views/eventitem.php';
    }
    
    /**
     * Get answers for an event item
     */
    public function getEventItemAnswers($params = []) {
        require_once __DIR__ . '/../models/EventAnswerModel.php';
        
        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
        
        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Event not found']);
            exit;
        }
        
        $eventItemModel = new EventItemModel();
        $eventItem = $eventItemModel->getById($itemId);
        if (!$eventItem) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Event item not found']);
            exit;
        }
        
        $answerModel = new EventAnswerModel();
        $answers = $answerModel->getByEventItem($itemId);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'answers' => $answers]);
    }
    
    /**
     * Add an answer to an event item
     */
    public function addEventItemAnswer($params = []) {
        require_once __DIR__ . '/../models/EventAnswerModel.php';
        
        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
        $value = isset($_POST['value']) ? trim($_POST['value']) : '';
        
        if (empty($value)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Answer value is required']);
            exit;
        }
        
        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Event not found']);
            exit;
        }
        
        $eventItemModel = new EventItemModel();
        $eventItem = $eventItemModel->getById($itemId);
        if (!$eventItem) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Event item not found']);
            exit;
        }
        
        $answerModel = new EventAnswerModel();
        $answerId = $answerModel->upsertAnswer($itemId, $value);
        
        if ($answerId) {
            Logger::getInstance()->info('User submitted answer: ' . json_encode(['event_item_id' => $itemId, 'value' => $value, 'user_ip' => $_SERVER['REMOTE_ADDR'] ?? null]));
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $answerId]);
        } else {
            Logger::getInstance()->error('Failed to add or increment answer for event_item_id=' . $itemId . ', value=' . json_encode($value));
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to add answer']);
        }
    }
    
    /**
     * Show form to add an answer to an event item
     */
    public function showEventItemAnswerForm($params = []) {
        require_once __DIR__ . '/../models/EventAnswerModel.php';
        require_once __DIR__ . '/../models/EventItemModel.php';
        
        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        
        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
            return;
        }
        
        $eventItemModel = new EventItemModel();
        $eventItem = $eventItemModel->getById($itemId);
        if (!$eventItem) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_item_not_found');
            return;
        }
        $currentApp = 'involved';
        require_once __DIR__ . '/../views/event_item_answer.php';
    }

    /**
     * Show waiting room page if event has no active URL
     */
    public function showWaitingRoom($params) {
        $code = strtoupper($params['code'] ?? '');
        $model = new EventModel();
        $event = $model->getByKey($code);
        if (!$event) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_not_found');
            return;
        }
        require_once __DIR__ . '/../views/waiting_room.php';
    }

    /**
     * Delete an event item by ID (POST)
     * Route: /{lang}/involved/{code}/eventitem/{itemid}/delete
     */
    public function deleteEventItem($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();

        require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/EventItemModel.php';
        require_once __DIR__ . '/../models/EventItemModel.php';
        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            http_response_code(404);
            echo LanguageController::getInstance()->translate('event_not_found');
            return;
        }
        // Authorization check
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            http_response_code(403);
            echo LanguageController::getInstance()->translate('unauthorized');
            return;
        }
        $eventItemModel = new EventItemModel();
        $item = $eventItemModel->getById($itemId);
        if (!$item || (int)$item['event_id'] !== (int)$event['id']) {
            http_response_code(404);
            echo LanguageController::getInstance()->translate('event_item_not_found');
            return;
        }
        $success = $eventItemModel->delete($itemId);
        if ($success) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
        } else {
            http_response_code(500);
            echo LanguageController::getInstance()->translate('delete_failed');
        }
        exit;
    }

    /**
     * Update the order of event items via AJAX
     * POST JSON: { orderedIds: [id1, id2, ...] }
     * Route: /{lang}/involved/{code}/eventitem/reorder
     */
    public function reorderEventItems($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        $payload = json_decode(file_get_contents('php://input'), true);
        $orderedIds = $payload['orderedIds'] ?? null;
        header('Content-Type: application/json');
        if (!$orderedIds || !is_array($orderedIds)) {
            echo json_encode(['success' => false, 'error' => 'Invalid payload']);
            return;
        }
        require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/EventItemModel.php';
        require_once __DIR__ . '/../models/EventItemModel.php';
        $eventModel = new EventModel();
        $event = $eventModel->getByKey($code);
        if (!$event) {
            echo json_encode(['success' => false, 'error' => 'Event not found']);
            return;
        }
        // Authorization
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }
        $eventItemModel = new EventItemModel();
        $success = $eventItemModel->updatePositions($orderedIds);
        echo json_encode(['success' => $success]);
    }



    /**
     * Delete an answer for an event item (AJAX)
     * Expects POST, returns JSON
     */
    public function deleteEventItemAnswer($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
        $answerId = (int)($params['answerid'] ?? 0);
        
        header('Content-Type: application/json');
        
        // Check if we have valid IDs
        if ($itemId === 0 || $answerId === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'missing_parameters']);
            return;
        }
        
        // Authorization check
        if (!$this->isAuthorized($code)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'unauthorized']);
            return;
        }
        
        // Load the models
        require_once __DIR__ . '/../models/EventItemModel.php';
        require_once __DIR__ . '/../models/EventAnswerModel.php';
        
        // Delete the answer
        $eventAnswerModel = new EventAnswerModel();
        $success = $eventAnswerModel->deleteAnswer($answerId, $itemId);
        
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'delete_failed']);
        }
    }
    
    /**
     * Show form to add content to an event item
     */
    public function showAddItemForm($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
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

        require_once __DIR__ . '/../models/EventItemModel.php';
        $eventItemModel = new EventItemModel();
        $eventItem = $eventItemModel->getById($itemId);
        if (!$eventItem) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_item_not_found');
            return;
        }

        $currentApp = 'involved';
        require_once __DIR__ . '/../views/add_item_form.php';
    }

    /**
     * Add content to an event item
     */
    public function addItem($params = []) {
        $code = strtoupper($params['code'] ?? '');
        $itemId = (int)($params['itemid'] ?? 0);
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($code === '' || $itemId === 0 || $content === '') {
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

        // Authorization check
        if (!empty($event['password']) && !$this->isAuthorized($code)) {
            header('Location: /' . $langSlug . '/involved/' . urlencode($code));
            exit;
        }

        require_once __DIR__ . '/../models/EventItemModel.php';
        $eventItemModel = new EventItemModel();
        $eventItem = $eventItemModel->getById($itemId);
        if (!$eventItem) {
            http_response_code(404);
            $lang = LanguageController::getInstance();
            echo $lang->translate('event_item_not_found');
            return;
        }

        // Add the content as an answer
        require_once __DIR__ . '/../models/EventAnswerModel.php';
        $eventAnswerModel = new EventAnswerModel();
        $newId = $eventAnswerModel->create($itemId, $content);
        
        if ($newId) {
            Logger::getInstance()->info('Added content to event item ' . $itemId . ': ' . $content);
            header('Location: /' . $langSlug . '/involved/' . urlencode($code) . '/eventitem/' . $itemId);
        } else {
            Logger::getInstance()->error('Failed to add content to event item ' . $itemId);
            http_response_code(500);
            echo 'Failed to add content';
        }
    }
}
