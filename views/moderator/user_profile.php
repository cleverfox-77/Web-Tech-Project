<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/UserController.php';

AuthMiddleware::checkModerator();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id == 0) {
    header("Location: dashboard.php");
    exit();
}

$controller = new UserController();
$data = $controller->viewProfile($user_id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile &mdash; Moderation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 16px; }
        h2, h3 { color: #2c3e50; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 22px;
                 box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .profile-header { display: flex; align-items: center; gap: 20px; }
        .profile-header img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc; }
        .profile-header .info h2 { margin: 0 0 4px; }
        .profile-header .info p { margin: 0; color: #666; font-size: 14px; }
        .stats-row { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 16px; }
        .stat-box { background: #f8f9fa; border-radius: 6px; padding: 14px 22px; text-align: center; flex: 1; min-width: 100px; }
        .stat-box .label { font-size: 12px; color: #888; margin-bottom: 4px; }
        .stat-box .value { font-size: 26px; font-weight: bold; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        input[type=number], input[type=text] {
            padding: 8px; border: 1px solid #ccc; border-radius: 4px;
            font-size: 14px; width: 100%; box-sizing: border-box; margin-bottom: 10px; }
        .btn { padding: 9px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn:hover { opacity: 0.9; }
        .badge-active   { display: inline-block; background: #d4edda; color: #155724; padding: 3px 10px; border-radius: 20px; font-size: 12px; }
        .badge-inactive { display: inline-block; background: #f8d7da; color: #721c24; padding: 3px 10px; border-radius: 20px; font-size: 12px; }
        .section-title { font-size: 15px; font-weight: bold; color: #2c3e50; border-bottom: 2px solid #2c3e50;
                         padding-bottom: 6px; margin-bottom: 14px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .no-data { color: #aaa; font-style: italic; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; User Profile</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="report_queue.php">Report Queue</a>
        <a href="warnings.php">Warnings</a>
        <a href="tag_management.php">Tags</a>
        <a href="user_lookup.php">User Lookup</a>
        <a href="../../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (!$data['user']): ?>
        <div class="panel"><p>User not found.</p></div>
    <?php else: ?>

    <!-- Profile Header -->
    <div class="panel">
        <div class="profile-header">
            <img src="<?php echo $data['user']['profile_pic'] ? htmlspecialchars($data['user']['profile_pic']) : '../../assets/default_avatar.png'; ?>" alt="Avatar">
            <div class="info">
                <h2><?php echo htmlspecialchars($data['user']['name']); ?>
                    <small style="font-size:15px;color:#888;">@<?php echo htmlspecialchars($data['user']['username']); ?></small>
                </h2>
                <p>Role: <strong><?php echo ucfirst($data['user']['role']); ?></strong>
                   &mdash; Reputation: <strong><?php echo $data['user']['reputation']; ?></strong>
                   &mdash; Status:
                    <?php if ($data['user']['is_active']): ?>
                        <span class="badge-active">Active</span>
                    <?php else: ?>
                        <span class="badge-inactive">Suspended</span>
                    <?php endif; ?>
                </p>
                <p style="color:#888;font-size:13px;">Member since: <?php echo htmlspecialchars($data['user']['created_at']); ?></p>
                <?php if ($data['user']['bio']): ?>
                    <p><?php echo htmlspecialchars($data['user']['bio']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="label">Questions</div>
                <div class="value"><?php echo $data['stats']['questions']; ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Answers</div>
                <div class="value"><?php echo $data['stats']['answers']; ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Votes Cast</div>
                <div class="value"><?php echo $data['stats']['votes']; ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Warnings</div>
                <div class="value" style="color:#e74c3c;"><?php echo $data['stats']['warnings']; ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Reports Made</div>
                <div class="value"><?php echo $data['stats']['reports_made']; ?></div>
            </div>
        </div>
    </div>

    <!-- Warning History -->
    <div class="panel">
        <div class="section-title">Warning History</div>
        <?php if (count($data['warnings']) == 0): ?>
            <p class="no-data">No warnings issued to this user.</p>
        <?php else: ?>
        <table>
            <tr><th>#</th><th>Reason</th><th>Issued By</th><th>Date</th></tr>
            <?php foreach ($data['warnings'] as $i => $w): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($w['reason']); ?></td>
                <td><?php echo htmlspecialchars($w['mod_username']); ?></td>
                <td><?php echo htmlspecialchars($w['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Suspend / Activate -->
    <div class="panel">
        <div class="section-title">Moderation Actions</div>
        <?php if ($data['user']['is_active']): ?>
        <h3 style="margin-top:0;">Temporarily Suspend User</h3>
        <form method="post" action="../../controllers/UserController.php">
            <input type="hidden" name="action" value="suspend">
            <input type="hidden" name="user_id" value="<?php echo $data['user']['id']; ?>">
            <label style="font-weight:bold;color:#555;">Suspension Duration (days):</label>
            <input type="number" name="days" min="1" max="365" placeholder="e.g. 7" required style="width:200px;">
            <br>
            <button type="submit" class="btn btn-danger"
                onclick="return confirm('Suspend this user?')">Suspend User</button>
        </form>
        <?php else: ?>
        <h3 style="margin-top:0;color:#27ae60;">Re-activate User</h3>
        <form method="post" action="../../controllers/UserController.php">
            <input type="hidden" name="action" value="activate">
            <input type="hidden" name="user_id" value="<?php echo $data['user']['id']; ?>">
            <button type="submit" class="btn btn-success">Re-activate Account</button>
        </form>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>
</body>
</html>
