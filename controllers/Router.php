<?php
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
