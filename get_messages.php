<?php
require_once 'classes/Database.php';
require_once 'classes/Message.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$friendId = isset($_GET['friend_id']) ? intval($_GET['friend_id']) : 0;

if ($friendId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid friend ID']);
    exit;
}

$msg = new Message();
$messages = $msg->getMessages($userId, $friendId);
echo json_encode($messages);
