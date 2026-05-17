<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/SettingsController.php';
$controller = new SettingsController();
$data = $controller->index();
$s = $data['settings'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Platform Settings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #1a252f; color: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #ccc; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .navbar a:hover { color: white; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #1a252f; }
        .panel { background: white; border-radius: 8px; padding: 24px; margin-bottom: 24px;
                 box-shadow: 0 1px 5px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #1a252f; border-bottom: 2px solid #1a252f;
                         padding-bottom: 6px; margin-bottom: 18px; }
        .form-row { display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 14px; }
        .form-row .field { flex: 1; min-width: 140px; }
        .form-row label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input[type=number], input[type=text], textarea, select {
            width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px;
            font-size: 14px; box-sizing: border-box; }
        textarea { height: 80px; resize: vertical; }
        .btn { padding: 9px 22px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #1a252f; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #e67e22; color: white; }
        .btn:hover { opacity: 0.88; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .hint { font-size: 12px; color: #999; margin-top: 3px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Platform Settings</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="user_management.php">Users</a>
        <a href="expert_applications.php">Applications</a>
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

    <h2>Platform Settings</h2>

    <!-- Reputation Scoring Rules -->
    <div class="panel">
        <div class="section-title">Reputation Scoring Rules</div>
        <form method="post" action="../../controllers/SettingsController.php">
            <input type="hidden" name="action" value="update_reputation">
            <div class="form-row">
                <div class="field">
                    <label>Upvote on Question (+)</label>
                    <input type="number" name="upvote_question" min="0"
                        value="<?php echo isset($s['upvote_question']) ? (int)$s['upvote_question'] : 5; ?>">
                </div>
                <div class="field">
                    <label>Upvote on Answer (+)</label>
                    <input type="number" name="upvote_answer" min="0"
                        value="<?php echo isset($s['upvote_answer']) ? (int)$s['upvote_answer'] : 10; ?>">
                </div>
                <div class="field">
                    <label>Accepted Answer Bonus (+)</label>
                    <input type="number" name="accept_bonus" min="0"
                        value="<?php echo isset($s['accept_bonus']) ? (int)$s['accept_bonus'] : 15; ?>">
                </div>
                <div class="field">
                    <label>Downvote Penalty (-)</label>
                    <input type="number" name="downvote_penalty" min="0"
                        value="<?php echo isset($s['downvote_penalty']) ? (int)$s['downvote_penalty'] : 2; ?>">
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Save Rules</button>
                </div>
            </div>
        </form>
    </div>

    <!-- System Announcement -->
    <div class="panel">
        <div class="section-title">System Announcement</div>
        <p style="color:#666;font-size:13px;">This message is displayed on every user dashboard.</p>
        <form method="post" action="../../controllers/SettingsController.php">
            <input type="hidden" name="action" value="post_announcement">
            <label style="font-weight:bold;color:#555;font-size:13px;">Announcement Message *</label>
            <textarea name="announcement" placeholder="Type announcement message here..."><?php echo htmlspecialchars(isset($s['announcement']) ? $s['announcement'] : ''); ?></textarea>
            <br>
            <button type="submit" class="btn btn-warning" style="margin-top:10px;">Post Announcement</button>
        </form>
    </div>

    <!-- Badge Management -->
    <div class="panel">
        <div class="section-title">Badge Management</div>

        <!-- Add new badge -->
        <h3 style="margin-top:0;color:#1a252f;font-size:15px;">Add New Badge</h3>
        <form method="post" action="../../controllers/SettingsController.php">
            <input type="hidden" name="action" value="add_badge">
            <div class="form-row">
                <div class="field">
                    <label>Badge Name *</label>
                    <input type="text" name="badge_name" placeholder="e.g. Top Contributor" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="badge_desc" placeholder="Short description">
                </div>
                <div class="field">
                    <label>Icon (emoji or text)</label>
                    <input type="text" name="badge_icon" placeholder="e.g. &#11088;">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label>Threshold Type *</label>
                    <select name="threshold_type" required>
                        <option value="">-- Select --</option>
                        <option value="reputation">Reputation</option>
                        <option value="questions">Questions Posted</option>
                        <option value="answers">Answers Posted</option>
                        <option value="votes_received">Votes Received</option>
                    </select>
                </div>
                <div class="field">
                    <label>Threshold Value</label>
                    <input type="number" name="threshold_value" min="0" value="0">
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-success">Add Badge</button>
                </div>
            </div>
        </form>

        <!-- Award badge manually -->
        <h3 style="color:#1a252f;font-size:15px;margin-top:22px;">Award Badge to User</h3>
        <form method="post" action="../../controllers/SettingsController.php">
            <input type="hidden" name="action" value="award_badge">
            <div class="form-row">
                <div class="field">
                    <label>User ID *</label>
                    <input type="number" name="user_id" min="1" placeholder="User ID" required>
                    <p class="hint">Find the ID from the User Management page.</p>
                </div>
                <div class="field">
                    <label>Badge *</label>
                    <select name="badge_id" required>
                        <option value="">-- Select badge --</option>
                        <?php foreach ($data['badges'] as $badge): ?>
                        <option value="<?php echo $badge['id']; ?>"><?php echo htmlspecialchars($badge['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-success">Award</button>
                </div>
            </div>
        </form>

        <!-- All badges table -->
        <h3 style="color:#1a252f;font-size:15px;margin-top:22px;">All Badges</h3>
        <table>
            <tr><th>Icon</th><th>Name</th><th>Description</th><th>Threshold</th><th>Times Awarded</th></tr>
            <?php if (count($data['badges']) == 0): ?>
            <tr><td colspan="5" style="text-align:center;color:#aaa;padding:20px;">No badges created yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($data['badges'] as $b): ?>
            <tr>
                <td style="font-size:20px;"><?php echo htmlspecialchars($b['icon']); ?></td>
                <td><?php echo htmlspecialchars($b['name']); ?></td>
                <td><?php echo htmlspecialchars($b['description']); ?></td>
                <td><?php echo htmlspecialchars($b['threshold_type']); ?> &ge; <?php echo $b['threshold_value']; ?></td>
                <td><?php echo $b['award_count']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Featured Content -->
    <div class="panel">
        <div class="section-title">Featured Content Management</div>
        <p style="color:#666;font-size:13px;">Pin content on the platform homepage. Enter comma-separated IDs for each content type.</p>

        <!-- Featured Questions -->
        <h3 style="font-size:14px;color:#1a252f;margin-bottom:8px;">&#128204; Featured Questions</h3>
        <form method="post" action="../../controllers/SettingsController.php" style="margin-bottom:18px;">
            <input type="hidden" name="action" value="set_featured_questions">
            <div class="form-row">
                <div class="field">
                    <label>Question IDs (comma-separated)</label>
                    <input type="text" name="featured_question_ids"
                        value="<?php echo htmlspecialchars(isset($s['featured_questions']) ? $s['featured_questions'] : ''); ?>"
                        placeholder="e.g. 1,5,12">
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>

        <!-- Featured FAQ Articles -->
        <h3 style="font-size:14px;color:#1a252f;margin-bottom:8px;">&#128218; Featured FAQ Articles</h3>
        <form method="post" action="../../controllers/SettingsController.php" style="margin-bottom:18px;">
            <input type="hidden" name="action" value="set_featured_faqs">
            <div class="form-row">
                <div class="field">
                    <label>FAQ IDs (comma-separated)</label>
                    <input type="text" name="featured_faq_ids"
                        value="<?php echo htmlspecialchars(isset($s['featured_faqs']) ? $s['featured_faqs'] : ''); ?>"
                        placeholder="e.g. 2,7">
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>

        <!-- Featured Knowledge Base Articles -->
        <h3 style="font-size:14px;color:#1a252f;margin-bottom:8px;">&#128196; Featured Knowledge Base Articles</h3>
        <form method="post" action="../../controllers/SettingsController.php">
            <input type="hidden" name="action" value="set_featured_kb">
            <div class="form-row">
                <div class="field">
                    <label>Knowledge Base Article IDs (comma-separated)</label>
                    <input type="text" name="featured_kb_ids"
                        value="<?php echo htmlspecialchars(isset($s['featured_kb_articles']) ? $s['featured_kb_articles'] : ''); ?>"
                        placeholder="e.g. 3,9">
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
