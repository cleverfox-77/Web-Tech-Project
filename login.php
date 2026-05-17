<?php
/*
 * login.php — Admin login page
 * Place this file at: htdocs/your-project/login.php
 * Accessible at: http://localhost/your-project/login.php
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        $db   = new Database();
        $conn = $db->getConnection();

        $sql  = "SELECT id, name, role, is_active FROM users
                 WHERE username = ? AND password_hash = MD5(?) LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && $user['is_active'] == 1 && $user['role'] === 'admin') {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            header("Location: /admin/views/admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid credentials or insufficient role.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login &mdash; Community Forum</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5;
               display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 38px 44px; border-radius: 10px;
               box-shadow: 0 2px 12px rgba(0,0,0,0.13); width: 340px; }
        .box h2 { margin: 0 0 6px; color: #1a252f; text-align: center; }
        .box p  { margin: 0 0 24px; color: #888; text-align: center; font-size: 13px; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #ccc;
                border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        input:focus { outline: none; border-color: #1a252f; }
        button { width: 100%; padding: 11px; background: #1a252f; color: white; border: none;
                 border-radius: 4px; font-size: 15px; cursor: pointer; }
        button:hover { background: #2c3e50; }
        .error { background: #f8d7da; color: #721c24; padding: 10px 14px;
                 border-radius: 4px; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Admin Login</h2>
    <p>Community Forum Administration</p>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter username" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
// Session cleared — redirect to login
</html>
