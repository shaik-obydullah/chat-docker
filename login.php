<?php
require_once 'classes/Database.php';
require_once 'classes/User.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: chat.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = new User();
    $userId = $user->login($_POST['email'], $_POST['password']);
    if ($userId) {
        $_SESSION['user_id'] = $userId;
        header("Location: chat.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Evis Chat</title>
    <link rel="stylesheet" href="assets/css/base.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">💬</div>
                <h1>Welcome Back</h1>
                <p>Sign in to continue to Evis Chat</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="auth-btn">Sign In</button>
            </form>
            <p class="auth-footer">
                Don't have an account? <a href="register.php">Sign up</a>
            </p>
        </div>
    </div>
</body>
</html>
