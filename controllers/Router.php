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

class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function dispatch($uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($this->routes[$method])) {
            throw new Exception('Method not allowed');
        }

        foreach ($this->routes[$method] as $route => $callback) {
            $params = [];
            if ($this->matchRoute($route, $uri, $params)) {
                return $this->executeCallback($callback, $params);
            }
        }

        throw new Exception('Route not found');
    }

    private function matchRoute($route, $uri, &$params) {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<\1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Filter out numeric keys, keeping only named parameters
            $params = array_filter($matches, function($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    private function executeCallback($callback, $params) {
        if (is_string($callback)) {
            if (strpos($callback, '@') !== false) {
                // Handle Controller@method format
                list($controller, $method) = explode('@', $callback);
                
                // Try to get instance using getInstance for singletons
                if (method_exists($controller, 'getInstance')) {
                    $instance = $controller::getInstance();
                } else {
                    $instance = new $controller();
                }
                
                return $instance->$method($params);
            }
        } elseif (is_callable($callback)) {
            // Handle anonymous functions and other callables
            return $callback($params);
        }
        throw new Exception('Invalid callback');
    }
}
