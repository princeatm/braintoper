<?php
/**
 * Router
 * Simple router for MVC application
 */

namespace App;

class Router
{
    private array $routes = [];
    private string $uri;
    private string $method;

    public function __construct()
    {
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Register route
     */
    public function add(string $method, string $path, string $controller, string $action): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
        ];
        return $this;
    }

    /**
     * Dispatch route
     */
    public function dispatch(): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $this->method && $this->matchRoute($route['path'])) {
                $controllerClass = 'App\\Controllers\\' . $route['controller'];
                $controller = new $controllerClass();
                $action = $route['action'];
                $controller->$action();
                return;
            }
        }

        // Not found
        header('HTTP/1.0 404 Not Found');
        die('404 - Page Not Found');
    }

    /**
     * Match route with URI
     */
    private function matchRoute(string $path): bool
    {
        // Convert path to regex pattern
        $pattern = preg_replace('/\/{[a-zA-Z_][a-zA-Z0-9_]*\}/', '/([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        return (bool)preg_match($pattern, $this->uri);
    }
}
