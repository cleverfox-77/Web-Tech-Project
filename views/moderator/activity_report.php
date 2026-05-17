<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ReportGeneratorController.php';
$controller = new ReportGeneratorController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Moderation Activity Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 16px; }
        h2, h3 { color: #2c3e50; }
        .panel { background: white; border-radius: 6px; padding: 24px; margin-bottom: 24px;
                 box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .section-title { font-size: 15px; font-weight: bold; color: #2c3e50;
                         border-bottom: 2px solid #2c3e50; padding-bottom: 6px; margin-bottom: 16px; }
        .form-row { display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; }
        .form-row .field { flex: 1; min-width: 160px; }
        .form-row label { display: block; font-weight: bold; color: #555; margin-bottom: 5px; font-size: 14px; }
        input[type=date] { padding: 9px; border: 1px solid #ccc; border-radius: 4px;
                           font-size: 14px; width: 100%; box-sizing: border-box; }
        .btn { padding: 10px 22px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #2c3e50; color: white; }
        .btn:hover { background: #34495e; }
        .stats-grid { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 20px; }
        .stat-card { background: #f8f9fa; border-radius: 6px; padding: 20px 24px; flex: 1;
                     min-width: 140px; text-align: center; border-top: 4px solid #2c3e50; }
        .stat-card .lbl { font-size: 12px; color: #666; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
        .stat-card .val { font-size: 34px; font-weight: bold; color: #2c3e50; }
        .stat-card.red    { border-color: #e74c3c; } .stat-card.red    .val { color: #e74c3c; }
        .stat-card.green  { border-color: #27ae60; } .stat-card.green  .val { color: #27ae60; }
        .stat-card.blue   { border-color: #2980b9; } .stat-card.blue   .val { color: #2980b9; }
        .stat-card.orange { border-color: #e67e22; } .stat-card.orange .val { color: #e67e22; }
        .stat-card.purple { border-color: #8e44ad; } .stat-card.purple .val { color: #8e44ad; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .period-badge { display: inline-block; background: #d1ecf1; color: #0c5460;
                        padding: 5px 14px; border-radius: 20px; font-size: 14px; margin-bottom: 18px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Activity Report</span>
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

    <h2>Moderation Activity Report</h2>

    <div class="panel">
        <div class="section-title">Select Report Period</div>
        <form method="post" action="activity_report.php">
            <div class="form-row">
                <div class="field">
                    <label>From Date *</label>
                    <input type="date" name="from_date"
                           value="<?php echo htmlspecialchars($data['from_date']); ?>" required>
                </div>
                <div class="field">
                    <label>To Date *</label>
                    <input type="date" name="to_date"
                           value="<?php echo htmlspecialchars($data['to_date']); ?>" required>
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($data['report'] !== null):
        $r = $data['report'];
    ?>
    <div class="panel">
        <div class="section-title">Report Results</div>
        <div class="period-badge">
            Period: <strong><?php echo htmlspecialchars($data['from_date']); ?></strong>
            &mdash;
            <strong><?php echo htmlspecialchars($data['to_date']); ?></strong>
        </div>

        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="lbl">Total Processed</div>
                <div class="val"><?php echo $r['total_processed']; ?></div>
            </div>
            <div class="stat-card green">
                <div class="lbl">Resolved</div>
                <div class="val"><?php echo $r['resolved']; ?></div>
            </div>
            <div class="stat-card">
                <div class="lbl">Dismissed</div>
                <div class="val"><?php echo $r['dismissed']; ?></div>
            </div>
        </div>

        <div class="stats-grid" style="margin-top:14px;">
            <div class="stat-card orange">
                <div class="lbl">Warnings Issued</div>
                <div class="val"><?php echo $r['warnings_issued']; ?></div>
            </div>
            <div class="stat-card red">
                <div class="lbl">Content Deleted</div>
                <div class="val"><?php echo $r['content_deleted']; ?></div>
            </div>
            <div class="stat-card blue">
                <div class="lbl">Content Edited</div>
                <div class="val"><?php echo $r['content_edited']; ?></div>
            </div>
            <div class="stat-card purple">
                <div class="lbl">Users Suspended</div>
                <div class="val"><?php echo $r['users_suspended']; ?></div>
            </div>
        </div>

        <h3 style="margin-top:28px;">Full Breakdown</h3>
        <table>
            <tr><th>Metric</th><th>Count</th></tr>
            <tr><td>Total Reports Processed</td><td><?php echo $r['total_processed']; ?></td></tr>
            <tr><td>Resolved</td><td><?php echo $r['resolved']; ?></td></tr>
            <tr><td>Dismissed</td><td><?php echo $r['dismissed']; ?></td></tr>
            <tr><td>Warnings Issued</td><td><?php echo $r['warnings_issued']; ?></td></tr>
            <tr><td>Content Deleted</td><td><?php echo $r['content_deleted']; ?></td></tr>
            <tr><td>Content Edited</td><td><?php echo $r['content_edited']; ?></td></tr>
            <tr><td>Users Suspended</td><td><?php echo $r['users_suspended']; ?></td></tr>
        </table>

        <h3 style="margin-top:24px;">Actions Taken by Content Type</h3>
        <table>
            <tr><th>Content Type</th><th>Actions Taken</th></tr>
            <?php
            $types = array('question', 'answer', 'comment');
            foreach ($types as $type):
                $cnt = isset($r['actions_by_type'][$type]) ? $r['actions_by_type'][$type] : 0;
            ?>
            <tr>
                <td><?php echo ucfirst($type); ?></td>
                <td><?php echo $cnt; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
