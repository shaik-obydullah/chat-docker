<?php

declare(strict_types=1);

namespace Lime;

class Router
{
    private static array $routes = [];
    private string $controllerNamespace = 'App\\Controller\\';

    public static function get(string $uri, string $handler): void
    {
        self::$routes['GET'][$uri] = $handler;
    }

    public static function post(string $uri, string $handler): void
    {
        self::$routes['POST'][$uri] = $handler;
    }

    public static function match(array $methods, string $uri, string $handler): void
    {
        foreach ($methods as $method) {
            self::$routes[strtoupper($method)][$uri] = $handler;
        }
    }

    public static function loadRoutes(string $file): void
    {
        if (file_exists($file)) {
            require $file;
        }
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        [$handler, $params] = $this->matchRoute($method, $uri);

        if ($handler === null) {
            http_response_code(404);
            echo '404 - Route not found';
            exit;
        }

        [$controller, $action] = explode('@', $handler);
        $className = $this->controllerNamespace . $controller;

        if (!class_exists($className)) {
            http_response_code(404);
            echo '404 - Controller not found';
            exit;
        }

        $instance = new $className();

        if (!method_exists($instance, $action)) {
            http_response_code(404);
            echo '404 - Method not found';
            exit;
        }

        $instance->$action(...$params);
    }

    private function matchRoute(string $method, string $uri): array
    {
        foreach (self::$routes[$method] ?? [] as $pattern => $handler) {
            $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                return [$handler, $matches];
            }
        }

        return [null, []];
    }
}
