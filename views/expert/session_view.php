<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/SessionController.php';
AuthMiddleware::checkExpert();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id == 0) { header("Location: session_list.php"); exit(); }
$controller = new SessionController();
$data = $controller->view($id);
$s = $data['session'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session: <?php echo htmlspecialchars($s['title']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #1a5276; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 950px; margin: 28px auto; padding: 0 16px; }
        h2 { color: #1a5276; margin-bottom: 4px; }
        .panel { background: white; border-radius: 6px; padding: 22px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 6px; margin-bottom: 14px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-upcoming { background: #d1ecf1; color: #0c5460; }
        .badge-active   { background: #d4edda; color: #155724; }
        .badge-ended    { background: #f8f9fa; color: #555; }
        .q-item { border: 1px solid #eee; border-radius: 6px; padding: 14px; margin-bottom: 12px; background: #fafafa; }
        .q-item.answered { border-color: #27ae60; background: #f0fff4; }
        .q-text { font-size: 14px; color: #333; margin-bottom: 8px; }
        .q-meta { font-size: 12px; color: #888; margin-bottom: 10px; }
        .q-answer { background: #eafaf1; border-left: 4px solid #27ae60; padding: 10px; border-radius: 4px; font-size: 14px; margin-top: 8px; }
        .answer-form { margin-top: 10px; }
        .answer-form textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; height: 80px; box-sizing: border-box; }
        .btn { padding: 8px 18px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .btn-primary { background: #1a5276; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger  { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.88; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .ajax-msg { font-size: 13px; font-weight: bold; margin-top: 6px; }
        .no-data { color: #aaa; text-align: center; padding: 24px; font-style: italic; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Expert Panel</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="session_list.php">&larr; All Sessions</a>
        <a href="../../logout.php">Logout</a>
    </div>
</div>
<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <h2><?php echo htmlspecialchars($s['title']); ?>
        <span class="badge badge-<?php echo $s['status']; ?>"><?php echo ucfirst($s['status']); ?></span>
    </h2>
    <p style="color:#666;font-size:14px;">
        Scheduled: <?php echo date('d M Y, H:i', strtotime($s['scheduled_at'])); ?> &mdash;
        Duration: <?php echo $s['duration_minutes']; ?> min &mdash;
        Questions: <?php echo $s['question_count']; ?>
    </p>

    <?php if ($s['description']): ?>
    <div class="panel" style="padding:14px 18px;">
        <p style="margin:0;color:#555;font-size:14px;"><?php echo nl2br(htmlspecialchars($s['description'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Session Controls -->
    <?php if ($s['status'] == 'active'): ?>
    <div class="panel" style="border-left:4px solid #e74c3c;">
        <strong style="color:#e74c3c;">&#128308; Session is LIVE</strong>
        &mdash; Members can submit questions now.
        <form method="post" action="../../controllers/SessionController.php" style="display:inline;margin-left:14px;">
            <input type="hidden" name="action" value="close">
            <input type="hidden" name="session_id" value="<?php echo $s['id']; ?>">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Close this session?')">Close Session</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Questions List -->
    <div class="panel">
        <div class="section-title">
            Session Questions (<?php echo count($data['questions']); ?>)
            <?php if ($s['status'] == 'active'): ?>
                <span id="refreshNote" style="font-size:12px;color:#888;font-weight:normal;">
                    &mdash; <a href="#" onclick="loadQuestions(); return false;">Refresh questions</a>
                </span>
            <?php endif; ?>
        </div>
        <div id="questionsList">
        <?php if (count($data['questions']) == 0): ?>
            <p class="no-data">No questions submitted yet.</p>
        <?php else: ?>
        <?php foreach ($data['questions'] as $q): ?>
        <div class="q-item <?php echo $q['is_answered'] ? 'answered' : ''; ?>" id="sq_<?php echo $q['id']; ?>">
            <div class="q-text"><strong>Q:</strong> <?php echo htmlspecialchars($q['question_text']); ?></div>
            <div class="q-meta">by @<?php echo htmlspecialchars($q['submitter_username']); ?> &mdash; <?php echo date('H:i, d M Y', strtotime($q['submitted_at'])); ?></div>

            <?php if ($q['is_answered']): ?>
                <div class="q-answer"><strong>A:</strong> <?php echo nl2br(htmlspecialchars($q['answer_text'])); ?></div>
            <?php elseif ($s['status'] == 'active'): ?>
                <div class="answer-form">
                    <textarea id="ans_<?php echo $q['id']; ?>" placeholder="Type your answer..."></textarea>
                    <button class="btn btn-success" style="margin-top:6px;" onclick="submitAnswer(<?php echo $q['id']; ?>)">Submit Answer (AJAX)</button>
                    <div class="ajax-msg" id="msg_<?php echo $q['id']; ?>"></div>
                </div>
            <?php else: ?>
                <p style="color:#aaa;font-size:13px;font-style:italic;">Not answered.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Answer a session question via XMLHttpRequest — visible to all immediately
function submitAnswer(sq_id) {
    var answer_text = document.getElementById('ans_' + sq_id).value.trim();
    var msgEl = document.getElementById('msg_' + sq_id);

    if (!answer_text) {
        msgEl.style.color = 'red';
        msgEl.innerHTML = 'Answer cannot be empty.';
        return;
    }

    msgEl.style.color = '#888';
    msgEl.innerHTML = 'Submitting...';

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.success) {
                msgEl.style.color = '#27ae60';
                msgEl.innerHTML = '&#10003; Answer submitted.';
                // Show answer inline without reload
                var card = document.getElementById('sq_' + sq_id);
                card.className = 'q-item answered';
                var ansDiv = document.createElement('div');
                ansDiv.className = 'q-answer';
                ansDiv.innerHTML = '<strong>A:</strong> ' + answer_text.replace(/\n/g, '<br>');
                card.querySelector('.answer-form').replaceWith(ansDiv);
            } else {
                msgEl.style.color = 'red';
                msgEl.innerHTML = response.message;
            }
        }
    };
    xhttp.open("POST", "../../api/expert_ajax.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send("action=answer_session_question&sq_id=" + sq_id + "&answer_text=" + encodeURIComponent(answer_text));
}

// Refresh questions list during active session
function loadQuestions() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.success) {
                buildQuestionList(response.questions);
            }
        }
    };
    xhttp.open("GET", "../../api/expert_ajax.php?action=get_session_questions&session_id=<?php echo $s['id']; ?>", true);
    xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhttp.send();
}

function buildQuestionList(questions) {
    if (questions.length == 0) {
        document.getElementById('questionsList').innerHTML = '<p class="no-data">No questions yet.</p>';
        return;
    }
    var html = '';
    for (var i = 0; i < questions.length; i++) {
        var q = questions[i];
        html += '<div class="q-item ' + (q.is_answered == 1 ? 'answered' : '') + '" id="sq_' + q.id + '">';
        html += '<div class="q-text"><strong>Q:</strong> ' + q.question_text + '</div>';
        html += '<div class="q-meta">by @' + q.submitter_username + '</div>';
        if (q.is_answered == 1) {
            html += '<div class="q-answer"><strong>A:</strong> ' + q.answer_text + '</div>';
        } else {
            html += '<div class="answer-form">';
            html += '<textarea id="ans_' + q.id + '" placeholder="Type your answer..."></textarea>';
            html += '<button class="btn btn-success" style="margin-top:6px;" onclick="submitAnswer(' + q.id + ')">Submit Answer (AJAX)</button>';
            html += '<div class="ajax-msg" id="msg_' + q.id + '"></div>';
            html += '</div>';
        }
        html += '</div>';
    }
    document.getElementById('questionsList').innerHTML = html;
}
</script>
</body>
</html>
