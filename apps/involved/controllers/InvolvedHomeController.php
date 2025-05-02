<?php
/**
 * Controller for the Involved! app.
 */
require_once __DIR__ . '/../models/EventModel.php';

class InvolvedHomeController {
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
        $code = $model->createEmpty();
        if ($code === false) {
            http_response_code(500);
            $db = DatabaseHelper::getInstance();
            echo 'Failed to create event. Error: ' . htmlspecialchars($db->getErrorMessage());
            return;
        }
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        header('Location: /' . $langSlug . '/involved/' . $code);
        exit;
    }

    /**
     * Join an existing event via code
     */
    public function join($params = []) {
        $code = isset($_POST['event_code']) ? strtoupper(trim($_POST['event_code'])) : '';
        if ($code === '') {
            $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
            header('Location: /' . $langSlug . '/involved');
            exit;
        }
        $langSlug = $params['lang'] ?? LanguageController::getInstance()->getCurrentLanguage();
        header('Location: /' . $langSlug . '/involved/' . urlencode($code));
        exit;
    }

    /**
     * Display an event page
     */
    public function show($params) {
        $code = $params['code'] ?? '';
        $model = new EventModel();
        $event = $model->getByKey($code);
        if (!$event) {
            http_response_code(404);
            echo 'Event not found';
            return;
        }
        $eventData = $event;
        // Set current app for header navigation
        $currentApp = 'involved';
        require_once __DIR__ . '/../views/event.php';
    }
}
