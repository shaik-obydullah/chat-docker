<?php
require_once 'classes/Database.php';
require_once 'classes/User.php';

session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $user = new User();
        if ($user->register($name, $email, $password)) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed. Email may already be in use.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Evis Chat</title>
    <link rel="stylesheet" href="assets/css/base.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">💬</div>
                <h1>Create Account</h1>
                <p>Get started with your free account</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="you@example.com" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Create a password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm</label>
                        <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm password" required>
                    </div>
                </div>
                <button type="submit" class="auth-btn">Create Account</button>
            </form>
            <p class="auth-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
