<?php

declare(strict_types=1);

namespace Lime;

class Migration
{
    private static function db(): \PDO
    {
        return Database::connect();
    }

    public static function run(): void
    {
        self::ensureTable();

        $dir = APP_PATH . 'Database/Migrations/';
        $files = glob($dir . '*.sql');
        if ($files === false) {
            return;
        }
        sort($files);

        $executed = self::executed();

        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $executed, true)) {
                continue;
            }

            $sql = file_get_contents($file);
            if ($sql === false || trim($sql) === '') {
                continue;
            }

            try {
                self::db()->exec($sql);
                self::db()->prepare('INSERT INTO migrations (name) VALUES (?)')
                    ->execute([$name]);
                echo "Migrated: {$name}\n";
            } catch (\PDOException $e) {
                echo "Error: {$name} — {$e->getMessage()}\n";
            }
        }
    }

    public static function reset(): void
    {
        self::ensureTable();
        self::db()->exec('DROP TABLE IF EXISTS migrations');
        echo "Migrations reset.\n";
    }

    private static function ensureTable(): void
    {
        self::db()->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ');
    }

    private static function executed(): array
    {
        $rows = Database::fetchAll('SELECT name FROM migrations ORDER BY id');
        return array_column($rows, 'name');
    }
}
