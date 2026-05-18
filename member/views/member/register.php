<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../controllers/AuthController.php';

$errors = array();
$old    = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ctrl   = new AuthController();
    $result = $ctrl->register();
    $errors = $result['errors'];
    $old    = $result['old'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px 0; }
        .box { background: white; padding: 36px 42px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.12); width: 380px; }
        h2 { margin: 0 0 22px; color: #2c3e50; text-align: center; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 4px; }
        input[type=text], input[type=email], input[type=password] {
            width: 100%; padding: 9px; margin-bottom: 14px; border: 1px solid #ccc;
            border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        input.error-field { border-color: #e74c3c; }
        button { width: 100%; padding: 10px; background: #2c3e50; color: white; border: none;
                 border-radius: 4px; font-size: 15px; cursor: pointer; }
        button:hover { background: #34495e; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 13px; }
        .field-msg { font-size: 12px; margin-top: -10px; margin-bottom: 10px; }
        .field-msg.ok  { color: #27ae60; }
        .field-msg.bad { color: #e74c3c; }
        .link { text-align: center; margin-top: 14px; font-size: 13px; }
        .link a { color: #2c3e50; }
    </style>
</head>
<body>
<div class="box">
    <h2>Create Account</h2>
    <?php foreach ($errors as $e): ?>
        <div class="error"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label>Full Name *</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars(isset($old['name']) ? $old['name'] : ''); ?>" required>

        <label>Username *</label>
        <input type="text" name="username" id="username"
               value="<?php echo htmlspecialchars(isset($old['username']) ? $old['username'] : ''); ?>"
               autocomplete="off" required>
        <div class="field-msg" id="username_msg"></div>

        <label>Email *</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars(isset($old['email']) ? $old['email'] : ''); ?>" required>

        <label>Password *</label>
        <input type="password" name="password" required>

        <label>Confirm Password *</label>
        <input type="password" name="confirm" required>

        <button type="submit">Register</button>
    </form>
    <div class="link"><a href="login.php">Already have an account? Login</a></div>
</div>

<script>
// Live username availability check via XMLHttpRequest (AJAX)
var usernameInput = document.getElementById('username');
var usernameMsg   = document.getElementById('username_msg');
var checkTimer    = null;

usernameInput.onkeyup = function() {
    var username = this.value.trim();
    clearTimeout(checkTimer);
    usernameMsg.innerHTML = '';
    if (username.length < 3) { return; }

    checkTimer = setTimeout(function() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var response = JSON.parse(this.responseText);
                if (response.exists) {
                    usernameMsg.className = 'field-msg bad';
                    usernameMsg.innerHTML = '&#10007; Username is already taken.';
                } else {
                    usernameMsg.className = 'field-msg ok';
                    usernameMsg.innerHTML = '&#10003; Username is available.';
                }
            }
        };
        xhttp.open("GET", "../../api/member_ajax.php?action=check_username&username=" + encodeURIComponent(username), true);
        xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhttp.send();
    }, 400);
};
</script>
</body>
</html>
