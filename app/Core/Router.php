<?php

namespace App\Core;

use RuntimeException;

// Week 7: Router with dynamic {param} extraction via regex
class Router
{
    private array $routes = [];

    public function get(string $uri, string $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, string $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, string $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function delete(string $uri, string $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    private function addRoute(string $method, string $uri, string $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'uri'    => $uri,
            'action' => $action,
        ];
    }

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        // Strip query string
        $uri = parse_url($requestUri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Week 1: HTTP method override for PUT/DELETE via POST forms
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            $pattern = $this->buildPattern($route['uri']);
            if ($route['method'] === $requestMethod && preg_match($pattern, $uri, $matches)) {
                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->callAction($route['action'], $params);
                return;
            }
        }

        // Week 1: 404 response
        http_response_code(404);
        $view = __DIR__ . '/../views/errors/404.php';
        if (file_exists($view)) {
            include $view;
        } else {
            echo '<h1>404 — Page Not Found</h1>';
        }
    }

    private function buildPattern(string $uri): string
    {
        // Convert {param} placeholders to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    private function callAction(string $action, array $params): void
    {
        [$controllerName, $method] = explode('@', $action);
        $class = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($class)) {
            throw new RuntimeException("Controller {$class} not found.");
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Method {$method} not found in {$class}.");
        }

        call_user_func_array([$controller, $method], $params);
    }
}
