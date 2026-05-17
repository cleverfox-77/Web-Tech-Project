<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';
$controller = new DashboardController();
$data = $controller->index();
$expert = $data['expert'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expert Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #1a5276; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1050px; margin: 28px auto; padding: 0 16px; }
        h2 { color: #1a5276; margin-bottom: 6px; }
        .sub { color: #888; font-size: 14px; margin-bottom: 24px; }
        .stats-grid { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 28px; }
        .stat-card { background: white; border-radius: 6px; padding: 22px 28px; flex: 1; min-width: 160px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); text-align: center; border-top: 4px solid #1a5276; }
        .stat-card h3 { margin: 0 0 8px; font-size: 13px; color: #666; text-transform: uppercase; }
        .stat-card .number { font-size: 38px; font-weight: bold; color: #1a5276; }
        .stat-card.green { border-color: #27ae60; } .stat-card.green .number { color: #27ae60; }
        .stat-card.blue  { border-color: #2980b9; } .stat-card.blue  .number { color: #2980b9; }
        .stat-card.orange{ border-color: #e67e22; } .stat-card.orange .number { color: #e67e22; }
        .quick-links { background: white; border-radius: 6px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); margin-bottom: 20px; }
        .quick-links h3 { margin-top: 0; color: #1a5276; }
        .quick-links a { display: inline-block; background: #1a5276; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; margin: 6px; font-size: 14px; }
        .quick-links a:hover { background: #1f618d; }
        .panel { background: white; border-radius: 6px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); margin-bottom: 20px; }
        .section-title { font-size: 15px; font-weight: bold; color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 6px; margin-bottom: 14px; }
        .badge-expert { display: inline-block; background: #27ae60; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .btn { padding: 7px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-primary { background: #1a5276; color: white; }
        .btn:hover { opacity: 0.88; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Expert Panel</span>
    <div>
        <a href="faq_list.php">FAQs</a>
        <a href="kb_list.php">Knowledge Base</a>
        <a href="session_list.php">Q&A Sessions</a>
        <a href="profile.php">Profile</a>
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

    <h2>Welcome, <?php echo htmlspecialchars($expert['name']); ?> <span class="badge-expert">Expert &#10003;</span></h2>
    <p class="sub">Domain: <strong><?php echo htmlspecialchars($expert['expert_domain']); ?></strong> &mdash; Reputation: <strong><?php echo $expert['reputation']; ?></strong></p>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>FAQs Created</h3>
            <div class="number"><?php echo $data['faq_count']; ?></div>
        </div>
        <div class="stat-card blue">
            <h3>KB Articles</h3>
            <div class="number"><?php echo $data['kb_count']; ?></div>
        </div>
        <div class="stat-card orange">
            <h3>Q&A Sessions</h3>
            <div class="number"><?php echo $data['session_count']; ?></div>
        </div>
        <div class="stat-card green">
            <h3>Total Content Views</h3>
            <div class="number"><?php echo $data['total_views']; ?></div>
        </div>
    </div>

    <!-- Active Sessions Alert -->
    <?php if (count($data['active_sessions']) > 0): ?>
    <div class="panel" style="border-left: 4px solid #e74c3c;">
        <div class="section-title" style="color:#e74c3c;border-color:#e74c3c;">&#128308; Active Q&A Sessions</div>
        <table>
            <tr><th>Session</th><th>Questions</th><th>Action</th></tr>
            <?php foreach ($data['active_sessions'] as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['title']); ?></td>
                <td><?php echo $s['question_count']; ?></td>
                <td><a href="session_view.php?id=<?php echo $s['id']; ?>" class="btn btn-primary">Manage Session</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Content Performance -->
    <div class="panel">
        <div class="section-title">Content Performance</div>
        <table>
            <tr><th>Content Type</th><th>Count</th><th>Total Views</th></tr>
            <tr><td>FAQ Articles</td><td><?php echo $data['faq_count']; ?></td><td><?php echo $data['faq_views']; ?></td></tr>
            <tr><td>Knowledge Base Articles</td><td><?php echo $data['kb_count']; ?></td><td><?php echo $data['kb_views']; ?></td></tr>
            <tr><td>Session Transcripts (Answered)</td><td><?php echo $data['session_count']; ?></td><td><?php echo $data['session_views']; ?></td></tr>
            <tr style="font-weight:bold;"><td>Total</td><td></td><td><?php echo $data['total_views']; ?></td></tr>
        </table>
    </div>

    <div class="quick-links">
        <h3>Quick Actions</h3>
        <a href="faq_list.php">Manage FAQs</a>
        <a href="kb_list.php">Manage Knowledge Base</a>
        <a href="session_list.php">Manage Sessions</a>
        <a href="profile.php">Edit Profile</a>
    </div>
</div>
</body>
</html>
