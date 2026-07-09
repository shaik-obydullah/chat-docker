<?php

declare(strict_types=1);

namespace App\Controller;

use Lime\Controller;
use Lime\Database;

class ChatController extends Controller
{
    private static function encryptFile(string $data): string
    {
        $key = hash('sha256', $_ENV['APP_KEY'] ?? 'lime-default-key', true);
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        return $iv . openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }

    private static function decryptFile(string $data): string
    {
        $key = hash('sha256', $_ENV['APP_KEY'] ?? 'lime-default-key', true);
        $ivLen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLen);
        $ciphertext = substr($data, $ivLen);
        return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }
    public function index(): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $this->view('chat/index', [
            'user' => [
                'id'    => $_SESSION['user_id'],
                'name'  => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
            ],
        ]);
    }

    public function contacts(): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $userId = (int) $_SESSION['user_id'];

        $rows = Database::fetchAll('
            SELECT u.id, u.name, u.email, u.avatar_color, u.status,
                   (SELECT m.message FROM messages m
                    WHERE (m.sender_id = u.id AND m.receiver_id = ?)
                       OR (m.sender_id = ? AND m.receiver_id = u.id)
                    ORDER BY m.created_at DESC LIMIT 1
                   ) AS last_msg
            FROM contacts c
            JOIN users u ON u.id = c.contact_id
            WHERE c.user_id = ?
            ORDER BY u.name
        ', [$userId, $userId, $userId]);

        $this->json($rows);
    }

    public function messages(string $contactId): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $userId = (int) $_SESSION['user_id'];
        $contactId = (int) $contactId;
        $after = (int) ($_GET['after'] ?? 0);

        $sql = '
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE ((m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?))
        ';
        $params = [$userId, $contactId, $contactId, $userId];

        if ($after > 0) {
            $sql .= ' AND m.id > ?';
            $params[] = $after;
        }

        $sql .= ' ORDER BY m.created_at ASC';

        $rows = Database::fetchAll($sql, $params);

        $this->json($rows);
    }

    public function send(): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $userId = (int) $_SESSION['user_id'];
        $receiverId = (int) ($_POST['receiver_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $type = $_POST['type'] ?? 'text';

        if ($receiverId < 1 || $message === '') {
            $this->json(['error' => 'Invalid data'], 400);
        }

        $id = Database::insert(
            'INSERT INTO messages (sender_id, receiver_id, message, type) VALUES (?, ?, ?, ?)',
            [$userId, $receiverId, $message, $type]
        );

        $this->json(['id' => $id, 'status' => 'ok']);
    }

    public function uploadVoice(): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $userId = (int) $_SESSION['user_id'];
        $receiverId = (int) ($_POST['receiver_id'] ?? 0);

        if ($receiverId < 1 || !isset($_FILES['audio'])) {
            $this->json(['error' => 'Invalid data'], 400);
        }

        $file = $_FILES['audio'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'Upload failed'], 400);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'webm';
        $name = 'voice_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir = BASE_PATH . 'storage/voice/';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dest = $dir . $name;

        $raw = file_get_contents($file['tmp_name']);
        if ($raw === false || file_put_contents($dest, self::encryptFile($raw)) === false) {
            $this->json(['error' => 'Save failed'], 500);
        }

        $url = '/voice/' . $name;

        Database::insert(
            'INSERT INTO messages (sender_id, receiver_id, message, type) VALUES (?, ?, ?, ?)',
            [$userId, $receiverId, $url, 'voice']
        );

        $this->json(['url' => $url, 'status' => 'ok']);
    }

    public function addContact(): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $userId = (int) $_SESSION['user_id'];
        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            $this->json(['error' => 'Email is required'], 400);
        }

        $contact = Database::fetch('SELECT id, name FROM users WHERE email = ?', [$email]);

        if (!$contact) {
            $this->json(['error' => 'User not found'], 404);
        }

        $contactId = (int) $contact['id'];

        if ($contactId === $userId) {
            $this->json(['error' => 'Cannot add yourself'], 400);
        }

        $exists = Database::fetch(
            'SELECT id FROM contacts WHERE user_id = ? AND contact_id = ?',
            [$userId, $contactId]
        );

        if ($exists) {
            $this->json(['error' => 'Contact already added'], 409);
        }

        Database::execute(
            'INSERT IGNORE INTO contacts (user_id, contact_id) VALUES (?, ?), (?, ?)',
            [$userId, $contactId, $contactId, $userId]
        );

        $this->json(['status' => 'ok', 'contact' => $contact]);
    }

    public function serveVoice(string $filename): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo 'Unauthorized';
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $url = '/voice/' . $filename;

        $msg = Database::fetch(
            'SELECT id, sender_id, receiver_id, message FROM messages WHERE message = ? AND type = ?',
            [$url, 'voice']
        );

        if (!$msg || ($msg['sender_id'] != $userId && $msg['receiver_id'] != $userId)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        $path = BASE_PATH . 'storage/voice/' . basename($filename);

        if (!file_exists($path)) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        $encrypted = file_get_contents($path);
        if ($encrypted === false) {
            http_response_code(500);
            echo 'Read failed';
            exit;
        }

        $decrypted = self::decryptFile($encrypted);

        header('Content-Type: audio/webm');
        header('Content-Length: ' . strlen($decrypted));
        header('Cache-Control: private, max-age=86400');
        echo $decrypted;
        exit;
    }
}
