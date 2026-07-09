<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
</head>
<body style="font-family:sans-serif;text-align:center;padding:80px 20px;background:#f0f2f5">
  <h1 style="color:#6366f1"><?= htmlspecialchars($title) ?></h1>
  <p style="color:#6b7280;font-size:18px"><?= htmlspecialchars($message) ?></p>
  <p style="margin-top:40px;color:#9ca3af;font-size:14px">Lime PHP Framework v<?= LIME_VERSION ?></p>
</body>
</html>
