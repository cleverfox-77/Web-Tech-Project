<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/QuestionController.php';
AuthMiddleware::checkMember();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id == 0) { header("Location: dashboard.php"); exit(); }
$controller = new QuestionController();
$data = $controller->view($id);
$q       = $data['question'];
$user_id = $data['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($q['title']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 900px; margin: 28px auto; padding: 0 16px; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        h2 { color: #2c3e50; margin-top: 0; }
        .meta { font-size: 13px; color: #888; margin-bottom: 14px; }
        .body-text { font-size: 15px; line-height: 1.7; color: #333; }
        .tag { display: inline-block; background: #e8f4f8; color: #2c3e50; border: 1px solid #bee5eb;
               padding: 3px 10px; border-radius: 14px; font-size: 12px; margin: 2px; }
        .vote-row { display: flex; align-items: center; gap: 10px; margin-top: 14px; }
        .vote-btn { padding: 6px 14px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer;
                    background: white; font-size: 13px; }
        .vote-btn:hover { background: #f0f0f0; }
        .vote-score { font-size: 18px; font-weight: bold; color: #2c3e50; }
        .section-title { font-size: 15px; font-weight: bold; color: #2c3e50; border-bottom: 2px solid #2c3e50; padding-bottom: 6px; margin-bottom: 14px; }
        .answer-card { border: 1px solid #eee; border-radius: 6px; padding: 16px; margin-bottom: 14px; background: #fafafa; }
        .answer-card.accepted { border-color: #27ae60; background: #f0fff4; }
        .expert-badge { display: inline-block; background: #27ae60; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
        .accept-badge { display: inline-block; background: #27ae60; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
        .comment-list { margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; }
        .comment-item { font-size: 13px; color: #555; margin-bottom: 6px; }
        .comment-item .by { color: #888; }
        textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; height: 80px; box-sizing: border-box; }
        .btn { padding: 7px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-primary { background: #2c3e50; color: white; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn:hover { opacity: 0.88; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .edit-form { display: none; margin-top: 10px; }
        input[type=text] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; margin-bottom: 8px; }
        .report-form { display: none; margin-top: 8px; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong></span>
    <div>
        <a href="dashboard.php">Home</a>
        <a href="search.php">Search</a>
        <a href="notifications.php">Notifications</a>
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

    <!-- Question -->
    <div class="panel">
        <h2><?php echo htmlspecialchars($q['title']); ?></h2>
        <div class="meta">
            Asked by <strong><?php echo htmlspecialchars($q['author_username']); ?></strong>
            (rep: <?php echo $q['author_reputation']; ?>) &mdash;
            <?php echo date('d M Y, H:i', strtotime($q['created_at'])); ?> &mdash;
            Status: <strong><?php echo ucfirst($q['status']); ?></strong>
        </div>

        <div class="body-text"><?php echo nl2br(htmlspecialchars($q['body'])); ?></div>

        <div style="margin-top:12px;">
            <?php foreach ($data['tags'] as $t): ?>
                <span class="tag"><?php echo htmlspecialchars($t['name']); ?></span>
            <?php endforeach; ?>
        </div>

        <!-- Vote buttons — AJAX -->
        <div class="vote-row">
            <button class="vote-btn" onclick="castVote('question', <?php echo $q['id']; ?>, 1)">&#9650; Upvote</button>
            <span class="vote-score" id="score_question_<?php echo $q['id']; ?>"><?php echo $q['vote_score']; ?></span>
            <button class="vote-btn" onclick="castVote('question', <?php echo $q['id']; ?>, -1)">&#9660; Downvote</button>

            <?php if ($q['author_id'] == $user_id): ?>
                <button class="btn btn-warning" onclick="toggleForm('edit_q')">Edit</button>
                <form method="post" action="../../controllers/QuestionController.php" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this question?')">Delete</button>
                </form>
            <?php endif; ?>

            <!-- Report question -->
            <button class="btn" style="background:#95a5a6;color:white;" onclick="toggleForm('report_q_<?php echo $q['id']; ?>')">Report</button>
        </div>

        <!-- Edit question form -->
        <?php if ($q['author_id'] == $user_id): ?>
        <div id="edit_q" class="edit-form">
            <form method="post" action="../../controllers/QuestionController.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                <label style="font-weight:bold;color:#555;font-size:13px;">Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($q['title']); ?>" required>
                <label style="font-weight:bold;color:#555;font-size:13px;">Body</label>
                <textarea name="body" style="height:120px;"><?php echo htmlspecialchars($q['body']); ?></textarea>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Report form -->
        <div id="report_q_<?php echo $q['id']; ?>" class="report-form">
            <form method="post" action="../../controllers/ReportController.php">
                <input type="hidden" name="entity_type" value="question">
                <input type="hidden" name="entity_id" value="<?php echo $q['id']; ?>">
                <input type="text" name="reason" placeholder="Reason for reporting..." required style="margin-top:8px;">
                <button type="submit" class="btn btn-danger">Submit Report</button>
            </form>
        </div>

        <!-- Comments on question -->
        <div class="comment-list">
            <?php foreach ($data['question_comments'] as $c): ?>
            <div class="comment-item">
                <?php echo htmlspecialchars($c['body']); ?>
                <span class="by">— @<?php echo htmlspecialchars($c['author_username']); ?>, <?php echo date('d M Y', strtotime($c['created_at'])); ?></span>
                <?php if ($c['author_id'] == $user_id): ?>
                    <form method="post" action="../../controllers/CommentController.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="comment_id" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                        <button type="submit" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:12px;">delete</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <form method="post" action="../../controllers/CommentController.php" style="margin-top:8px;">
                <input type="hidden" name="action" value="post">
                <input type="hidden" name="entity_type" value="question">
                <input type="hidden" name="entity_id" value="<?php echo $q['id']; ?>">
                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                <input type="text" name="body" placeholder="Add a comment..." style="width:70%;margin-right:8px;">
                <button type="submit" class="btn btn-primary">Comment</button>
            </form>
        </div>
    </div>

    <!-- Answers -->
    <div class="section-title"><?php echo count($data['answers']); ?> Answer(s)</div>

    <?php foreach ($data['answers'] as $a): ?>
    <div class="answer-card <?php echo $a['is_accepted'] ? 'accepted' : ''; ?>">
        <div class="meta">
            <?php if ($a['is_accepted']): ?><span class="accept-badge">&#10003; Accepted</span>&nbsp;<?php endif; ?>
            <?php if ($a['is_expert_answer']): ?><span class="expert-badge">Expert &#10003;</span>&nbsp;<?php endif; ?>
            <strong><?php echo htmlspecialchars($a['author_username']); ?></strong>
            (<?php echo ucfirst($a['author_role']); ?>, rep: <?php echo $a['author_reputation']; ?>)
            &mdash; <?php echo date('d M Y', strtotime($a['created_at'])); ?>
        </div>

        <div class="body-text"><?php echo nl2br(htmlspecialchars($a['body'])); ?></div>

        <div class="vote-row" style="margin-top:10px;">
            <button class="vote-btn" onclick="castVote('answer', <?php echo $a['id']; ?>, 1)">&#9650;</button>
            <span class="vote-score" id="score_answer_<?php echo $a['id']; ?>"><?php echo $a['vote_score']; ?></span>
            <button class="vote-btn" onclick="castVote('answer', <?php echo $a['id']; ?>, -1)">&#9660;</button>

            <!-- Accept answer — only question author -->
            <?php if ($q['author_id'] == $user_id && !$a['is_accepted']): ?>
            <form method="post" action="../../controllers/AnswerController.php" style="display:inline;">
                <input type="hidden" name="action" value="accept">
                <input type="hidden" name="answer_id" value="<?php echo $a['id']; ?>">
                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                <button type="submit" class="btn btn-success">&#10003; Accept</button>
            </form>
            <?php endif; ?>

            <!-- Edit / Delete own answer -->
            <?php if ($a['author_id'] == $user_id): ?>
                <button class="btn btn-warning" onclick="toggleForm('edit_a_<?php echo $a['id']; ?>')">Edit</button>
                <form method="post" action="../../controllers/AnswerController.php" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="answer_id" value="<?php echo $a['id']; ?>">
                    <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this answer?')">Delete</button>
                </form>
            <?php endif; ?>
            <button class="btn" style="background:#95a5a6;color:white;" onclick="toggleForm('report_a_<?php echo $a['id']; ?>')">Report</button>
        </div>

        <!-- Edit answer form -->
        <?php if ($a['author_id'] == $user_id): ?>
        <div id="edit_a_<?php echo $a['id']; ?>" class="edit-form">
            <form method="post" action="../../controllers/AnswerController.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="answer_id" value="<?php echo $a['id']; ?>">
                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                <textarea name="body"><?php echo htmlspecialchars($a['body']); ?></textarea>
                <button type="submit" class="btn btn-primary" style="margin-top:6px;">Save</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Report answer form -->
        <div id="report_a_<?php echo $a['id']; ?>" class="report-form">
            <form method="post" action="../../controllers/ReportController.php">
                <input type="hidden" name="entity_type" value="answer">
                <input type="hidden" name="entity_id" value="<?php echo $a['id']; ?>">
                <input type="text" name="reason" placeholder="Reason for reporting..." required>
                <button type="submit" class="btn btn-danger">Submit Report</button>
            </form>
        </div>

        <!-- Answer comments -->
        <div class="comment-list">
            <?php foreach ($data['answer_comments'][$a['id']] as $c): ?>
            <div class="comment-item">
                <?php echo htmlspecialchars($c['body']); ?>
                <span class="by">— @<?php echo htmlspecialchars($c['author_username']); ?></span>
                <?php if ($c['author_id'] == $user_id): ?>
                    <form method="post" action="../../controllers/CommentController.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="comment_id" value="<?php echo $c['id']; ?>">
                        <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                        <button type="submit" style="background:none;border:none;color:#e74c3c;cursor:pointer;font-size:12px;">delete</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <form method="post" action="../../controllers/CommentController.php" style="margin-top:8px;">
                <input type="hidden" name="action" value="post">
                <input type="hidden" name="entity_type" value="answer">
                <input type="hidden" name="entity_id" value="<?php echo $a['id']; ?>">
                <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                <input type="text" name="body" placeholder="Add a comment..." style="width:70%;margin-right:8px;">
                <button type="submit" class="btn btn-primary">Comment</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Post Answer -->
    <?php if ($q['status'] == 'open'): ?>
    <div class="panel">
        <div class="section-title">Your Answer</div>
        <form method="post" action="../../controllers/AnswerController.php">
            <input type="hidden" name="action" value="post">
            <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
            <textarea name="body" placeholder="Write your answer here..." style="height:140px;"></textarea>
            <button type="submit" class="btn btn-primary" style="margin-top:8px;">Post Answer</button>
        </form>
    </div>
    <?php else: ?>
    <div class="panel" style="color:#888;text-align:center;">This question is closed and cannot receive new answers.</div>
    <?php endif; ?>
</div>

<script>
function toggleForm(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}

// Vote via XMLHttpRequest
function castVote(entity_type, entity_id, value) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.success) {
                document.getElementById('score_' + entity_type + '_' + entity_id).innerHTML = response.new_score;
            } else {
                alert(response.message);
            }
        }
    };
    xhttp.open("POST", "../../api/member_ajax.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send("action=cast_vote&entity_type=" + entity_type + "&entity_id=" + entity_id + "&value=" + value);
}
</script>
</body>
</html>
