<?php

declare(strict_types=1);

namespace Lime;

class View
{
    public static function render(string $path, array $data = []): void
    {
        $file = APP_PATH . 'View' . DIRECTORY_SEPARATOR . $path . '.php';

        if (!file_exists($file)) {
            http_response_code(404);
            echo "View not found: {$path}";
            exit;
        }

        extract($data);
        require $file;
    }
}
