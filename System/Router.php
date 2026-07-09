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

        $handler = self::$routes[$method][$uri] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '404 - Route not found';
            exit;
        }

        [$controller, $method] = explode('@', $handler);
        $className = $this->controllerNamespace . $controller;

        if (!class_exists($className)) {
            http_response_code(404);
            echo '404 - Controller not found';
            exit;
        }

        $instance = new $className();

        if (!method_exists($instance, $method)) {
            http_response_code(404);
            echo '404 - Method not found';
            exit;
        }

        $instance->$method();
    }
}
