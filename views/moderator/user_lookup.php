<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../config/Database.php';

AuthMiddleware::checkModerator();

$results  = array();
$keyword  = '';
$searched = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keyword  = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
    $searched = true;

    if (!empty($keyword)) {
        $db   = new Database();
        $conn = $db->getConnection();

        $sql  = "SELECT id, name, username, email, role, reputation, is_active, created_at
                 FROM users
                 WHERE name LIKE ? OR username LIKE ?
                 ORDER BY username ASC";
        $stmt = $conn->prepare($sql);
        $like = '%' . $keyword . '%';
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Lookup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #2c3e50; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 22px;
                 box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .section-title { font-size: 15px; font-weight: bold; color: #2c3e50;
                         border-bottom: 2px solid #2c3e50; padding-bottom: 6px; margin-bottom: 14px; }
        .search-row { display: flex; gap: 10px; align-items: center; }
        .search-row input[type=text] { flex: 1; padding: 10px 14px; border: 1px solid #ccc;
                                       border-radius: 4px; font-size: 14px; }
        .btn { padding: 10px 22px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #2c3e50; color: white; }
        .btn:hover { background: #34495e; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        tr:hover td { background: #fafafa; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-member    { background: #eaf2ff; color: #2980b9; }
        .badge-expert    { background: #eafaf1; color: #27ae60; }
        .badge-moderator { background: #fef9e7; color: #e67e22; }
        .badge-admin     { background: #f5eef8; color: #8e44ad; }
        .badge-active    { background: #d4edda; color: #155724; }
        .badge-inactive  { background: #f8d7da; color: #721c24; }
        .no-data { color: #aaa; font-style: italic; text-align: center; padding: 24px; }
        a.profile-link { display: inline-block; background: #2c3e50; color: white;
                         padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; }
        a.profile-link:hover { background: #34495e; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; User Lookup</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="report_queue.php">Report Queue</a>
        <a href="warnings.php">Warnings</a>
        <a href="tag_management.php">Tags</a>
        <a href="../../logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>User Lookup</h2>

    <div class="panel">
        <div class="section-title">Search Users</div>
        <form method="post" action="user_lookup.php">
            <div class="search-row">
                <input type="text" name="keyword"
                       value="<?php echo htmlspecialchars($keyword); ?>"
                       placeholder="Search by name or username..." required>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>

    <?php if ($searched): ?>
    <div class="panel">
        <div class="section-title">
            Search Results
            <?php if (!empty($keyword)): ?>
                for &ldquo;<?php echo htmlspecialchars($keyword); ?>&rdquo;
            <?php endif; ?>
            &mdash; <?php echo count($results); ?> found
        </div>

        <?php if (count($results) == 0): ?>
            <p class="no-data">No users found matching your search.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Reputation</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Action</th>
            </tr>
            <?php foreach ($results as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td>@<?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <span class="badge badge-<?php echo $u['role']; ?>">
                        <?php echo ucfirst($u['role']); ?>
                    </span>
                </td>
                <td><?php echo $u['reputation']; ?></td>
                <td>
                    <?php if ($u['is_active']): ?>
                        <span class="badge badge-active">Active</span>
                    <?php else: ?>
                        <span class="badge badge-inactive">Suspended</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars(date('d M Y', strtotime($u['created_at']))); ?></td>
                <td>
                    <a href="user_profile.php?id=<?php echo $u['id']; ?>" class="profile-link">
                        View Profile
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
