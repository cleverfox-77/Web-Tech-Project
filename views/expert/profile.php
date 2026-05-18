<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';
$controller = new ProfileController();
$data = $controller->index();
$expert = $data['expert'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Expert Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #1a5276; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 850px; margin: 28px auto; padding: 0 16px; }
        h2 { color: #1a5276; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 6px; margin-bottom: 14px; }
        .profile-header { display: flex; align-items: center; gap: 20px; }
        .avatar { width: 70px; height: 70px; border-radius: 50%; background: #1a5276; color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; flex-shrink: 0; }
        .stats-row { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 16px; }
        .stat-box { background: #f8f9fa; border-radius: 6px; padding: 12px 20px; text-align: center; flex: 1; min-width: 100px; }
        .stat-box .lbl { font-size: 11px; color: #888; margin-bottom: 4px; }
        .stat-box .val { font-size: 24px; font-weight: bold; color: #1a5276; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input[type=text], textarea { width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; margin-bottom: 12px; }
        textarea { height: 90px; resize: vertical; }
        .btn { padding: 9px 22px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #1a5276; color: white; }
        .btn:hover { opacity: 0.88; }
        .badge-expert { display: inline-block; background: #27ae60; color: white; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Expert Panel</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="faq_list.php">FAQs</a>
        <a href="kb_list.php">Knowledge Base</a>
        <a href="session_list.php">Sessions</a>
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

    <!-- Profile Header -->
    <div class="panel">
        <div class="profile-header">
            <div class="avatar"><?php echo strtoupper(substr($expert['name'], 0, 1)); ?></div>
            <div>
                <h2 style="margin:0 0 4px;">
                    <?php echo htmlspecialchars($expert['name']); ?>
                    <span class="badge-expert">Expert &#10003;</span>
                </h2>
                <p style="margin:0;color:#666;font-size:13px;">
                    @<?php echo htmlspecialchars($expert['username']); ?> &mdash;
                    Domain: <strong><?php echo htmlspecialchars($expert['expert_domain']); ?></strong> &mdash;
                    Reputation: <strong><?php echo $expert['reputation']; ?></strong>
                </p>
                <?php if ($expert['bio']): ?>
                    <p style="margin:6px 0 0;color:#555;font-size:13px;"><?php echo htmlspecialchars($expert['bio']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content Performance Stats -->
        <div class="stats-row">
            <div class="stat-box"><div class="lbl">FAQs</div><div class="val"><?php echo $data['faq_count']; ?></div></div>
            <div class="stat-box"><div class="lbl">KB Articles</div><div class="val"><?php echo $data['kb_count']; ?></div></div>
            <div class="stat-box"><div class="lbl">Sessions</div><div class="val"><?php echo $data['session_count']; ?></div></div>
            <div class="stat-box"><div class="lbl">FAQ Views</div><div class="val"><?php echo $data['faq_views']; ?></div></div>
            <div class="stat-box"><div class="lbl">KB Views</div><div class="val"><?php echo $data['kb_views']; ?></div></div>
            <div class="stat-box"><div class="lbl">Total Views</div><div class="val"><?php echo $data['total_views']; ?></div></div>
        </div>
    </div>

    <!-- Edit Profile -->
    <div class="panel">
        <div class="section-title">Update Expert Profile</div>
        <form method="post" action="../../controllers/ProfileController.php">
            <label>Expert Domain</label>
            <input type="text" name="expert_domain" placeholder="e.g. Machine Learning, Web Security"
                   value="<?php echo htmlspecialchars($expert['expert_domain']); ?>">
            <label>Expert Bio / Credentials Summary</label>
            <textarea name="bio" placeholder="Describe your expertise, credentials, and background..."><?php echo htmlspecialchars($expert['bio']); ?></textarea>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>
</body>
</html>
