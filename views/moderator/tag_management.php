<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/TagController.php';
$controller = new TagController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tag Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 16px; }
        h2, h3 { color: #2c3e50; }
        .panel { background: white; border-radius: 6px; padding: 20px; margin-bottom: 24px;
                 box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        tr:hover { background: #f8f9fa; }
        input[type=text], input[type=number], select, textarea {
            padding: 8px; border: 1px solid #ccc; border-radius: 4px;
            font-size: 14px; width: 100%; box-sizing: border-box; margin-bottom: 10px; }
        .form-row { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
        .form-row .field { flex: 1; min-width: 140px; }
        .form-row label { display: block; font-weight: bold; color: #555; margin-bottom: 4px; font-size: 13px; }
        .btn { padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary  { background: #2c3e50; color: white; }
        .btn-warning  { background: #f39c12; color: white; }
        .btn-danger   { background: #e74c3c; color: white; }
        .btn-info     { background: #2980b9; color: white; }
        .btn:hover { opacity: 0.9; }
        .tag-badge { display: inline-block; background: #e8f4f8; color: #2c3e50;
                     border: 1px solid #bee5eb; padding: 3px 10px; border-radius: 14px;
                     font-size: 13px; margin: 2px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .section-title { font-size: 16px; font-weight: bold; color: #2c3e50; border-bottom: 2px solid #2c3e50;
                         padding-bottom: 6px; margin-bottom: 14px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Tag Management</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="report_queue.php">Report Queue</a>
        <a href="warnings.php">Warnings</a>
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

    <h2>Tag Taxonomy Management</h2>

    <!-- Add New Tag -->
    <div class="panel">
        <div class="section-title">Add New Tag</div>
        <form method="post" action="../../controllers/TagController.php">
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <div class="field">
                    <label>Tag Name *</label>
                    <input type="text" name="name" placeholder="e.g. javascript" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Short description of this tag">
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Add Tag</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Rename Tag -->
    <div class="panel">
        <div class="section-title">Rename / Update Tag</div>
        <form method="post" action="../../controllers/TagController.php">
            <input type="hidden" name="action" value="rename">
            <div class="form-row">
                <div class="field">
                    <label>Select Tag *</label>
                    <select name="tag_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($data['tags'] as $tag): ?>
                        <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?> (<?php echo $tag['question_count']; ?> questions)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>New Name *</label>
                    <input type="text" name="name" placeholder="New tag name" required>
                </div>
                <div class="field">
                    <label>New Description</label>
                    <input type="text" name="description" placeholder="Updated description">
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-warning">Rename</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Merge Tags -->
    <div class="panel">
        <div class="section-title">Merge Tags</div>
        <p style="color:#666;font-size:13px;">All questions tagged with the <em>source</em> tag will be retagged with the <em>destination</em> tag, and the source tag will be deleted.</p>
        <form method="post" action="../../controllers/TagController.php">
            <input type="hidden" name="action" value="merge">
            <div class="form-row">
                <div class="field">
                    <label>Source Tag (to be deleted) *</label>
                    <select name="source_id" required>
                        <option value="">-- Select source --</option>
                        <?php foreach ($data['tags'] as $tag): ?>
                        <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Destination Tag (to keep) *</label>
                    <select name="dest_id" required>
                        <option value="">-- Select destination --</option>
                        <?php foreach ($data['tags'] as $tag): ?>
                        <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-info" onclick="return confirm('Merge these tags? This cannot be undone.')">Merge</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Tag -->
    <div class="panel">
        <div class="section-title">Delete Unused Tag</div>
        <p style="color:#666;font-size:13px;">Only tags with 0 questions can be deleted.</p>
        <form method="post" action="../../controllers/TagController.php">
            <input type="hidden" name="action" value="delete">
            <div class="form-row">
                <div class="field">
                    <label>Select Tag *</label>
                    <select name="tag_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($data['tags'] as $tag): if ($tag['question_count'] == 0): ?>
                        <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this tag permanently?')">Delete</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tag Usage Statistics -->
    <div class="panel">
        <div class="section-title">Tag Usage Statistics</div>
        <table>
            <tr>
                <th>Tag Name</th>
                <th>Description</th>
                <th>Questions</th>
                <th>Total Views</th>
                <th>Status</th>
            </tr>
            <?php foreach ($data['tags'] as $tag): ?>
            <tr>
                <td><span class="tag-badge"><?php echo htmlspecialchars($tag['name']); ?></span></td>
                <td><?php echo htmlspecialchars($tag['description']); ?></td>
                <td><?php echo $tag['question_count']; ?></td>
                <td><?php echo $tag['total_views']; ?></td>
                <td>
                    <?php if ($tag['question_count'] == 0): ?>
                        <span style="color:#e74c3c;">Unused</span>
                    <?php elseif ($tag['question_count'] <= 3): ?>
                        <span style="color:#f39c12;">Sparse</span>
                    <?php else: ?>
                        <span style="color:#27ae60;">Active</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
