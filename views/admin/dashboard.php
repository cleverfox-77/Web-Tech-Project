<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/AdminDashboardController.php';
$controller = new AdminDashboardController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #1a252f; color: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; }
        .navbar span strong { font-size: 17px; }
        .navbar a { color: #ccc; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .navbar a:hover { color: white; }
        .container { max-width: 1150px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #1a252f; margin-bottom: 6px; }
        .sub { color: #888; font-size: 14px; margin-bottom: 24px; }
        .stats-grid { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 28px; }
        .stat-card { background: white; border-radius: 8px; padding: 22px 28px; flex: 1; min-width: 160px;
                     box-shadow: 0 1px 5px rgba(0,0,0,0.09); border-top: 4px solid #1a252f; text-align: center; }
        .stat-card h3 { margin: 0 0 8px; font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .number { font-size: 40px; font-weight: bold; color: #1a252f; }
        .stat-card.blue  { border-color: #2980b9; } .stat-card.blue  .number { color: #2980b9; }
        .stat-card.green { border-color: #27ae60; } .stat-card.green .number { color: #27ae60; }
        .stat-card.orange{ border-color: #e67e22; } .stat-card.orange .number { color: #e67e22; }
        .stat-card.red   { border-color: #e74c3c; } .stat-card.red   .number { color: #e74c3c; }
        .stat-card.purple{ border-color: #8e44ad; } .stat-card.purple .number { color: #8e44ad; }
        .roles-panel { background: white; border-radius: 8px; padding: 20px; margin-bottom: 24px;
                       box-shadow: 0 1px 5px rgba(0,0,0,0.09); }
        .roles-panel h3 { margin-top: 0; color: #1a252f; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .announcement-box { background: #fff8e1; border-left: 5px solid #f39c12; padding: 14px 18px;
                            border-radius: 4px; margin-bottom: 24px; font-size: 14px; }
        .announcement-box strong { color: #e67e22; }
        .quick-nav { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 28px; }
        .quick-nav a { display: block; background: #1a252f; color: white; padding: 12px 22px;
                       border-radius: 6px; text-decoration: none; font-size: 14px; }
        .quick-nav a:hover { background: #2c3e50; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Admin Panel</span>
    <div>
        <a href="user_management.php">Users</a>
        <a href="expert_applications.php">Applications</a>
        <a href="moderation_activity.php">Moderation</a>
        <a href="platform_settings.php">Settings</a>
        <a href="analytics_report.php">Analytics</a>
        <a href="audit_log.php">Audit Log</a>
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

    <h2>Welcome, <?php echo htmlspecialchars($data['admin_name']); ?></h2>
    <p class="sub">Platform administration overview</p>

    <?php if ($data['announcement']): ?>
    <div class="announcement-box">
        <strong>Current Announcement:</strong> <?php echo htmlspecialchars($data['announcement']); ?>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card blue">
            <h3>Total Users</h3>
            <div class="number"><?php echo $data['total_users']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Questions</h3>
            <div class="number"><?php echo $data['total_questions']; ?></div>
        </div>
        <div class="stat-card green">
            <h3>Total Answers</h3>
            <div class="number"><?php echo $data['total_answers']; ?></div>
        </div>
        <div class="stat-card orange">
            <h3>Questions Today</h3>
            <div class="number"><?php echo $data['questions_today']; ?></div>
        </div>
        <div class="stat-card purple">
            <h3>Active Q&A Sessions</h3>
            <div class="number"><?php echo $data['active_sessions']; ?></div>
        </div>
        <div class="stat-card red">
            <h3>Pending Applications</h3>
            <div class="number"><?php echo $data['pending_apps']; ?></div>
        </div>
    </div>

    <div class="roles-panel">
        <h3>Users by Role</h3>
        <table>
            <tr><th>Role</th><th>Count</th></tr>
            <?php
            $roles = array('member', 'expert', 'moderator', 'admin');
            foreach ($roles as $role):
                $cnt = isset($data['role_counts'][$role]) ? $data['role_counts'][$role] : 0;
            ?>
            <tr>
                <td><?php echo ucfirst($role); ?></td>
                <td><?php echo $cnt; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="quick-nav">
        <a href="user_management.php">Manage Users</a>
        <a href="expert_applications.php">Expert Applications</a>
        <a href="moderation_activity.php">Moderation Activity</a>
        <a href="platform_settings.php">Platform Settings</a>
        <a href="analytics_report.php">View Analytics</a>
        <a href="audit_log.php">Audit Log</a>
    </div>
</div>

</body>
</html>
