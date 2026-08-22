<?php

declare(strict_types=1);

namespace Lime;

abstract class Controller
{
    protected function view(string $path, array $data = []): void
    {
        View::render($path, $data);
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
