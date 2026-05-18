<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../controllers/AuthController.php';
$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ctrl   = new AuthController();
    $result = $ctrl->login();
    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expert Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 36px 42px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.12); width: 340px; }
        h2 { margin: 0 0 4px; color: #1a5276; text-align: center; }
        p.sub { text-align: center; color: #888; font-size: 13px; margin: 0 0 22px; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 4px; }
        input[type=text], input[type=password] { width: 100%; padding: 9px; margin-bottom: 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 10px; background: #1a5276; color: white; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; }
        button:hover { background: #1f618d; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 12px; font-size: 13px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 12px; font-size: 13px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Community Forum</h2>
    <p class="sub">Verified Expert Login</p>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $e): ?>
        <div class="error"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label>Username</label>
        <input type="text" name="username" placeholder="Expert username" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
