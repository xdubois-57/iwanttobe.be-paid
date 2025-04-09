<?php
class LanguageController {
    private static $instance = null;
    private $translations = [];
    private $currentLang;
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->currentLang = $this->determineLanguage();
        $this->loadTranslations();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function change($params) {
        // Get language code from either POST data or URL parameter
        $lang = $_POST['lang'] ?? ($params['lang'] ?? null);
        
        // Attempt to change the language if a valid language code is provided
        if ($lang && $this->setLanguage($lang)) {
            // On success, redirect back to the previous page or home
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        } else {
            // On failure (invalid language code), redirect to homepage
            header('Location: /');
        }
        exit;
    }
    
    private function determineLanguage() {
        // First check session
        if (isset($_SESSION['lang'])) {
            return $_SESSION['lang'];
        }
        
        // Then check cookie
        if (isset($_COOKIE['lang'])) {
            $cookieLang = $_COOKIE['lang'];
            $config = require __DIR__ . '/../config/languages.php';
            if (isset($config['available_languages'][$cookieLang])) {
                $_SESSION['lang'] = $cookieLang;
                return $cookieLang;
            }
        }
        
        // Then check browser language
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
        $config = require __DIR__ . '/../config/languages.php';
        
        // If browser language is supported, use it
        if (isset($config['available_languages'][$browserLang])) {
            $_SESSION['lang'] = $browserLang;
            return $browserLang;
        }
        
        // Default to English if browser language not supported
        $_SESSION['lang'] = 'en';
        return 'en';
    }
    
    public function getCurrentLanguage() {
        return $this->currentLang;
    }
    
    private function loadTranslations() {
        $langFile = __DIR__ . '/../translations/' . $this->currentLang . '.php';
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        }
    }
    
    public function translate($key) {
        return $this->translations[$key] ?? $key;
    }

    public function setLanguage($lang) {
        $config = require __DIR__ . '/../config/languages.php';
        if (!isset($config['available_languages'][$lang])) {
            return false;
        }

        $_SESSION['lang'] = $lang;
        setcookie('lang', $lang, time() + (86400 * 30), '/'); // 30 days
        $this->currentLang = $lang;
        $this->loadTranslations();
        return true;
    }
}
