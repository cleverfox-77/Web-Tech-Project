<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';
$controller = new DashboardController();
$data = $controller->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .navbar a:hover { text-decoration: underline; }
        .notif-badge { background: #e74c3c; color: white; border-radius: 50%; padding: 2px 7px; font-size: 11px; margin-left: 2px; }
        .container { max-width: 900px; margin: 28px auto; padding: 0 16px; }
        .tabs { display: flex; gap: 0; margin-bottom: 20px; }
        .tab { padding: 9px 20px; background: white; border: 1px solid #ddd; cursor: pointer;
               font-size: 14px; color: #555; text-decoration: none; }
        .tab:first-child { border-radius: 4px 0 0 4px; }
        .tab:last-child  { border-radius: 0 4px 4px 0; }
        .tab.active { background: #2c3e50; color: white; border-color: #2c3e50; }
        #questionFeed { }
        .q-card { background: white; border-radius: 6px; padding: 16px 20px; margin-bottom: 14px;
                  box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .q-card h3 { margin: 0 0 6px; font-size: 16px; }
        .q-card h3 a { color: #2c3e50; text-decoration: none; }
        .q-card h3 a:hover { text-decoration: underline; }
        .q-meta { font-size: 12px; color: #888; display: flex; gap: 14px; flex-wrap: wrap; }
        .q-meta span { display: flex; align-items: center; gap: 4px; }
        .badge-status { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
        .badge-open   { background: #d4edda; color: #155724; }
        .badge-closed { background: #f8d7da; color: #721c24; }
        .ask-btn { display: inline-block; background: #2c3e50; color: white; padding: 10px 22px;
                   border-radius: 4px; text-decoration: none; font-size: 14px; margin-bottom: 18px; }
        .ask-btn:hover { background: #34495e; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .loading { color: #888; font-style: italic; padding: 20px; text-align: center; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong></span>
    <div>
        <a href="dashboard.php">Home</a>
        <a href="search.php">Search</a>
        <a href="notifications.php">
            Notifications
            <?php if ($data['unread'] > 0): ?>
                <span class="notif-badge"><?php echo $data['unread']; ?></span>
            <?php endif; ?>
        </a>
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

    <a href="ask_question.php" class="ask-btn">+ Ask a Question</a>

    <!-- Feed Tabs — switch via AJAX without page reload -->
    <div class="tabs">
        <a href="#" class="tab <?php echo $data['feed'] == 'newest'     ? 'active' : ''; ?>" onclick="loadFeed('newest'); return false;">Newest</a>
        <a href="#" class="tab <?php echo $data['feed'] == 'unanswered' ? 'active' : ''; ?>" onclick="loadFeed('unanswered'); return false;">Unanswered</a>
        <a href="#" class="tab <?php echo $data['feed'] == 'most_voted' ? 'active' : ''; ?>" onclick="loadFeed('most_voted'); return false;">Most Voted</a>
        <a href="#" class="tab <?php echo $data['feed'] == 'trending'   ? 'active' : ''; ?>" onclick="loadFeed('trending'); return false;">Trending</a>
    </div>

    <div id="questionFeed">
        <?php if (count($data['questions']) == 0): ?>
            <p style="color:#aaa;text-align:center;padding:30px;">No questions found.</p>
        <?php endif; ?>
        <?php foreach ($data['questions'] as $q): ?>
        <div class="q-card">
            <h3><a href="question_view.php?id=<?php echo $q['id']; ?>"><?php echo htmlspecialchars($q['title']); ?></a></h3>
            <div class="q-meta">
                <span>&#9650; <?php echo $q['vote_score']; ?> votes</span>
                <span>&#128172; <?php echo $q['answer_count']; ?> answers</span>
                <span>&#128065; <?php echo $q['view_count']; ?> views</span>
                <span><span class="badge-status badge-<?php echo $q['status']; ?>"><?php echo ucfirst($q['status']); ?></span></span>
                <span>by <?php echo htmlspecialchars($q['author_username']); ?></span>
                <span><?php echo date('d M Y', strtotime($q['created_at'])); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// AJAX feed switching using XMLHttpRequest
function loadFeed(type) {
    // Update active tab
    var tabs = document.querySelectorAll('.tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].className = 'tab';
    }
    event.target.className = 'tab active';

    document.getElementById('questionFeed').innerHTML = '<p class="loading">Loading...</p>';

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.success) {
                var html = '';
                if (response.questions.length == 0) {
                    html = '<p style="color:#aaa;text-align:center;padding:30px;">No questions found.</p>';
                }
                for (var i = 0; i < response.questions.length; i++) {
                    var q = response.questions[i];
                    html += '<div class="q-card">';
                    html += '<h3><a href="question_view.php?id=' + q.id + '">' + q.title + '</a></h3>';
                    html += '<div class="q-meta">';
                    html += '<span>&#9650; ' + q.vote_score + ' votes</span>';
                    html += '<span>&#128172; ' + q.answer_count + ' answers</span>';
                    html += '<span>&#128065; ' + q.view_count + ' views</span>';
                    html += '<span>by ' + q.author_username + '</span>';
                    html += '</div></div>';
                }
                document.getElementById('questionFeed').innerHTML = html;
            }
        }
    };
    xhttp.open("GET", "../../api/member_ajax.php?action=get_feed&feed=" + type, true);
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send();
}
</script>
</body>
</html>
