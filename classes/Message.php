<?php
class Message {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function send($senderId, $receiverId, $message = null, $audioUrl = null) {
        $stmt = $this->db->prepare(
            "INSERT INTO messages (sender_id, receiver_id, message, audio_url) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("iiss", $senderId, $receiverId, $message, $audioUrl);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getMessages($userId, $friendId) {
        $stmt = $this->db->prepare(
            "SELECT id, sender_id, receiver_id, message, audio_url, timestamp
             FROM messages
             WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
             ORDER BY timestamp"
        );
        $stmt->bind_param("iiii", $userId, $friendId, $friendId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $messages;
    }
}
