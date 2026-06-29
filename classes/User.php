<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($userId, $hashedPassword);
            $stmt->fetch();
            if (password_verify($password, $hashedPassword)) {
                $stmt->close();
                return $userId;
            }
        }
        $stmt->close();
        return false;
    }

    public function getFriends($userId) {
        $stmt = $this->db->prepare("SELECT id, name FROM users WHERE id != ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $friends = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $friends;
    }

    public function getById($userId) {
        $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
}
