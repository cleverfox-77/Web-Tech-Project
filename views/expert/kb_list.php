<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/KbController.php';
require_once __DIR__ . '/../../models/ExpertUser.php';
$controller = new KbController();
$data = $controller->index();
$tagModel = new Tag();
$tags = $tagModel->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Knowledge Base</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #1a5276; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 1050px; margin: 28px auto; padding: 0 16px; }
        h2 { color: #1a5276; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 6px; margin-bottom: 14px; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input[type=text], textarea, select { width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; margin-bottom: 12px; }
        textarea { height: 140px; resize: vertical; }
        .btn { padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-primary { background: #1a5276; color: white; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn-warning { background: #e67e22; color: white; }
        .btn:hover { opacity: 0.88; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
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

    <h2>Knowledge Base Articles</h2>

    <!-- Create Article -->
    <div class="panel">
        <div class="section-title">Create New Article</div>
        <form method="post" action="../../controllers/KbController.php">
            <input type="hidden" name="action" value="create">
            <label>Title *</label>
            <input type="text" name="title" placeholder="Article title..." required>
            <label>Body *</label>
            <textarea name="body" placeholder="Write the article content (longer-form reference material)..."></textarea>
            <label>Associated Tag</label>
            <select name="tag_id">
                <option value="0">-- No tag --</option>
                <?php foreach ($tags as $t): ?>
                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Create Article</button>
        </form>
    </div>

    <!-- Article List -->
    <div class="panel">
        <div class="section-title">My Articles (<?php echo count($data['articles']); ?>)</div>
        <?php if (count($data['articles']) == 0): ?>
            <p class="no-data">No articles created yet.</p>
        <?php else: ?>
        <table>
            <tr><th>Title</th><th>Tag</th><th>Views</th><th>Updated</th><th>Actions</th></tr>
            <?php foreach ($data['articles'] as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['title']); ?></td>
                <td><?php echo htmlspecialchars($a['tag_name']); ?></td>
                <td><?php echo $a['view_count']; ?></td>
                <td><?php echo date('d M Y', strtotime($a['updated_at'])); ?></td>
                <td>
                    <button class="btn btn-warning" onclick="toggleEdit('kb_<?php echo $a['id']; ?>')">Edit</button>
                    <form method="post" action="../../controllers/KbController.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="kb_id" value="<?php echo $a['id']; ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this article?')">Delete</button>
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan="5" style="padding:0;">
                    <div id="kb_<?php echo $a['id']; ?>" class="edit-form">
                        <form method="post" action="../../controllers/KbController.php">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="kb_id" value="<?php echo $a['id']; ?>">
                            <label>Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>" required>
                            <label>Body</label>
                            <textarea name="body"><?php echo htmlspecialchars($a['body']); ?></textarea>
                            <label>Tag</label>
                            <select name="tag_id">
                                <option value="0">-- No tag --</option>
                                <?php foreach ($tags as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo $a['tag_name'] == $t['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
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
