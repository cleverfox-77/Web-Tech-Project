<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ModeratorDashboardController.php';
$controller = new ModeratorDashboardController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Moderator Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 18px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #2c3e50; }
        .stats-grid { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 6px; padding: 24px 32px; flex: 1; min-width: 180px;
                     box-shadow: 0 2px 6px rgba(0,0,0,0.08);
                      text-align: center; 
                    }
        .stat-card h3 { margin: 0 0 8px; font-size: 15px; color: #555; }
        .stat-card .number { font-size: 38px; font-weight: bold; color: #2c3e50; }
        .stat-card.red .number  { color: #e74c3c; }
        .stat-card.blue .number { color: #2980b9; }
        .stat-card.green .number { color: #27ae60; }
        .quick-links { background: white; border-radius: 6px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .quick-links h3 { margin-top: 0; color: #2c3e50; }
        .quick-links a { display: inline-block; background: #2c3e50; color: white; padding: 10px 20px;
                         border-radius: 4px; text-decoration: none; margin: 6px; }
        .quick-links a:hover { background: #34495e; }
        .pending-breakdown { background: white; border-radius: 6px; padding: 20px; margin-top: 20px;
                             box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .pending-breakdown h3 { margin-top: 0; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Moderator Panel</span>
    <div>
        <a href="report_queue.php">Report Queue</a>
        <a href="warnings.php">Warnings</a>
        <a href="tag_management.php">Tags</a>
        <a href="user_lookup.php">User Lookup</a>
        <a href="activity_report.php">Reports</a>
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

    <h2>Welcome, <?php echo htmlspecialchars($data['mod_name']); ?></h2>

    <div class="stats-grid">
        <div class="stat-card red">
            <h3>Pending Reports</h3>
            <div class="number"><?php echo $data['pending_total']; ?></div>
        </div>
        <div class="stat-card blue">
            <h3>Content Edited Today</h3>
            <div class="number"><?php echo $data['edits_today']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Warnings This Week</h3>
            <div class="number"><?php echo $data['warnings_week']; ?></div>
        </div>
        <div class="stat-card green">
            <h3>New Users This Week</h3>
            <div class="number"><?php echo $data['new_users_week']; ?></div>
        </div>
    </div>

    <div class="pending-breakdown">
        <h3>Pending Reports by Type</h3>
        <table>
            <tr><th>Entity Type</th><th>Pending Count</th></tr>
            <?php
            $types = array('question', 'answer', 'comment');
            foreach ($types as $type):
                $cnt = isset($data['pending_by_type'][$type]) ? $data['pending_by_type'][$type] : 0;
            ?>
            <tr>
                <td><?php echo ucfirst($type); ?></td>
                <td><?php echo $cnt; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="quick-links" style="margin-top:20px;">
        <h3>Quick Actions</h3>
        <a href="report_queue.php">View Report Queue</a>
        <a href="warnings.php">Warning History</a>
        <a href="tag_management.php">Manage Tags</a>
        <a href="user_lookup.php">User Lookup</a>
        <a href="activity_report.php">Generate Activity Report</a>
    </div>
</div>
</body>
</html>
