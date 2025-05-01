<?php
/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class LanguageController {
    private static $instance = null;
    private $translations = [];
    private $currentLang;
    
    private function __construct() {
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
        // Get language code from URL parameter only
        $lang = $params['lang'] ?? null;
        // Attempt to change the language if a valid language code is provided
        if ($lang && $this->setLanguage($lang)) {
            // Redirect to the same path but with new language code
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $segments = explode('/', trim($uri, '/'));
            if (!empty($segments) && isset($params['lang'])) {
                $segments[0] = $params['lang'];
                $newUri = '/' . implode('/', $segments);
                header('Location: ' . $newUri);
            } else {
                header('Location: /' . $params['lang']);
            }
        } else {
            header('Location: /');
        }
        exit;
    }
    
    private function determineLanguage() {
        // Extract language from the URL path (e.g., /en/why-us)
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $segments = explode('/', trim($uri, '/'));
        $config = require __DIR__ . '/../config/languages.php';
        $langFromUrl = $segments[0] ?? null;
        if ($langFromUrl && isset($config['available_languages'][$langFromUrl])) {
            return $langFromUrl;
        }
        // Default to English if not present in URL
        return 'en';
    }
    
    public function getCurrentLanguage() {
        return $this->currentLang;
    }
    
    private function loadTranslations() {
        // Load English base first for fallback
        $enFile = __DIR__ . '/../translations/en.php';
        $base = file_exists($enFile) ? require $enFile : [];

        $langFile = __DIR__ . '/../translations/' . $this->currentLang . '.php';
        $current = file_exists($langFile) ? require $langFile : [];

        // Overlay current language on top of English
        $this->translations = array_merge($base, $current);
    }
    
    public function translate($key) {
        return $this->translations[$key] ?? $key;
    }

    public function setLanguage($lang) {
        $config = require __DIR__ . '/../config/languages.php';
        if (!isset($config['available_languages'][$lang])) {
            return false;
        }
        $this->currentLang = $lang;
        $this->loadTranslations();
        return true;
    }
}
