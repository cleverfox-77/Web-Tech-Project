<?php
session_start();
require_once 'moderator/config/Database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $db   = new Database();
    $conn = $db->getConnection();

    $sql  = "SELECT id, name, role, is_active FROM users WHERE username = ? AND password_hash = MD5(?) LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    if ($user && $user['is_active'] == 1 && $user['role'] === 'moderator') {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];
        header("Location: moderator/views/moderator/dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials or insufficient role.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Moderator Login</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
  .box { background: white; padding: 36px 40px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); width: 320px; }
  h2 { margin-top: 0; color: #2c3e50; text-align: center; }
  input { width: 100%; padding: 9px; margin-bottom: 14px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
  button { width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; }
  button:hover { background: #34495e; }
  .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 14px; font-size: 14px; }
</style>
</head>
<body>
<div class="box">
    <h2>Moderator Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="text"     name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>