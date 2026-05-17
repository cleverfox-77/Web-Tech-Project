<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ReportController.php';
$controller = new ReportController();
$data = $controller->queue();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Report Queue</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #2c3e50; }
        .report-card { background: white;
        border: 1px solid #eaeaea;
        border-radius: 6px; padding: 20px; margin-bottom: 20px;
                       box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .report-card .meta { font-size: 13px; color: #888; margin-bottom: 8px; }
        .report-card .content-box { background: #f8f9fa; border-left: 4px solid #2c3e50;
                                    padding: 12px; margin: 10px 0; border-radius: 3px; }
        .report-card .reason { background: #fff3cd; border-left: 4px solid #ffc107;
                               padding: 10px; margin: 10px 0; border-radius: 3px; font-size: 14px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-question { background: #d1ecf1; color: #0c5460; }
        .badge-answer   { background: #d4edda; color: #155724; }
        .badge-comment  { background: #fff3cd; color: #856404; }
        .action-form { margin-top: 14px; border-top: 1px solid #eee; padding-top: 14px; }
        .action-form label { font-weight: bold; color: #2c3e50; }
        .action-form input[type=text], .action-form textarea, .action-form select {
            width: 100%; padding: 8px; margin: 6px 0 12px; border: 1px solid #ccc;
            border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .action-form textarea { height: 80px; resize: vertical; }
        .btn { padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; margin-right: 6px; }
        .btn-dismiss { background: #6c757d; color: white; }
        .btn-edit    { background: #2980b9; color: white; }
        .btn-delete  { background: #e74c3c; color: white; }
        .btn-warn    { background: #f39c12; color: white; }
        .btn-close   { background: #8e44ad; color: white; }
        .btn:hover { opacity: 0.9; }
        .edit-fields { display: none; margin-top: 10px; }
        .no-reports { text-align: center; padding: 60px; color: #888; font-size: 18px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Report Queue</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
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

    <h2>Pending Report Queue (<?php echo count($data['reports']); ?>)</h2>

    <?php if (count($data['reports']) == 0): ?>
        <div class="no-reports">&#10003; No pending reports. The platform is clean!</div>
    <?php endif; ?>

    <?php foreach ($data['reports'] as $report): ?>
    <div class="report-card">
        <div class="meta">
            Report #<?php echo $report['id']; ?> &mdash;
            <span class="badge badge-<?php echo $report['entity_type']; ?>">
                <?php echo ucfirst($report['entity_type']); ?>
            </span>
            &mdash; Reported by: <strong><?php echo htmlspecialchars($report['reporter_username']); ?></strong>
            &mdash; <?php echo htmlspecialchars($report['created_at']); ?>
        </div>

        <div class="reason">
            <strong>Reason:</strong> <?php echo htmlspecialchars($report['reason']); ?>
        </div>

        <?php if ($report['content']): ?>
        <div class="content-box">
            <?php if ($report['entity_type'] == 'question'): ?>
                <strong>Question:</strong> <?php echo htmlspecialchars($report['content']['title']); ?><br>
                <em>By: <?php echo htmlspecialchars($report['content']['author_username']); ?></em><br>
                <p><?php echo nl2br(htmlspecialchars($report['content']['body'])); ?></p>
            <?php elseif ($report['entity_type'] == 'answer'): ?>
                <strong>Answer by:</strong> <?php echo htmlspecialchars($report['content']['author_username']); ?><br>
                <p><?php echo nl2br(htmlspecialchars($report['content']['body'])); ?></p>
            <?php elseif ($report['entity_type'] == 'comment'): ?>
                <strong>Comment by:</strong> <?php echo htmlspecialchars($report['content']['author_username']); ?><br>
                <p><?php echo nl2br(htmlspecialchars($report['content']['body'])); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="action-form">
            <!-- Moderator Note -->
            <label>Moderator Note (optional):</label>
            <input type="text" id="note_<?php echo $report['id']; ?>" placeholder="Internal note...">

            <div>
                <!-- Dismiss -->
                <form method="post" action="../../controllers/ReportController.php" style="display:inline;">
                    <input type="hidden" name="action" value="dismiss">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="entity_type" value="<?php echo $report['entity_type']; ?>">
                    <input type="hidden" name="entity_id" value="<?php echo $report['entity_id']; ?>">
                    <input type="hidden" name="moderator_note" id="dis_note_<?php echo $report['id']; ?>" value="">
                    <button type="submit" class="btn btn-dismiss"
                        onclick="document.getElementById('dis_note_<?php echo $report['id']; ?>').value=document.getElementById('note_<?php echo $report['id']; ?>').value">
                        Dismiss
                    </button>
                </form>

                <!-- Edit -->
                <button class="btn btn-edit" onclick="toggleEdit(<?php echo $report['id']; ?>, '<?php echo $report['entity_type']; ?>')">Edit Content</button>

                <!-- Delete -->
                <form method="post" action="../../controllers/ReportController.php" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="entity_type" value="<?php echo $report['entity_type']; ?>">
                    <input type="hidden" name="entity_id" value="<?php echo $report['entity_id']; ?>">
                    <input type="hidden" name="moderator_note" id="del_note_<?php echo $report['id']; ?>" value="">
                    <button type="submit" class="btn btn-delete"
                        onclick="return confirm('Delete this content?') && (document.getElementById('del_note_<?php echo $report['id']; ?>').value=document.getElementById('note_<?php echo $report['id']; ?>').value, true)">
                        Delete
                    </button>
                </form>

                <!-- Warn -->
                <button class="btn btn-warn" onclick="toggleWarn(<?php echo $report['id']; ?>)">Issue Warning</button>

                <?php if ($report['entity_type'] == 'question'): ?>
                <!-- Close -->
                <button class="btn btn-close" onclick="toggleClose(<?php echo $report['id']; ?>)">Close Question</button>
                <?php endif; ?>
            </div>

            <!-- Edit Form -->
            <div id="edit_form_<?php echo $report['id']; ?>" class="edit-fields">
                <form method="post" action="../../controllers/ReportController.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="entity_type" value="<?php echo $report['entity_type']; ?>">
                    <input type="hidden" name="entity_id" value="<?php echo $report['entity_id']; ?>">
                    <input type="hidden" name="moderator_note" id="edit_note_<?php echo $report['id']; ?>" value="">
                    <?php if ($report['entity_type'] == 'question'): ?>
                        <label>Title:</label>
                        <input type="text" name="title" value="<?php echo $report['content'] ? htmlspecialchars($report['content']['title']) : ''; ?>" required>
                    <?php endif; ?>
                    <label>Body:</label>
                    <textarea name="body" required><?php echo $report['content'] ? htmlspecialchars($report['content']['body']) : ''; ?></textarea>
                    <button type="submit" class="btn btn-edit"
                        onclick="document.getElementById('edit_note_<?php echo $report['id']; ?>').value=document.getElementById('note_<?php echo $report['id']; ?>').value">
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Warn Form -->
            <div id="warn_form_<?php echo $report['id']; ?>" class="edit-fields">
                <form method="post" action="../../controllers/ReportController.php">
                    <input type="hidden" name="action" value="warn">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="entity_type" value="<?php echo $report['entity_type']; ?>">
                    <input type="hidden" name="entity_id" value="<?php echo $report['entity_id']; ?>">
                    <input type="hidden" name="author_id" value="<?php echo $report['content'] ? $report['content']['author_id'] : 0; ?>">
                    <input type="hidden" name="moderator_note" id="warn_note_<?php echo $report['id']; ?>" value="">
                    <label>Warning Reason:</label>
                    <input type="text" name="warn_reason" placeholder="Explain the warning..." required>
                    <button type="submit" class="btn btn-warn"
                        onclick="document.getElementById('warn_note_<?php echo $report['id']; ?>').value=document.getElementById('note_<?php echo $report['id']; ?>').value">
                        Send Warning
                    </button>
                </form>
            </div>

            <!-- Close Form -->
            <div id="close_form_<?php echo $report['id']; ?>" class="edit-fields">
                <form method="post" action="../../controllers/ReportController.php">
                    <input type="hidden" name="action" value="close">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="entity_type" value="<?php echo $report['entity_type']; ?>">
                    <input type="hidden" name="entity_id" value="<?php echo $report['entity_id']; ?>">
                    <input type="hidden" name="moderator_note" id="close_note_<?php echo $report['id']; ?>" value="">
                    <label>Close Reason:</label>
                    <select name="close_reason" required>
                        <option value="">-- Select reason --</option>
                        <option value="off-topic">Off-topic</option>
                        <option value="duplicate">Duplicate</option>
                        <option value="unclear">Unclear question</option>
                        <option value="policy-violation">Policy violation</option>
                    </select>
                    <button type="submit" class="btn btn-close"
                        onclick="document.getElementById('close_note_<?php echo $report['id']; ?>').value=document.getElementById('note_<?php echo $report['id']; ?>').value">
                        Close Question
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function toggleEdit(id, type) {
    var el = document.getElementById('edit_form_' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
function toggleWarn(id) {
    var el = document.getElementById('warn_form_' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
function toggleClose(id) {
    var el = document.getElementById('close_form_' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>
