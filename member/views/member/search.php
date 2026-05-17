<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/QuestionController.php';
require_once __DIR__ . '/../../models/Tag.php';
AuthMiddleware::checkMember();
$controller = new QuestionController();
$data = $controller->search();
$tagModel = new Tag();
$all_tags = $tagModel->getAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Questions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 900px; margin: 28px auto; padding: 0 16px; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 6px; margin-bottom: 14px; }
        .search-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
        .search-row .field { flex: 1; min-width: 140px; }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input[type=text], input[type=date], select {
            width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px;
            font-size: 14px; box-sizing: border-box; }
        .btn { padding: 10px 22px; background: #2c3e50; color: white; border: none; border-radius: 4px; font-size: 14px; cursor: pointer; }
        .btn:hover { background: #34495e; }
        .q-card { background: #fafafa; border: 1px solid #eee; border-radius: 6px; padding: 14px 18px; margin-bottom: 12px; }
        .q-card h3 { margin: 0 0 6px; font-size: 15px; }
        .q-card h3 a { color: #2c3e50; text-decoration: none; }
        .q-card h3 a:hover { text-decoration: underline; }
        .q-meta { font-size: 12px; color: #888; display: flex; gap: 12px; flex-wrap: wrap; }
        .badge-status { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
        .badge-open   { background: #d4edda; color: #155724; }
        .badge-closed { background: #f8d7da; color: #721c24; }
        .no-data { color: #aaa; text-align: center; padding: 30px; font-style: italic; }
        #ajaxResults { }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong></span>
    <div>
        <a href="dashboard.php">Home</a>
        <a href="notifications.php">Notifications</a>
        <a href="profile.php">Profile</a>
        <a href="../../logout.php">Logout</a>
    </div>
</div>
<div class="container">
    <h2 style="color:#2c3e50;">Search Questions</h2>
    <div class="panel">
        <!-- Search form — results via AJAX -->
        <div class="search-row">
            <div class="field">
                <label>Keyword *</label>
                <input type="text" id="searchKeyword" placeholder="Search questions..." value="<?php echo htmlspecialchars($data['keyword']); ?>">
            </div>
            <div class="field">
                <label>Tag</label>
                <select id="searchTag">
                    <option value="">All Tags</option>
                    <?php foreach ($all_tags as $t): ?>
                    <option value="<?php echo htmlspecialchars($t['name']); ?>" <?php echo $data['tag'] == $t['name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['name']); ?> (<?php echo $t['question_count']; ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Status</label>
                <select id="searchStatus">
                    <option value="">All</option>
                    <option value="open"   <?php echo $data['status'] == 'open'   ? 'selected' : ''; ?>>Open</option>
                    <option value="closed" <?php echo $data['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
            <div class="field">
                <label>From</label>
                <input type="date" id="searchFrom" value="<?php echo htmlspecialchars($data['from_date']); ?>">
            </div>
            <div class="field">
                <label>To</label>
                <input type="date" id="searchTo" value="<?php echo htmlspecialchars($data['to_date']); ?>">
            </div>
            <div class="field" style="flex:0;">
                <label>&nbsp;</label>
                <button class="btn" onclick="doSearch()">Search</button>
            </div>
        </div>
    </div>

    <div id="ajaxResults">
        <?php if (!empty($data['results'])): ?>
        <div class="section-title"><?php echo count($data['results']); ?> result(s) for "<?php echo htmlspecialchars($data['keyword']); ?>"</div>
        <?php foreach ($data['results'] as $q): ?>
        <div class="q-card">
            <h3><a href="question_view.php?id=<?php echo $q['id']; ?>"><?php echo htmlspecialchars($q['title']); ?></a></h3>
            <div class="q-meta">
                <span>&#9650; <?php echo $q['vote_score']; ?> votes</span>
                <span>&#128172; <?php echo $q['answer_count']; ?> answers</span>
                <span class="badge-status badge-<?php echo $q['status']; ?>"><?php echo ucfirst($q['status']); ?></span>
                <span>by @<?php echo htmlspecialchars($q['author_username']); ?></span>
                <span><?php echo date('d M Y', strtotime($q['created_at'])); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// AJAX search using XMLHttpRequest
function doSearch() {
    var keyword = document.getElementById('searchKeyword').value.trim();
    var tag     = document.getElementById('searchTag').value;
    var status  = document.getElementById('searchStatus').value;
    var from    = document.getElementById('searchFrom').value;
    var to      = document.getElementById('searchTo').value;

    if (!keyword) { alert('Please enter a keyword.'); return; }

    document.getElementById('ajaxResults').innerHTML = '<p style="color:#888;text-align:center;">Searching...</p>';

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            var html = '';
            if (!response.success || response.results.length == 0) {
                html = '<p class="no-data">No questions found.</p>';
            } else {
                html += '<div class="section-title">' + response.results.length + ' result(s) for "' + keyword + '"</div>';
                for (var i = 0; i < response.results.length; i++) {
                    var q = response.results[i];
                    html += '<div class="q-card">';
                    html += '<h3><a href="question_view.php?id=' + q.id + '">' + q.title + '</a></h3>';
                    html += '<div class="q-meta">';
                    html += '<span>&#9650; ' + q.vote_score + ' votes</span>';
                    html += '<span>&#128172; ' + q.answer_count + ' answers</span>';
                    html += '<span class="badge-status badge-' + q.status + '">' + q.status + '</span>';
                    html += '<span>by @' + q.author_username + '</span>';
                    html += '</div></div>';
                }
            }
            document.getElementById('ajaxResults').innerHTML = html;
        }
    };
    var params = 'action=search_questions&q=' + encodeURIComponent(keyword);
    if (tag)    params += '&tag='       + encodeURIComponent(tag);
    if (status) params += '&status='    + encodeURIComponent(status);
    if (from)   params += '&from_date=' + encodeURIComponent(from);
    if (to)     params += '&to_date='   + encodeURIComponent(to);

    xhttp.open("GET", "../../api/member_ajax.php?" + params, true);
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send();
}
</script>
</body>
</html>
