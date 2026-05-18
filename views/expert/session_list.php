<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/SessionController.php';
$controller = new SessionController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Q&amp;A Sessions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #1a5276; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 1050px; margin: 28px auto; padding: 0 16px; }
        h2 { color: #1a5276; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 6px; margin-bottom: 14px; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input[type=text], input[type=datetime-local], input[type=number], textarea {
            width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; margin-bottom: 12px; }
        textarea { height: 80px; resize: vertical; }
        .form-row { display: flex; gap: 14px; flex-wrap: wrap; }
        .form-row .field { flex: 1; min-width: 140px; }
        .btn { padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-primary { background: #1a5276; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn-warning { background: #e67e22; color: white; }
        .btn:hover { opacity: 0.88; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge-upcoming { background: #d1ecf1; color: #0c5460; }
        .badge-active   { background: #d4edda; color: #155724; }
        .badge-ended    { background: #f8f9fa; color: #555; }
        .edit-form { display: none; background: #f8f9fa; border-radius: 6px; padding: 16px; margin-top: 10px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .no-data { color: #aaa; font-style: italic; text-align: center; padding: 24px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Expert Panel</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="faq_list.php">FAQs</a>
        <a href="kb_list.php">Knowledge Base</a>
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

    <h2>Q&amp;A Sessions</h2>

    <!-- Schedule New Session -->
    <div class="panel">
        <div class="section-title">Schedule New Session</div>
        <form method="post" action="../../controllers/SessionController.php">
            <input type="hidden" name="action" value="create">
            <label>Title *</label>
            <input type="text" name="title" placeholder="Session title..." required>
            <label>Description</label>
            <textarea name="description" placeholder="What will this session cover?"></textarea>
            <div class="form-row">
                <div class="field">
                    <label>Scheduled Date &amp; Time *</label>
                    <input type="datetime-local" name="scheduled_at" required>
                </div>
                <div class="field">
                    <label>Duration (minutes)</label>
                    <input type="number" name="duration" min="15" max="480" value="60">
                </div>
                <div class="field">
                    <label>Max Questions</label>
                    <input type="number" name="max_questions" min="1" max="100" value="20">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Schedule Session</button>
        </form>
    </div>

    <!-- Sessions List -->
    <div class="panel">
        <div class="section-title">All My Sessions (<?php echo count($data['sessions']); ?>)</div>
        <?php if (count($data['sessions']) == 0): ?>
            <p class="no-data">No sessions scheduled yet.</p>
        <?php else: ?>
        <table>
            <tr><th>Title</th><th>Scheduled</th><th>Duration</th><th>Questions</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($data['sessions'] as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['title']); ?></td>
                <td><?php echo date('d M Y, H:i', strtotime($s['scheduled_at'])); ?></td>
                <td><?php echo $s['duration_minutes']; ?> min</td>
                <td><?php echo $s['question_count']; ?> (<?php echo $s['answered_count']; ?> answered)</td>
                <td><span class="badge badge-<?php echo $s['status']; ?>"><?php echo ucfirst($s['status']); ?></span></td>
                <td>
                    <?php if ($s['status'] == 'upcoming'): ?>
                        <form method="post" action="../../controllers/SessionController.php" style="display:inline;">
                            <input type="hidden" name="action" value="activate">
                            <input type="hidden" name="session_id" value="<?php echo $s['id']; ?>">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Activate this session now?')">&#9654; Activate</button>
                        </form>
                        <button class="btn btn-warning" onclick="toggleEdit('sess_<?php echo $s['id']; ?>')">Edit</button>
                        <form method="post" action="../../controllers/SessionController.php" style="display:inline;">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="session_id" value="<?php echo $s['id']; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this session?')">Cancel</button>
                        </form>
                    <?php elseif ($s['status'] == 'active'): ?>
                        <a href="session_view.php?id=<?php echo $s['id']; ?>" class="btn btn-success">&#128308; Manage Live</a>
                    <?php else: ?>
                        <a href="session_view.php?id=<?php echo $s['id']; ?>" class="btn btn-primary">View Transcript</a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="padding:0;">
                    <div id="sess_<?php echo $s['id']; ?>" class="edit-form">
                        <form method="post" action="../../controllers/SessionController.php">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="session_id" value="<?php echo $s['id']; ?>">
                            <label>Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($s['title']); ?>" required>
                            <label>Description</label>
                            <textarea name="description"><?php echo htmlspecialchars($s['description']); ?></textarea>
                            <div class="form-row">
                                <div class="field">
                                    <label>Scheduled Date &amp; Time</label>
                                    <input type="datetime-local" name="scheduled_at" value="<?php echo date('Y-m-d\TH:i', strtotime($s['scheduled_at'])); ?>" required>
                                </div>
                                <div class="field">
                                    <label>Duration (min)</label>
                                    <input type="number" name="duration" value="<?php echo $s['duration_minutes']; ?>" min="15">
                                </div>
                                <div class="field">
                                    <label>Max Questions</label>
                                    <input type="number" name="max_questions" value="<?php echo $s['max_questions']; ?>" min="1">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
<script>
function toggleEdit(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>
