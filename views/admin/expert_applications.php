<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';
$controller = new ApplicationController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expert Applications</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #1a252f; color: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #ccc; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .navbar a:hover { color: white; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #1a252f; }
        .panel { background: white; border-radius: 8px; padding: 22px; margin-bottom: 22px;
                 box-shadow: 0 1px 5px rgba(0,0,0,0.09); }
        .app-card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 18px; margin-bottom: 16px; background: #fafafa; }
        .app-card .app-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .app-card .app-header h3 { margin: 0 0 4px; color: #1a252f; font-size: 16px; }
        .app-card .app-header p  { margin: 0; color: #888; font-size: 13px; }
        .app-card .field { margin-top: 12px; }
        .app-card .field strong { display: block; font-size: 12px; color: #555; text-transform: uppercase;
                                  letter-spacing: 0.4px; margin-bottom: 4px; }
        .app-card .field p { margin: 0; font-size: 14px; color: #333; line-height: 1.5; }
        .app-actions { margin-top: 14px; display: flex; gap: 10px; align-items: center; }
        .btn { padding: 9px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-approve { background: #27ae60; color: white; }
        .btn-reject  { background: #e74c3c; color: white; }
        .btn-quick   { background: #2980b9; color: white; font-size: 12px; padding: 6px 14px; }
        .btn:hover { opacity: 0.88; }
        .badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-pending  { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-rejected { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .section-title { font-size: 15px; font-weight: bold; color: #1a252f; border-bottom: 2px solid #1a252f;
                         padding-bottom: 6px; margin-bottom: 16px; }
        .no-items { text-align: center; color: #aaa; padding: 30px; font-style: italic; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        #ajaxMsg { font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Expert Applications</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="user_management.php">Users</a>
        <a href="platform_settings.php">Settings</a>
        <a href="analytics_report.php">Analytics</a>
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

    <h2>Expert Applications</h2>

    <!-- Pending Applications -->
    <div class="panel">
        <div class="section-title">Pending Applications (<?php echo count($data['pending']); ?>)</div>
        <span id="ajaxMsg"></span>

        <?php if (count($data['pending']) == 0): ?>
            <div class="no-items">No pending applications at this time.</div>
        <?php endif; ?>

        <?php foreach ($data['pending'] as $app): ?>
        <div class="app-card" id="appCard_<?php echo $app['id']; ?>">
            <div class="app-header">
                <div>
                    <h3><?php echo htmlspecialchars($app['applicant_name']); ?>
                        <small style="font-size:13px;color:#888;">@<?php echo htmlspecialchars($app['applicant_username']); ?></small>
                    </h3>
                    <p><?php echo htmlspecialchars($app['applicant_email']); ?> &mdash; Applied: <?php echo htmlspecialchars($app['submitted_at']); ?></p>
                </div>
                <span class="badge badge-pending">Pending</span>
            </div>

            <div class="field">
                <strong>Domain</strong>
                <p><?php echo htmlspecialchars($app['domain']); ?></p>
            </div>
            <div class="field">
                <strong>Credentials</strong>
                <p><?php echo nl2br(htmlspecialchars($app['credentials'])); ?></p>
            </div>
            <div class="field">
                <strong>Motivation</strong>
                <p><?php echo nl2br(htmlspecialchars($app['motivation'])); ?></p>
            </div>

            <div class="app-actions">
                <!-- Standard form submit -->
                <form method="post" action="../../controllers/ApplicationController.php">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                    <button type="submit" class="btn btn-approve"
                        onclick="return confirm('Approve and promote this user to Expert?')">
                        &#10003; Approve
                    </button>
                </form>
                <button class="btn btn-reject" onclick="toggleRejectForm(<?php echo $app['id']; ?>)">&#10007; Reject</button>
                <!-- Quick AJAX approve -->
                <button class="btn btn-quick"
                    onclick="quickApprove(<?php echo $app['id']; ?>)">&#9889; Quick Approve (AJAX)</button>
            </div>

            <!-- Reject form with reason — shown on button click -->
            <div id="rejectForm_<?php echo $app['id']; ?>" style="display:none;margin-top:12px;background:#fff5f5;border:1px solid #f5c6cb;border-radius:6px;padding:14px;">
                <form method="post" action="../../controllers/ApplicationController.php">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                    <label style="font-weight:bold;color:#721c24;font-size:13px;display:block;margin-bottom:5px;">Rejection Reason *</label>
                    <input type="text" name="reject_reason" placeholder="Explain why this application is being rejected..."
                           style="width:100%;padding:8px;border:1px solid #f5c6cb;border-radius:4px;font-size:13px;box-sizing:border-box;margin-bottom:8px;" required>
                    <button type="submit" class="btn btn-reject" onclick="return confirm('Confirm rejection?')">Confirm Rejection</button>
                    <button type="button" class="btn" style="background:#95a5a6;color:white;margin-left:6px;"
                        onclick="toggleRejectForm(<?php echo $app['id']; ?>)">Cancel</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- All Applications History -->
    <div class="panel">
        <div class="section-title">All Applications History</div>
        <table>
            <tr><th>Applicant</th><th>Domain</th><th>Status</th><th>Submitted</th></tr>
            <?php if (count($data['all_apps']) == 0): ?>
            <tr><td colspan="4" class="no-items">No applications found.</td></tr>
            <?php endif; ?>
            <?php foreach ($data['all_apps'] as $app): ?>
            <tr>
                <td><?php echo htmlspecialchars($app['applicant_name']); ?>
                    <small style="color:#888;">@<?php echo htmlspecialchars($app['applicant_username']); ?></small></td>
                <td><?php echo htmlspecialchars($app['domain']); ?></td>
                <td><span class="badge badge-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                <td><?php echo htmlspecialchars($app['submitted_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<script>
function toggleRejectForm(id) {
    var el = document.getElementById('rejectForm_' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}

// AJAX quick approve — no page reload
function quickApprove(app_id) {
    if (!confirm('Quick-approve this application via AJAX?')) {
        return;
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            var msg = document.getElementById('ajaxMsg');
            if (response.success) {
                msg.style.color = 'green';
                msg.innerHTML = '&#10003; ' + response.message;
                // Hide the approved card without reloading
                var card = document.getElementById('appCard_' + app_id);
                if (card) {
                    card.style.opacity = '0.4';
                    card.style.pointerEvents = 'none';
                }
            } else {
                msg.style.color = 'red';
                msg.innerHTML = '&#10007; ' + response.message;
            }
        }
    };
    xhttp.open("POST", "../../api/admin_ajax.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send("action=approve_application&app_id=" + app_id);
}
</script>
</body>
</html>
