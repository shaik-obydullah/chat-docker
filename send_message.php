<?php
require_once 'classes/Database.php';
require_once 'classes/Message.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$senderId = $_SESSION['user_id'];
$receiverId = $_POST['receiver_id'] ?? null;
$message = $_POST['message'] ?? null;
$audio = $_FILES['audio'] ?? null;

if (!$receiverId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Receiver ID is required']);
    exit;
}

$audioUrl = null;
if ($audio && $audio['error'] === UPLOAD_ERR_OK) {
    $audioUrl = 'uploads/' . uniqid() . '.wav';
    if (!move_uploaded_file($audio['tmp_name'], $audioUrl)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload audio file']);
        exit;
    }
}

$msg = new Message();
if ($msg->send($senderId, $receiverId, $message, $audioUrl)) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
}
