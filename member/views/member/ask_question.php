<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/QuestionController.php';
AuthMiddleware::checkMember();

$errors = array();
$old    = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ctrl   = new QuestionController();
    $result = $ctrl->ask();
    $errors = $result['errors'];
    $old    = $result['old'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ask a Question</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .navbar { background: #2c3e50; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 16px; font-size: 14px; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #2c3e50; }
        .panel { background: white; border-radius: 6px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.09); }
        label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        input[type=text], textarea {
            width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px;
            font-size: 14px; box-sizing: border-box; margin-bottom: 16px; }
        textarea { height: 160px; resize: vertical; }
        .btn { padding: 10px 24px; background: #2c3e50; color: white; border: none;
               border-radius: 4px; font-size: 14px; cursor: pointer; }
        .btn:hover { background: #34495e; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 12px; font-size: 13px; }
        .hint { font-size: 12px; color: #888; margin-top: -12px; margin-bottom: 14px; }
        .tag-suggest { position: absolute; background: white; border: 1px solid #ccc;
                       border-radius: 4px; z-index: 100; min-width: 200px; }
        .tag-suggest div { padding: 8px 12px; cursor: pointer; font-size: 13px; }
        .tag-suggest div:hover { background: #f0f0f0; }
        .tag-wrap { position: relative; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong></span>
    <div>
        <a href="dashboard.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="../../logout.php">Logout</a>
    </div>
</div>
<div class="container">
    <h2>Ask a Question</h2>
    <div class="panel">
        <?php foreach ($errors as $e): ?>
            <div class="error"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label>Title *</label>
            <input type="text" name="title" placeholder="Be specific and clear..." required
                   value="<?php echo htmlspecialchars(isset($old['title']) ? $old['title'] : ''); ?>">

            <label>Body *</label>
            <textarea name="body" placeholder="Describe your question in detail..."><?php echo htmlspecialchars(isset($old['body']) ? $old['body'] : ''); ?></textarea>

            <label>Tags</label>
            <div class="tag-wrap">
                <input type="text" name="tags" id="tagInput" placeholder="e.g. php, mysql, javascript"
                       value="<?php echo htmlspecialchars(isset($old['tags']) ? $old['tags'] : ''); ?>"
                       autocomplete="off">
                <div class="tag-suggest" id="tagSuggest" style="display:none;"></div>
            </div>
            <p class="hint">Separate tags with commas. Only existing tags will be attached.</p>

            <button type="submit" class="btn">Post Question</button>
        </form>
    </div>
</div>

<script>
// Tag autocomplete via XMLHttpRequest
var tagInput   = document.getElementById('tagInput');
var tagSuggest = document.getElementById('tagSuggest');
var tagTimer   = null;

tagInput.onkeyup = function() {
    clearTimeout(tagTimer);
    var parts   = this.value.split(',');
    var current = parts[parts.length - 1].trim();
    if (current.length < 2) { tagSuggest.style.display = 'none'; return; }

    tagTimer = setTimeout(function() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var response = JSON.parse(this.responseText);
                if (response.success && response.tags.length > 0) {
                    var html = '';
                    for (var i = 0; i < response.tags.length; i++) {
                        html += '<div onclick="selectTag(\'' + response.tags[i].name + '\')">' +
                                response.tags[i].name + ' (' + response.tags[i].question_count + ')</div>';
                    }
                    tagSuggest.innerHTML = html;
                    tagSuggest.style.display = 'block';
                } else {
                    tagSuggest.style.display = 'none';
                }
            }
        };
        xhttp.open("GET", "../../api/member_ajax.php?action=tag_autocomplete&prefix=" + encodeURIComponent(current), true);
        xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhttp.send();
    }, 300);
};

function selectTag(name) {
    var parts = tagInput.value.split(',');
    parts[parts.length - 1] = name;
    tagInput.value = parts.join(', ') + ', ';
    tagSuggest.style.display = 'none';
    tagInput.focus();
}

document.addEventListener('click', function(e) {
    if (e.target !== tagInput) { tagSuggest.style.display = 'none'; }
});
</script>
</body>
</html>
