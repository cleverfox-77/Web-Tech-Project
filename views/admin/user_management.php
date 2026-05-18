<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/UserManagementController.php';
$controller = new UserManagementController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #1a252f; color: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #ccc; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .navbar a:hover { color: white; }
        .container { max-width: 1150px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #1a252f; }
        .panel { background: white; border-radius: 8px; padding: 22px; margin-bottom: 22px;
                 box-shadow: 0 1px 5px rgba(0,0,0,0.09); }
        .search-row { display: flex; gap: 10px; align-items: center; margin-bottom: 18px; }
        .search-row input[type=text] { flex: 1; padding: 10px 14px; border: 1px solid #ccc;
                                       border-radius: 4px; font-size: 14px; }
        .btn { padding: 9px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary   { background: #1a252f; color: white; }
        .btn-success   { background: #27ae60; color: white; }
        .btn-warning   { background: #e67e22; color: white; }
        .btn-danger    { background: #e74c3c; color: white; }
        .btn-info      { background: #2980b9; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn:hover { opacity: 0.88; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        tr:hover { background: #fafafa; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-member    { background: #eaf2ff; color: #2980b9; }
        .badge-expert    { background: #eafaf1; color: #27ae60; }
        .badge-moderator { background: #fef9e7; color: #e67e22; }
        .badge-admin     { background: #f5eef8; color: #8e44ad; }
        .badge-active   { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        .action-btns form { display: inline; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        #searchResults { display: none; }
        #liveResults table { margin-top: 10px; }
        .spinner { display: none; color: #888; font-size: 13px; margin-left: 8px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; User Management</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
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

    <h2>User Management</h2>

    <div class="panel">
        <!-- Live AJAX Search -->
        <div class="search-row">
            <input type="text" id="liveSearch" placeholder="Search by name or username (live search)..." autocomplete="off">
            <span class="spinner" id="spinner">Searching...</span>
        </div>
        <div id="liveResults"></div>
    </div>

    <!-- Full user table -->
    <div class="panel">
        <h3 style="margin-top:0;color:#1a252f;">
            All Users
            <?php if ($data['keyword']): ?>
                &mdash; Results for: <em><?php echo htmlspecialchars($data['keyword']); ?></em>
            <?php endif; ?>
        </h3>
        <table id="userTable">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Reputation</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php if (count($data['users']) == 0): ?>
            <tr><td colspan="8" style="text-align:center;color:#aaa;padding:30px;">No users found.</td></tr>
            <?php endif; ?>
            <?php foreach ($data['users'] as $user): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td>@<?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                <td><?php echo $user['reputation']; ?></td>
                <td>
                    <?php if ($user['is_active']): ?>
                        <span class="badge badge-active">Active</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Inactive</span>
                    <?php endif; ?>
                </td>
                <td class="action-btns">
                    <?php if ($user['role'] == 'member'): ?>
                    <form method="post" action="../../controllers/UserManagementController.php" style="display:inline;">
                        <input type="hidden" name="action" value="promote_expert">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-success" style="font-size:12px;padding:5px 10px;"
                            onclick="return confirm('Promote to Expert?')">&#8593; Expert</button>
                    </form>
                    <form method="post" action="../../controllers/UserManagementController.php" style="display:inline;">
                        <input type="hidden" name="action" value="promote_moderator">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-warning" style="font-size:12px;padding:5px 10px;"
                            onclick="return confirm('Promote to Moderator?')">&#8593; Mod</button>
                    </form>
                    <?php elseif ($user['role'] == 'expert' || $user['role'] == 'moderator'): ?>
                    <form method="post" action="../../controllers/UserManagementController.php" style="display:inline;">
                        <input type="hidden" name="action" value="demote_member">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-secondary" style="font-size:12px;padding:5px 10px;"
                            onclick="return confirm('Demote to Member?')">&#8595; Demote</button>
                    </form>
                    <?php endif; ?>

                    <?php if ($user['is_active']): ?>
                    <form method="post" action="../../controllers/UserManagementController.php" style="display:inline;">
                        <input type="hidden" name="action" value="deactivate">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-danger" style="font-size:12px;padding:5px 10px;"
                            onclick="return confirm('Deactivate this account?')">Deactivate</button>
                    </form>
                    <?php else: ?>
                    <form method="post" action="../../controllers/UserManagementController.php" style="display:inline;">
                        <input type="hidden" name="action" value="activate">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-info" style="font-size:12px;padding:5px 10px;">Activate</button>
                    </form>
                    <?php endif; ?>
                    <a href="user_profile.php?id=<?php echo $user['id']; ?>"
                       style="display:inline-block;background:#1a252f;color:white;padding:5px 10px;border-radius:4px;font-size:12px;text-decoration:none;">Profile</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<script>
// Live AJAX user search using XMLHttpRequest
var searchInput = document.getElementById('liveSearch');
var liveResults = document.getElementById('liveResults');
var spinner = document.getElementById('spinner');
var searchTimer = null;

searchInput.onkeyup = function() {
    var keyword = this.value.trim();
    clearTimeout(searchTimer);

    if (keyword.length == 0) {
        liveResults.innerHTML = '';
        return;
    }

    searchTimer = setTimeout(function() {
        spinner.style.display = 'inline';
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4) {
                spinner.style.display = 'none';
                if (this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.success) {
                        buildResultsTable(response.users, keyword);
                    } else {
                        liveResults.innerHTML = '<p style="color:#e74c3c;">' + response.message + '</p>';
                    }
                }
            }
        };
        xhttp.open("GET", "../../api/admin_ajax.php?action=search_user&keyword=" + encodeURIComponent(keyword), true);
        xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhttp.send();
    }, 350);
};

function buildResultsTable(users, keyword) {
    if (users.length == 0) {
        liveResults.innerHTML = '<p style="color:#888;font-size:13px;">No users found for "' + keyword + '".</p>';
        return;
    }
    var html = '<p style="font-size:13px;color:#555;">Found <strong>' + users.length + '</strong> result(s) for "' + keyword + '":</p>';
    html += '<table><tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th><th>Status</th></tr>';
    for (var i = 0; i < users.length; i++) {
        var u = users[i];
        var status = u.is_active == 1 ? '<span class="badge badge-active">Active</span>' : '<span class="badge badge-inactive">Inactive</span>';
        html += '<tr>';
        html += '<td>' + u.id + '</td>';
        html += '<td>' + u.name + '</td>';
        html += '<td>@' + u.username + '</td>';
        html += '<td><span class="badge badge-' + u.role + '">' + u.role.charAt(0).toUpperCase() + u.role.slice(1) + '</span></td>';
        html += '<td>' + status + '</td>';
        html += '</tr>';
    }
    html += '</table>';
    liveResults.innerHTML = html;
}
</script>
</body>
</html>
