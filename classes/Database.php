<?php
class Database {
    private static $instance = null;
    private $connection;

    private $servername = "chat_server";
    private $username = "root";
    private $password = "root";
    private $dbname = "chat_user";

    private function __construct() {
        $this->connection = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8mb4");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}