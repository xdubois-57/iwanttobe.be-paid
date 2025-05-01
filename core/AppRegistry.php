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

require_once __DIR__ . '/AppInterface.php';

/**
 * Central registry for all applications
 */
class AppRegistry
{
    /**
     * @var AppRegistry|null Singleton instance
     */
    private static ?AppRegistry $instance = null;
    
    /**
     * @var array Registered apps indexed by slug
     */
    private array $apps = [];
    
    /**
     * @var AppInterface|null Currently active app
     */
    private ?AppInterface $currentApp = null;
    
    /**
     * @var Router|null Router instance
     */
    private ?Router $router = null;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {}
    
    /**
     * Gets the singleton instance
     * @return AppRegistry
     */
    public static function getInstance(): AppRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Sets the router instance
     * @param Router $router
     */
    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }
    
    /**
     * Registers an app
     * @param AppInterface $app
     */
    public function registerApp(AppInterface $app): void
    {
        $this->apps[$app->getSlug()] = $app;
    }
    
    /**
     * Gets all registered apps
     * @return array
     */
    public function getApps(): array
    {
        return $this->apps;
    }
    
    /**
     * Gets app by slug
     * @param string $slug
     * @return AppInterface|null
     */
    public function getApp(string $slug): ?AppInterface
    {
        return $this->apps[$slug] ?? null;
    }
    
    /**
     * Sets the current app by slug
     * @param string $slug
     * @return bool Success
     */
    public function setCurrent(string $slug): bool
    {
        $app = $this->getApp($slug);
        if ($app) {
            $this->currentApp = $app;
            return true;
        }
        $this->currentApp = null;
        return false;
    }
    
    /**
     * Gets the current app
     * @return AppInterface|null
     */
    public function getCurrent(): ?AppInterface
    {
        return $this->currentApp;
    }
    
    /**
     * Gets the current app's menu items
     * @return array Empty array if no current app
     */
    public function getCurrentMenuItems(): array
    {
        if ($this->currentApp === null) {
            return [];
        }
        return $this->currentApp->getMenuItems();
    }
    
    /**
     * Registers routes for all apps
     */
    public function registerAllRoutes(): void
    {
        if ($this->router === null) {
            throw new RuntimeException("Router not set in AppRegistry");
        }
        
        foreach ($this->apps as $app) {
            $app->registerRoutes($this->router);
        }
    }
}
