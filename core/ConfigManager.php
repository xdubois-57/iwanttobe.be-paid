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

/**
 * ConfigManager
 * 
 * Manages website configuration stored in JSON file
 */
class ConfigManager {
    private static $instance = null;
    private $configFile;
    private $config;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->configFile = __DIR__ . '/../config/website_config.json';
        $this->loadConfig();
    }
    
    /**
     * Get the ConfigManager instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load configuration from JSON file
     */
    private function loadConfig() {
        if (file_exists($this->configFile)) {
            $jsonContent = file_get_contents($this->configFile);
            $this->config = json_decode($jsonContent, true);
        } else {
            // Default configuration
            $this->config = [
                'initialised' => false
            ];
        }
    }
    
    /**
     * Save current configuration to JSON file
     * 
     * @return bool True if saved successfully, false otherwise
     */
    public function saveConfig() {
        $jsonContent = json_encode($this->config, JSON_PRETTY_PRINT);
        return file_put_contents($this->configFile, $jsonContent) !== false;
    }
    
    /**
     * Get a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Configuration value
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Set a configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return $this For method chaining
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
        return $this;
    }
    
    /**
     * Check if website has been initialized
     * 
     * @return bool True if website is initialized
     */
    public function isInitialised() {
        return $this->get('initialised', false) === true;
    }
    
    /**
     * Mark website as initialized
     * 
     * @return bool True if saved successfully
     */
    public function markAsInitialised() {
        $this->set('initialised', true);
        return $this->saveConfig();
    }
}
