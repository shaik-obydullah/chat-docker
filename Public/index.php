<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$blockedPaths = ['/App', '/System', '/vendor', '/.env'];
foreach ($blockedPaths as $path) {
    if (str_starts_with($requestUri, $path)) {
        http_response_code(404);
        exit;
    }
}

require_once BASE_PATH . 'System/Bootstrap.php';
