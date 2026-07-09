<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat - Login</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body class="login-body">
  <div class="login-container">
    <div class="login-card">
      <div class="login-brand">
        <div class="login-logo">C</div>
        <h1>Chat</h1>
        <p class="login-subtitle">Sign in to continue</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div style="background:#fef2f2;color:#991b1b;padding:10px 14px;border-radius:10px;margin-bottom:16px;font-size:13px;text-align:center">
          <?php foreach ($errors as $field => $msgs): ?>
            <?php foreach ((array) $msgs as $msg): ?>
              <div><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="login-form" method="post" action="/login">
        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required autofocus>
        </div>
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="login-btn">Sign In</button>
      </form>
      <p class="login-footer">Demo: <strong>sarah@example.com</strong> / <strong>password</strong></p>
    </div>
  </div>
</body>
</html>
