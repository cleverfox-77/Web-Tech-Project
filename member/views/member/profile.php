<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';
$controller = new ProfileController();
$data = $controller->index();
$u = $data['user'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 950px; margin: 28px auto; padding: 0 16px; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 6px; margin-bottom: 14px; }
        .profile-header { display: flex; align-items: center; gap: 20px; }
        .avatar { width: 70px; height: 70px; border-radius: 50%; background: #2c3e50; color: white;
                  display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; flex-shrink: 0; }
        .stats-row { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 14px; }
        .stat-box { background: #f8f9fa; border-radius: 6px; padding: 12px 20px; text-align: center; flex: 1; min-width: 90px; }
        .stat-box .lbl { font-size: 11px; color: #888; margin-bottom: 4px; }
        .stat-box .val { font-size: 24px; font-weight: bold; color: #2c3e50; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input[type=text], input[type=email], input[type=password], textarea, select {
            width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px;
            font-size: 14px; box-sizing: border-box; margin-bottom: 12px; }
        textarea { height: 80px; resize: vertical; }
        .btn { padding: 9px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #2c3e50; color: white; }
        .btn:hover { opacity: 0.88; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 9px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .badge-tag { display: inline-block; background: #2c3e50; color: white; padding: 3px 10px; border-radius: 20px; font-size: 12px; margin: 2px; }
        .app-status { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; }
        .app-status.pending  { background: #fff3cd; color: #856404; }
        .app-status.approved { background: #d4edda; color: #155724; }
        .app-status.rejected { background: #f8d7da; color: #721c24; }
        .rep-pos { color: #27ae60; font-weight: bold; }
        .rep-neg { color: #e74c3c; font-weight: bold; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .no-data { color: #aaa; font-style: italic; text-align: center; padding: 20px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong></span>
    <div>
        <a href="dashboard.php">Home</a>
        <a href="notifications.php">Notifications</a>
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
            <div class="avatar"><?php echo strtoupper(substr($u['name'], 0, 1)); ?></div>
            <div>
                <h2 style="margin:0 0 4px;"><?php echo htmlspecialchars($u['name']); ?>
                    <small style="font-size:14px;color:#888;font-weight:normal;">@<?php echo htmlspecialchars($u['username']); ?></small>
                </h2>
                <p style="margin:0;color:#666;font-size:13px;">
                    Role: <strong><?php echo ucfirst($u['role']); ?></strong> &mdash;
                    <?php echo htmlspecialchars($u['email']); ?> &mdash;
                    Joined: <?php echo date('d M Y', strtotime($u['created_at'])); ?>
                </p>
                <?php if ($u['bio']): ?><p style="margin:6px 0 0;color:#555;font-size:13px;"><?php echo htmlspecialchars($u['bio']); ?></p><?php endif; ?>
            </div>
        </div>
        <div class="stats-row">
            <div class="stat-box"><div class="lbl">Reputation</div><div class="val"><?php echo $u['reputation']; ?></div></div>
            <div class="stat-box"><div class="lbl">Questions</div><div class="val"><?php echo $data['stats']['questions']; ?></div></div>
            <div class="stat-box"><div class="lbl">Answers</div><div class="val"><?php echo $data['stats']['answers']; ?></div></div>
            <div class="stat-box"><div class="lbl">Votes Received</div><div class="val"><?php echo $data['stats']['votes_received']; ?></div></div>
        </div>
    </div>

    <!-- Edit Profile -->
    <div class="panel">
        <div class="section-title">Update Profile</div>
        <form method="post" action="../../controllers/ProfileController.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            <label>Bio</label>
            <textarea name="bio"><?php echo htmlspecialchars($u['bio']); ?></textarea>
            <label>Profile Picture</label>
            <input type="file" name="profile_pic" accept="image/*" style="margin-bottom:12px;">
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="panel">
        <div class="section-title">Change Password</div>
        <form method="post" action="../../controllers/ProfileController.php">
            <input type="hidden" name="action" value="update_password">
            <label>New Password *</label>
            <input type="password" name="password" required>
            <label>Confirm Password *</label>
            <input type="password" name="confirm" required>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>

    <!-- Expert Application -->
    <div class="panel">
        <div class="section-title">Expert Application</div>
        <?php if ($data['application']): ?>
            <p>Application status: <span class="app-status <?php echo $data['application']['status']; ?>"><?php echo ucfirst($data['application']['status']); ?></span>
               &mdash; Domain: <strong><?php echo htmlspecialchars($data['application']['domain']); ?></strong>
               &mdash; Submitted: <?php echo date('d M Y', strtotime($data['application']['submitted_at'])); ?>
            </p>
        <?php elseif ($u['role'] == 'member'): ?>
        <form method="post" action="../../controllers/ProfileController.php">
            <input type="hidden" name="action" value="apply_expert">
            <label>Domain *</label>
            <input type="text" name="domain" placeholder="e.g. Web Development, Machine Learning" required>
            <label>Credentials *</label>
            <textarea name="credentials" placeholder="Degrees, certifications, years of experience..."></textarea>
            <label>Motivation *</label>
            <textarea name="motivation" placeholder="Why do you want to be a verified expert?"></textarea>
            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
        <?php else: ?>
            <p style="color:#27ae60;">You are a <strong><?php echo ucfirst($u['role']); ?></strong> — no application needed.</p>
        <?php endif; ?>
    </div>

    <!-- Reputation History -->
    <div class="panel">
        <div class="section-title">Reputation History</div>
        <?php if (count($data['rep_history']) == 0): ?>
            <p class="no-data">No reputation events yet.</p>
        <?php else: ?>
        <table>
            <tr><th>Event</th><th>Type</th><th>Value</th><th>Date</th></tr>
            <?php foreach ($data['rep_history'] as $h): ?>
            <tr>
                <td><?php echo $h['value'] > 0 ? 'Upvote received' : 'Downvote received'; ?></td>
                <td><?php echo ucfirst($h['entity_type']); ?></td>
                <td><?php if ($h['value'] > 0): ?><span class="rep-pos">+<?php echo $h['value']; ?></span><?php else: ?><span class="rep-neg"><?php echo $h['value']; ?></span><?php endif; ?></td>
                <td><?php echo date('d M Y', strtotime($h['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- Badges -->
    <div class="panel">
        <div class="section-title">Badges</div>
        <?php if (count($data['badges']) > 0): ?>
            <?php foreach ($data['badges'] as $b): ?>
                <span class="badge-tag"><?php echo htmlspecialchars($b['icon']); ?> <?php echo htmlspecialchars($b['name']); ?></span>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">No badges earned yet.</p>
        <?php endif; ?>

        <h4 style="color:#2c3e50;margin-top:18px;">Badge Progress</h4>
        <table>
            <tr><th>Badge</th><th>Requirement</th><th>Target</th><th>Status</th></tr>
            <?php foreach ($data['all_badges'] as $b):
                $earned = false;
                foreach ($data['badges'] as $eb) { if ($eb['name'] == $b['name']) { $earned = true; break; } }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($b['icon']); ?> <?php echo htmlspecialchars($b['name']); ?></td>
                <td><?php echo htmlspecialchars($b['threshold_type']); ?></td>
                <td><?php echo $b['threshold_value']; ?></td>
                <td><?php echo $earned ? '<span style="color:#27ae60;">&#10003; Earned</span>' : '<span style="color:#aaa;">Not yet</span>'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
