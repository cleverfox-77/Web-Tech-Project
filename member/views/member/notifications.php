<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/NotificationController.php';
$controller = new NotificationController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 800px; margin: 28px auto; padding: 0 16px; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 6px; margin-bottom: 14px; }
        .notif-item { padding: 12px 14px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .notif-item:last-child { border-bottom: none; }
        .notif-item.unread { background: #f0f7ff; }
        .notif-msg { font-size: 14px; color: #333; }
        .notif-meta { font-size: 12px; color: #888; margin-top: 4px; }
        .btn { padding: 5px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-primary { background: #2c3e50; color: white; }
        .btn-mark-all { padding: 8px 18px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; margin-bottom: 14px; }
        .no-data { color: #aaa; font-style: italic; text-align: center; padding: 30px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong></span>
    <div>
        <a href="dashboard.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="../../logout.php">Logout</a>
    </div>
</div>
<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <h2 style="color:#2c3e50;">Notifications <small style="font-size:14px;color:#888;">(<?php echo $data['unread']; ?> unread)</small></h2>

    <div class="panel">
        <?php if ($data['unread'] > 0): ?>
        <!-- Mark all as read via AJAX -->
        <button class="btn-mark-all" onclick="markAllRead()">Mark All as Read (AJAX)</button>
        <span id="markMsg" style="font-size:13px;color:#27ae60;margin-left:10px;"></span>
        <?php endif; ?>

        <?php if (count($data['notifications']) == 0): ?>
            <p class="no-data">No notifications yet.</p>
        <?php else: ?>
        <?php foreach ($data['notifications'] as $n): ?>
        <div class="notif-item <?php echo !$n['is_read'] ? 'unread' : ''; ?>" id="notif_<?php echo $n['id']; ?>">
            <div>
                <div class="notif-msg"><?php echo htmlspecialchars($n['message']); ?></div>
                <div class="notif-meta"><?php echo date('d M Y, H:i', strtotime($n['created_at'])); ?> &mdash; <?php echo htmlspecialchars($n['type']); ?></div>
            </div>
            <div>
                <?php if ($n['link']): ?>
                    <a href="<?php echo htmlspecialchars($n['link']); ?>" class="btn btn-primary">View</a>
                <?php endif; ?>
                <?php if (!$n['is_read']): ?>
                <button class="btn" style="background:#95a5a6;color:white;margin-left:6px;"
                    onclick="markRead(<?php echo $n['id']; ?>)">Mark Read</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Mark one notification as read via XMLHttpRequest
function markRead(id) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.success) {
                var el = document.getElementById('notif_' + id);
                if (el) { el.className = 'notif-item'; }
            }
        }
    };
    xhttp.open("POST", "../../api/member_ajax.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send("action=mark_notif_read&notif_id=" + id);
}

// Mark all as read via XMLHttpRequest
function markAllRead() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.success) {
                var items = document.querySelectorAll('.notif-item.unread');
                for (var i = 0; i < items.length; i++) { items[i].className = 'notif-item'; }
                document.getElementById('markMsg').innerHTML = '&#10003; All marked as read.';
            }
        }
    };
    xhttp.open("POST", "../../api/member_ajax.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send("action=mark_all_read");
}
</script>
</body>
</html>
