<?php

declare(strict_types=1);

namespace Lime;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO(
                sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $_ENV['DB_HOST'] ?? 'chat_server',
                    $_ENV['DB_PORT'] ?? '3306',
                    $_ENV['DB_NAME'] ?? 'chat_user'
                ),
                $_ENV['DB_USER'] ?? 'root',
                $_ENV['DB_PASS'] ?? 'root',
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::connect()->lastInsertId();
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
}
