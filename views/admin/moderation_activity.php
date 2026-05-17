<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ModerationActivityController.php';
$controller = new ModerationActivityController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Moderation Activity &mdash; Admin</title>
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
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-resolved  { background: #d4edda; color: #155724; }
        .badge-dismissed { background: #fff3cd; color: #856404; }
        .badge-pending   { background: #f8d7da; color: #721c24; }
        .no-data { color: #aaa; font-style: italic; text-align: center; padding: 24px; }
        .override-form { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px;
                         padding: 14px; margin-top: 10px; display: none; }
        .override-form label { font-weight: bold; color: #555; font-size: 13px; display: block; margin-bottom: 5px; }
        .override-form input[type=text] { width: 100%; padding: 8px; border: 1px solid #ccc;
                                          border-radius: 4px; font-size: 13px; box-sizing: border-box; margin-bottom: 8px; }
        .btn { padding: 7px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-success   { background: #27ae60; color: white; }
        .btn-warning   { background: #e67e22; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn:hover { opacity: 0.88; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .mod-tag { display: inline-block; background: #eaf2ff; color: #2980b9; padding: 2px 8px;
                   border-radius: 10px; font-size: 11px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Moderation Activity</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="user_management.php">Users</a>
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

    <h2>Moderation Activity</h2>

    <!-- Suspended Users with Override -->
    <div class="panel">
        <div class="section-title">Suspended Users (<?php echo count($data['suspended_users']); ?>)</div>
        <?php if (count($data['suspended_users']) == 0): ?>
            <p class="no-data">No suspended users at this time.</p>
        <?php else: ?>
        <table>
            <tr><th>User</th><th>Username</th><th>Email</th><th>Last Warning By</th><th>Last Warning</th><th>Admin Override</th></tr>
            <?php foreach ($data['suspended_users'] as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td>@<?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <?php if ($u['last_mod_username']): ?>
                        <span class="mod-tag">@<?php echo htmlspecialchars($u['last_mod_username']); ?></span>
                    <?php else: ?>
                        <span style="color:#aaa;">—</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $u['last_warning_at'] ? htmlspecialchars($u['last_warning_at']) : '—'; ?></td>
                <td>
                    <button class="btn btn-success" onclick="toggleOverride('lift_<?php echo $u['id']; ?>')">
                        &#8593; Lift Suspension
                    </button>
                    <div id="lift_<?php echo $u['id']; ?>" class="override-form">
                        <form method="post" action="../../controllers/ModerationActivityController.php">
                            <input type="hidden" name="action" value="lift_suspension">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <label>Reason for lifting suspension *</label>
                            <input type="text" name="reason" placeholder="Explain why this suspension is being overridden..." required>
                            <button type="submit" class="btn btn-success">Confirm Lift</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleOverride('lift_<?php echo $u['id']; ?>')">Cancel</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- All Warnings -->
    <div class="panel">
        <div class="section-title">All Warnings Issued (<?php echo count($data['warnings']); ?>)</div>
        <?php if (count($data['warnings']) == 0): ?>
            <p class="no-data">No warnings have been issued.</p>
        <?php else: ?>
        <table>
            <tr><th>Warned User</th><th>Reason</th><th>Issued By (Mod)</th><th>Date</th></tr>
            <?php foreach ($data['warnings'] as $w): ?>
            <tr>
                <td><?php echo htmlspecialchars($w['user_name']); ?>
                    <br><small style="color:#888;">@<?php echo htmlspecialchars($w['user_username']); ?></small></td>
                <td><?php echo htmlspecialchars($w['reason']); ?></td>
                <td><span class="mod-tag">@<?php echo htmlspecialchars($w['mod_username']); ?></span></td>
                <td><?php echo htmlspecialchars($w['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Content Actions with Reinstate Override -->
    <div class="panel">
        <div class="section-title">Content Actions by Moderators (<?php echo count($data['content_actions']); ?>)</div>
        <?php if (count($data['content_actions']) == 0): ?>
            <p class="no-data">No content actions recorded.</p>
        <?php else: ?>
        <table>
            <tr><th>Report #</th><th>Entity</th><th>Reason</th><th>Status</th><th>Mod Note</th><th>Reporter</th><th>Date</th><th>Override</th></tr>
            <?php foreach ($data['content_actions'] as $r): ?>
            <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo ucfirst($r['entity_type']); ?> #<?php echo $r['entity_id']; ?></td>
                <td><?php echo htmlspecialchars($r['reason']); ?></td>
                <td><span class="badge badge-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span></td>
                <td><?php echo htmlspecialchars($r['moderator_note']); ?></td>
                <td>@<?php echo htmlspecialchars($r['reporter_username']); ?></td>
                <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                <td>
                    <?php if ($r['status'] == 'resolved'): ?>
                    <button class="btn btn-warning" onclick="toggleOverride('reinstate_<?php echo $r['id']; ?>')">
                        &#8617; Reinstate
                    </button>
                    <div id="reinstate_<?php echo $r['id']; ?>" class="override-form">
                        <form method="post" action="../../controllers/ModerationActivityController.php">
                            <input type="hidden" name="action" value="reinstate_content">
                            <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
                            <label>Reason for reinstating *</label>
                            <input type="text" name="reason" placeholder="Explain the override..." required>
                            <button type="submit" class="btn btn-warning">Confirm Reinstate</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleOverride('reinstate_<?php echo $r['id']; ?>')">Cancel</button>
                        </form>
                    </div>
                    <?php else: ?>
                        <span style="color:#aaa;font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleOverride(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>
