<?php
/*
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

declare(strict_types=1);

/**
 * Manages language translations and localization for the application
 */
class LanguageController {
    /** @var LanguageController|null Singleton instance */
    private static ?LanguageController $instance = null;
    /** @var array Translation data */
    private array $translations = [];
    /** @var string Current language code */
    private string $currentLang;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->currentLang = $this->determineLanguage();
        $this->loadTranslations();
    }

    /**
     * Get the singleton instance of LanguageController
     *
     * @return LanguageController The singleton instance
     */
    public static function getInstance(): LanguageController {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Change the current language
     *
     * @param array $params URL parameters
     * @return void
     */
    public function change(array $params): void {
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

    /**
     * Determine the current language based on session, cookie, and browser settings
     *
     * @return string The current language code
     */
    private function determineLanguage(): string {
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

    /**
     * Get the current language code
     *
     * @return string Current language code (e.g., 'en', 'fr', 'nl')
     */
    public function getCurrentLanguage(): string {
        return $this->currentLang;
    }

    /**
     * Load translations from the current language file
     *
     * @return void
     */
    private function loadTranslations(): void {
        $langFile = __DIR__ . '/../translations/' . $this->currentLang . '.php';
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        }
    }

    /**
     * Get a translated string by key
     *
     * @param string $key The translation key
     * @return string The translated string, or the key if not found
     */
    public function translate(string $key): string {
        return $this->translations[$key] ?? $key;
    }

    /**
     * Set the current language
     *
     * @param string $lang The language code (e.g., 'en', 'fr', 'nl')
     * @return bool True if the language was set successfully, false otherwise
     */
    public function setLanguage(string $lang): bool {
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
