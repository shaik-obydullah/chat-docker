<?php

declare(strict_types=1);

define('LIME_VERSION', '1.0.0');
define('APP_PATH', BASE_PATH . 'App' . DIRECTORY_SEPARATOR);
define('SYSTEM_PATH', BASE_PATH . 'System' . DIRECTORY_SEPARATOR);

$envFile = BASE_PATH . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2) + [null, null];
        if ($key !== null) {
            $_ENV[trim($key)] = trim($value ?? '');
        }
    }
}

spl_autoload_register(function (string $class) {
    $prefixes = [
        'Lime\\' => SYSTEM_PATH,
        'App\\'  => APP_PATH,
    ];

    foreach ($prefixes as $prefix => $dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Lime\Router::loadRoutes(APP_PATH . 'Router/web.php');

$router = new Lime\Router();
$router->dispatch();
