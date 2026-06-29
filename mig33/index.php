<?php
session_start();
$db = new SQLite3('hack.db');
$db->exec("CREATE TABLE IF NOT EXISTS msgs (id INTEGER PRIMARY KEY AUTOINCREMENT, nick TEXT, msg TEXT, time DATETIME DEFAULT CURRENT_TIMESTAMP)");

$pass = 'hack123';
$logged_in = !empty($_SESSION['hack_auth']);

if (!$logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === $pass) {
        $_SESSION['hack_auth'] = true;
        $_SESSION['hack_nick'] = strip_tags(trim($_POST['login_nick'])) ?: ('anon_' . substr(md5(rand()), 0, 6));
        header('Location: ?');
        exit;
    }
    $err = true;
}

if ($logged_in && isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?');
    exit;
}

$nick = $_SESSION['hack_nick'] ?? 'anon';

if ($logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['setnick'])) {
        $_SESSION['hack_nick'] = strip_tags(trim($_POST['setnick'])) ?: $nick;
        header('Location: ?');
        exit;
    }
    if (!empty($_POST['msg'])) {
        $stmt = $db->prepare("INSERT INTO msgs (nick, msg) VALUES (?, ?)");
        $stmt->bindValue(1, $nick);
        $stmt->bindValue(2, strip_tags(trim($_POST['msg'])));
        $stmt->execute();
        header('Location: ?');
        exit;
    }
}

$msgs = [];
if ($logged_in) {
    $res = $db->query("SELECT * FROM msgs ORDER BY id DESC LIMIT 100");
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) $msgs[] = $row;
    $msgs = array_reverse($msgs);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>// HACK_CHAT</title>

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{
    background:#0a0a0a;
    color:#0f0;
    font-family:"Courier New","Liberation Mono",monospace;
    font-size:16px;
    height:100dvh;
    display:flex;
    flex-direction:column;
    position:relative;
    overflow:hidden;
    -webkit-tap-highlight-color:transparent
}
body::after{
    content:'';
    position:fixed;top:0;left:0;width:100%;height:100%;
    background:repeating-linear-gradient(0deg,rgba(0,255,0,.015)0px,rgba(0,255,0,.015)1px,transparent 1px,transparent 3px);
    pointer-events:none;z-index:9998
}
#term{
    flex:1;display:flex;flex-direction:column;
    border:1px solid #0f0;margin:4px;
    box-shadow:0 0 20px rgba(0,255,0,.06),inset 0 0 20px rgba(0,255,0,.02);
    background:radial-gradient(ellipse at center,rgba(0,20,0,.4)0%,rgba(0,5,0,.8)100%)
}
#header{
    border-bottom:1px solid #0f0;
    padding:12px 14px;display:flex;justify-content:space-between;align-items:center;
    background:rgba(0,20,0,.5);flex-shrink:0;
    font-size:15px;min-height:48px
}
#header .prompt{font-weight:bold;letter-spacing:1px}
#header a{color:#0f0;text-decoration:none;font-size:14px;margin-left:10px;opacity:.6;padding:6px 0}
#header a:hover{opacity:1;text-shadow:0 0 6px #0f0}
#msgs{flex:1;overflow-y:auto;padding:4px 0;scroll-behavior:smooth;-webkit-overflow-scrolling:touch}
.msg{padding:8px 14px;border-bottom:1px solid rgba(0,255,0,.04);line-height:1.5}
.msg .nick{color:#0f0;font-weight:bold;font-size:15px}
.msg .nick::before{content:'<';color:#060}
.msg .nick::after{content:'>';color:#060}
.msg .time{color:#030;font-size:12px;margin-left:8px}
.msg .text{margin-top:3px;padding-left:4px;word-break:break-word;font-size:16px}
.msg .text::before{content:'> ';color:#060}
#input-bar{
    display:flex;border-top:1px solid #0f0;flex-shrink:0;
    background:rgba(0,20,0,.5);min-height:52px
}
#input-bar .prompt{
    color:#060;padding:14px 0 14px 14px;font-size:16px;flex-shrink:0;
    display:flex;align-items:center
}
#input-bar input[type="text"]{
    flex:1;background:transparent;border:none;color:#0f0;font-family:inherit;
    font-size:16px;padding:14px 8px;outline:none;min-height:52px
}
#input-bar input[type="text"]::placeholder{color:#030}
#input-bar button{
    background:transparent;border:none;border-left:1px solid #0f0;
    color:#0f0;font-family:inherit;font-weight:bold;font-size:15px;
    padding:14px 20px;cursor:pointer;min-width:70px
}
#input-bar button:active{background:rgba(0,255,0,.1);text-shadow:0 0 6px #0f0}
#nickbar{
    border-top:1px solid rgba(0,255,0,.15);padding:10px 14px;
    font-size:13px;display:flex;justify-content:space-between;align-items:center;
    background:rgba(0,10,0,.3);flex-shrink:0;flex-wrap:wrap;gap:6px;min-height:44px
}
#nickbar a{color:#060;text-decoration:none;padding:6px 0}
#nickbar a:active{color:#0f0}
#nickbar form{display:flex;align-items:center;gap:4px}
#nickbar input{
    background:#000;border:1px solid #0f0;color:#0f0;font-family:inherit;
    font-size:14px;padding:8px 10px;width:130px;outline:none;min-height:36px
}
#nickbar input::placeholder{color:#030}
#nickbar button{
    background:transparent;border:1px solid #0f0;color:#0f0;
    font-size:13px;padding:8px 12px;cursor:pointer;font-family:inherit;min-height:36px
}
#nickbar button:active{background:rgba(0,255,0,.1)}
.login-box{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    height:100%;padding:24px;gap:12px
}
.login-box h1{font-size:28px;letter-spacing:3px;text-shadow:0 0 10px #0f0;margin-bottom:8px}
.login-box .sub{opacity:.5;font-size:13px;margin-bottom:12px}
.login-box input[type="text"],.login-box input[type="password"]{
    background:#000;border:1px solid #0f0;color:#0f0;font-family:inherit;
    font-size:16px;padding:14px 16px;width:88%;max-width:280px;text-align:center;outline:none;min-height:48px
}
.login-box input:focus{border-color:#0f0;box-shadow:0 0 6px rgba(0,255,0,.3)}
.login-box input[type="submit"]{
    background:#001a00;border:1px solid #0f0;color:#0f0;font-family:inherit;
    font-size:16px;font-weight:bold;padding:14px 36px;cursor:pointer;margin-top:6px;min-height:48px
}
.login-box input[type="submit"]:active{background:#002a00;text-shadow:0 0 6px #0f0}
.login-box .err{color:#f00;text-shadow:0 0 6px #f00;font-size:13px}
::-webkit-scrollbar{width:4px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:#030}
</style>
</head>
<body>
<div id="term">
<?php if (!$logged_in): ?>
<div id="header">
    <span class="prompt">// H4CK_CHAT</span>
    <span></span>
</div>
<div class="login-box">
    <h1>// LOGIN</h1>
    <div class="sub">secure channel • authentication required</div>
    <?php if (isset($err)): ?><div class="err">[ access denied ]</div><?php endif; ?>
    <form method="post">
        <input type="text" name="login_nick" placeholder="nickname" maxlength="20" autocomplete="off" required><br><br>
        <input type="password" name="login_pass" placeholder="password" required><br><br>
        <input type="submit" value="[ authenticate ]">
    </form>
</div>
<?php else: ?>
<div id="header">
    <span class="prompt">// H4CK_CHAT <span style="color:#060;font-weight:normal;font-size:12px">[secure]</span></span>
    <span><a href="?logout=1">[disconnect]</a></span>
</div>
<div id="msgs">
<?php if (empty($msgs)): ?>
    <div class="msg" style="text-align:center;opacity:.3;padding:30px">[ no transmissions yet ]</div>
<?php endif; ?>
<?php foreach ($msgs as $m): ?>
    <div class="msg">
        <span class="nick"><?= htmlspecialchars($m['nick']) ?></span>
        <span class="time"><?= date('H:i:s', strtotime($m['time'])) ?></span>
        <div class="text"><?= htmlspecialchars($m['msg']) ?></div>
    </div>
<?php endforeach; ?>
</div>
<form method="post" id="input-bar">
    <span class="prompt">$ </span>
    <input type="text" name="msg" placeholder="type message..." autocomplete="off" required>
    <button type="submit">[send]</button>
</form>
<div id="nickbar">
    <span>connected as: <strong><?= htmlspecialchars($nick) ?></strong></span>
    <form method="post">
        <input type="text" name="setnick" placeholder="change nick" maxlength="20" autocomplete="off">
        <button type="submit">set</button>
    </form>
</div>
<?php endif; ?>
</div>
</body>
</html>
