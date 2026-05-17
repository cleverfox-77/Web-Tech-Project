<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/AuditLogController.php';
$controller = new AuditLogController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Audit Log &mdash; Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #1a252f; color: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #ccc; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .navbar a:hover { color: white; }
        .container { max-width: 1150px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #1a252f; }
        .panel { background: white; border-radius: 8px; padding: 22px; margin-bottom: 24px;
                 box-shadow: 0 1px 5px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #1a252f; border-bottom: 2px solid #1a252f;
                         padding-bottom: 6px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        tr:hover td { background: #fafafa; }
        .action-tag { display: inline-block; padding: 3px 10px; border-radius: 14px; font-size: 11px; font-weight: bold;
                      background: #eaf2ff; color: #2980b9; }
        .action-tag.promote  { background: #eafaf1; color: #27ae60; }
        .action-tag.demote   { background: #fef9e7; color: #e67e22; }
        .action-tag.suspend  { background: #f8d7da; color: #721c24; }
        .action-tag.lift     { background: #d4edda; color: #155724; }
        .action-tag.reinstate{ background: #fff3cd; color: #856404; }
        .action-tag.badge    { background: #f5eef8; color: #8e44ad; }
        .no-data { color: #aaa; font-style: italic; text-align: center; padding: 30px; }
        .total-badge { display: inline-block; background: #1a252f; color: white;
                       padding: 4px 14px; border-radius: 20px; font-size: 13px; margin-left: 10px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Audit Log</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="moderation_activity.php">Moderation Activity</a>
        <a href="user_management.php">Users</a>
        <a href="../../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Audit Log <span class="total-badge"><?php echo $data['total']; ?> entries</span></h2>

    <div class="panel">
        <div class="section-title">All Significant Admin &amp; Moderator Actions (Latest 200)</div>

        <?php if (count($data['logs']) == 0): ?>
            <p class="no-data">No audit log entries yet. Actions taken by admins will appear here.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Admin</th>
                <th>Action</th>
                <th>Target</th>
                <th>Target ID</th>
                <th>Reason / Note</th>
                <th>Date &amp; Time</th>
            </tr>
            <?php foreach ($data['logs'] as $i => $log): ?>
            <?php
                // Determine tag style class based on action type
                $tagClass = '';
                if (strpos($log['action_type'], 'promote') !== false)   $tagClass = 'promote';
                elseif (strpos($log['action_type'], 'demote') !== false) $tagClass = 'demote';
                elseif (strpos($log['action_type'], 'suspend') !== false || strpos($log['action_type'], 'deactivate') !== false) $tagClass = 'suspend';
                elseif (strpos($log['action_type'], 'lift') !== false || strpos($log['action_type'], 'activate') !== false) $tagClass = 'lift';
                elseif (strpos($log['action_type'], 'reinstate') !== false) $tagClass = 'reinstate';
                elseif (strpos($log['action_type'], 'badge') !== false)  $tagClass = 'badge';
            ?>
            <tr>
                <td style="color:#aaa;"><?php echo $i + 1; ?></td>
                <td>
                    <?php echo htmlspecialchars($log['admin_name']); ?><br>
                    <small style="color:#888;">@<?php echo htmlspecialchars($log['admin_username']); ?></small>
                </td>
                <td>
                    <span class="action-tag <?php echo $tagClass; ?>">
                        <?php echo htmlspecialchars(str_replace('_', ' ', $log['action_type'])); ?>
                    </span>
                </td>
                <td><?php echo ucfirst(str_replace('_', ' ', $log['target_type'])); ?></td>
                <td><?php echo $log['target_id'] > 0 ? '#' . $log['target_id'] : '—'; ?></td>
                <td><?php echo htmlspecialchars($log['reason']); ?></td>
                <td><?php echo htmlspecialchars($log['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
