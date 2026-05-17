<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/UserManagementController.php';

AuthMiddleware::checkAdmin();

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id == 0) {
    header("Location: user_management.php");
    exit();
}

$controller = new UserManagementController();
$data = $controller->viewProfile($user_id);

if (!$data['user']) {
    header("Location: user_management.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile &mdash; Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #1a252f; color: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #ccc; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .navbar a:hover { color: white; }
        .container { max-width: 950px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #1a252f; }
        .panel { background: white; border-radius: 8px; padding: 22px; margin-bottom: 22px;
                 box-shadow: 0 1px 5px rgba(0,0,0,0.09); }
        .profile-header { display: flex; align-items: center; gap: 22px; }
        .avatar { width: 76px; height: 76px; border-radius: 50%; background: #1a252f;
                  color: white; display: flex; align-items: center; justify-content: center;
                  font-size: 28px; font-weight: bold; flex-shrink: 0; }
        .profile-header .info h2 { margin: 0 0 4px; }
        .profile-header .info p  { margin: 0; color: #666; font-size: 14px; }
        .stats-row { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 18px; }
        .stat-box { background: #f8f9fa; border-radius: 6px; padding: 14px 20px; text-align: center; flex: 1; min-width: 100px; }
        .stat-box .lbl { font-size: 12px; color: #888; margin-bottom: 4px; }
        .stat-box .val { font-size: 26px; font-weight: bold; color: #1a252f; }
        .section-title { font-size: 15px; font-weight: bold; color: #1a252f; border-bottom: 2px solid #1a252f;
                         padding-bottom: 6px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-member    { background: #eaf2ff; color: #2980b9; }
        .badge-expert    { background: #eafaf1; color: #27ae60; }
        .badge-moderator { background: #fef9e7; color: #e67e22; }
        .badge-admin     { background: #f5eef8; color: #8e44ad; }
        .badge-active    { background: #d4edda; color: #155724; }
        .badge-inactive  { background: #f8d7da; color: #721c24; }
        .btn { padding: 9px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn-warning { background: #e67e22; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn:hover { opacity: 0.88; }
        .action-form { display: inline; }
        .rep-pos { color: #27ae60; font-weight: bold; }
        .rep-neg { color: #e74c3c; font-weight: bold; }
        .no-data { color: #aaa; font-style: italic; text-align: center; padding: 20px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; User Profile</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="user_management.php">&larr; All Users</a>
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

    <?php $u = $data['user']; ?>

    <!-- Profile Header -->
    <div class="panel">
        <div class="profile-header">
            <div class="avatar"><?php echo strtoupper(substr($u['name'], 0, 1)); ?></div>
            <div class="info">
                <h2><?php echo htmlspecialchars($u['name']); ?>
                    <small style="font-size:14px;color:#888;font-weight:normal;">
                        @<?php echo htmlspecialchars($u['username']); ?>
                    </small>
                </h2>
                <p>
                    <span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span>
                    &nbsp;
                    <?php if ($u['is_active']): ?>
                        <span class="badge badge-active">Active</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Suspended</span>
                    <?php endif; ?>
                    &nbsp;&mdash;&nbsp;
                    <?php echo htmlspecialchars($u['email']); ?>
                    &nbsp;&mdash;&nbsp;
                    Member since: <?php echo htmlspecialchars($u['created_at']); ?>
                </p>
                <?php if ($u['bio']): ?>
                    <p style="margin-top:6px;color:#555;font-size:13px;"><?php echo htmlspecialchars($u['bio']); ?></p>
                <?php endif; ?>
                <?php if ($u['expert_domain']): ?>
                    <p style="margin-top:4px;color:#2980b9;font-size:13px;">Domain: <?php echo htmlspecialchars($u['expert_domain']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-box">
                <div class="lbl">Reputation</div>
                <div class="val"><?php echo $u['reputation']; ?></div>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="panel">
        <div class="section-title">Admin Actions</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if ($u['role'] == 'member'): ?>
            <form class="action-form" method="post" action="../../controllers/UserManagementController.php">
                <input type="hidden" name="action" value="promote_expert">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <button type="submit" class="btn btn-success" onclick="return confirm('Promote to Expert?')">&#8593; Promote to Expert</button>
            </form>
            <form class="action-form" method="post" action="../../controllers/UserManagementController.php">
                <input type="hidden" name="action" value="promote_moderator">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <button type="submit" class="btn btn-warning" onclick="return confirm('Promote to Moderator?')">&#8593; Promote to Moderator</button>
            </form>
            <?php elseif ($u['role'] == 'expert' || $u['role'] == 'moderator'): ?>
            <form class="action-form" method="post" action="../../controllers/UserManagementController.php">
                <input type="hidden" name="action" value="demote_member">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <button type="submit" class="btn btn-secondary" onclick="return confirm('Demote to Member?')">&#8595; Demote to Member</button>
            </form>
            <?php endif; ?>

            <?php if ($u['is_active']): ?>
            <form class="action-form" method="post" action="../../controllers/UserManagementController.php">
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Deactivate this account?')">Deactivate Account</button>
            </form>
            <?php else: ?>
            <form class="action-form" method="post" action="../../controllers/UserManagementController.php">
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                <button type="submit" class="btn btn-success">Activate Account</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reputation History -->
    <div class="panel">
        <div class="section-title">Reputation History (Last 20 events)</div>
        <?php if (count($data['history']) == 0): ?>
            <p class="no-data">No reputation events found.</p>
        <?php else: ?>
        <table>
            <tr><th>Type</th><th>Entity Type</th><th>Value</th><th>Date</th></tr>
            <?php foreach ($data['history'] as $h): ?>
            <tr>
                <td><?php echo $h['value'] > 0 ? 'Upvote received' : 'Downvote received'; ?></td>
                <td><?php echo ucfirst($h['entity_type']); ?></td>
                <td>
                    <?php if ($h['value'] > 0): ?>
                        <span class="rep-pos">+<?php echo $h['value']; ?></span>
                    <?php else: ?>
                        <span class="rep-neg"><?php echo $h['value']; ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($h['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Badges -->
    <div class="panel">
        <div class="section-title">Badges Earned</div>
        <?php if (count($data['badges']) == 0): ?>
            <p class="no-data">No badges awarded yet.</p>
        <?php else: ?>
        <table>
            <tr><th>Icon</th><th>Badge</th><th>Description</th><th>Awarded</th></tr>
            <?php foreach ($data['badges'] as $b): ?>
            <tr>
                <td style="font-size:20px;"><?php echo htmlspecialchars($b['icon']); ?></td>
                <td><?php echo htmlspecialchars($b['name']); ?></td>
                <td><?php echo htmlspecialchars($b['description']); ?></td>
                <td><?php echo htmlspecialchars($b['awarded_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
