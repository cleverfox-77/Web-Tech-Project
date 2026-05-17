<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/ReportExportController.php';
require_once __DIR__ . '/../../models/Analytics.php';

AuthMiddleware::checkAdmin();

// Load analytics data
$analyticsModel = new Analytics();
$daily          = $analyticsModel->getDailyActivity();
$userGrowth     = $analyticsModel->getUserGrowth();
$tagPopularity  = $analyticsModel->getTagPopularity();
$leaderboard    = $analyticsModel->getReputationLeaderboard();
$activeExperts  = $analyticsModel->getMostActiveExperts();
$topQuestions   = $analyticsModel->getMostViewedQuestions();
$contributions  = $analyticsModel->getExpertContributions();

// Monthly report form
$reportCtrl = new ReportExportController();
$reportData = $reportCtrl->index();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Analytics &amp; Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #1a252f; color: white; padding: 12px 28px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: #ccc; text-decoration: none; margin-left: 18px; font-size: 14px; }
        .navbar a:hover { color: white; }
        .container { max-width: 1150px; margin: 30px auto; padding: 0 16px; }
        h2 { color: #1a252f; }
        .panel { background: white; border-radius: 8px; padding: 22px; margin-bottom: 24px;
                 box-shadow: 0 1px 5px rgba(0,0,0,0.09); }
        .section-title { font-size: 15px; font-weight: bold; color: #1a252f; border-bottom: 2px solid #1a252f;
                         padding-bottom: 6px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; font-weight: bold; color: #555; }
        tr:hover td { background: #fafafa; }
        .rank { font-weight: bold; color: #1a252f; }
        .two-col { display: flex; gap: 22px; flex-wrap: wrap; }
        .two-col .panel { flex: 1; min-width: 300px; margin-bottom: 0; }
        .bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .bar-label { width: 130px; font-size: 13px; color: #555; text-align: right; flex-shrink: 0; }
        .bar-wrap  { flex: 1; background: #eee; border-radius: 3px; height: 18px; overflow: hidden; }
        .bar-fill  { height: 18px; border-radius: 3px; background: #1a252f; }
        .bar-fill.green  { background: #27ae60; }
        .bar-fill.blue   { background: #2980b9; }
        .bar-val   { width: 40px; font-size: 12px; color: #888; }
        input[type=number], select { padding: 9px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn { padding: 9px 22px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #1a252f; color: white; }
        .btn:hover { opacity: 0.88; }
        .form-row { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
        .form-row .field { flex: 1; min-width: 120px; }
        .form-row label { display: block; font-weight: bold; color: #555; font-size: 13px; margin-bottom: 5px; }
        .report-grid { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 18px; }
        .report-card { background: #f8f9fa; border-radius: 6px; padding: 18px 24px; flex: 1; min-width: 130px;
                       text-align: center; border-top: 4px solid #1a252f; }
        .report-card.blue   { border-color: #2980b9; } .report-card.blue   .rval { color: #2980b9; }
        .report-card.green  { border-color: #27ae60; } .report-card.green  .rval { color: #27ae60; }
        .report-card.orange { border-color: #e67e22; } .report-card.orange .rval { color: #e67e22; }
        .report-card.red    { border-color: #e74c3c; } .report-card.red    .rval { color: #e74c3c; }
        .report-card .rlabel { font-size: 12px; color: #666; margin-bottom: 6px; }
        .report-card .rval   { font-size: 34px; font-weight: bold; color: #1a252f; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 16px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
        .badge-expert    { background: #eafaf1; color: #27ae60; }
        .badge-member    { background: #eaf2ff; color: #2980b9; }
        .badge-moderator { background: #fef9e7; color: #e67e22; }
        .badge-admin     { background: #f5eef8; color: #8e44ad; }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Analytics &amp; Reports</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="user_management.php">Users</a>
        <a href="expert_applications.php">Applications</a>
        <a href="platform_settings.php">Settings</a>
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

    <h2>Analytics &amp; Reports</h2>

    <!-- Daily Activity (last 7 days) -->
    <div class="panel">
        <div class="section-title">Questions &amp; Answers — Last 7 Days</div>
        <?php
        $maxQ = 1;
        foreach ($daily['questions'] as $d) { if ($d['question_count'] > $maxQ) $maxQ = $d['question_count']; }
        foreach ($daily['answers']   as $d) { if ($d['answer_count']   > $maxQ) $maxQ = $d['answer_count']; }
        ?>
        <?php foreach ($daily['questions'] as $d):
            $pct = $maxQ > 0 ? round(($d['question_count'] / $maxQ) * 100) : 0;
        ?>
        <div class="bar-row">
            <div class="bar-label"><?php echo $d['day']; ?> Q</div>
            <div class="bar-wrap"><div class="bar-fill blue" style="width:<?php echo $pct; ?>%;"></div></div>
            <div class="bar-val"><?php echo $d['question_count']; ?></div>
        </div>
        <?php endforeach; ?>
        <?php foreach ($daily['answers'] as $d):
            $pct = $maxQ > 0 ? round(($d['answer_count'] / $maxQ) * 100) : 0;
        ?>
        <div class="bar-row">
            <div class="bar-label"><?php echo $d['day']; ?> A</div>
            <div class="bar-wrap"><div class="bar-fill green" style="width:<?php echo $pct; ?>%;"></div></div>
            <div class="bar-val"><?php echo $d['answer_count']; ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (count($daily['questions']) == 0 && count($daily['answers']) == 0): ?>
            <p style="color:#aaa;text-align:center;">No activity data for the last 7 days.</p>
        <?php endif; ?>
    </div>

    <div class="two-col" style="margin-bottom:24px;">
        <!-- Tag Popularity -->
        <div class="panel">
            <div class="section-title">Top 10 Tags by Question Count</div>
            <?php
            $maxT = 1;
            foreach ($tagPopularity as $t) { if ($t['question_count'] > $maxT) $maxT = $t['question_count']; }
            ?>
            <?php foreach ($tagPopularity as $t):
                $pct = $maxT > 0 ? round(($t['question_count'] / $maxT) * 100) : 0;
            ?>
            <div class="bar-row">
                <div class="bar-label"><?php echo htmlspecialchars($t['name']); ?></div>
                <div class="bar-wrap"><div class="bar-fill" style="width:<?php echo $pct; ?>%;"></div></div>
                <div class="bar-val"><?php echo $t['question_count']; ?></div>
            </div>
            <?php endforeach; ?>
            <?php if (count($tagPopularity) == 0): ?>
                <p style="color:#aaa;text-align:center;">No tags found.</p>
            <?php endif; ?>
        </div>

        <!-- Most Active Experts -->
        <div class="panel">
            <div class="section-title">Most Active Experts (by Answers)</div>
            <table>
                <tr><th>#</th><th>Expert</th><th>Domain</th><th>Answers</th></tr>
                <?php foreach ($activeExperts as $i => $e): ?>
                <tr>
                    <td class="rank"><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($e['name']); ?><br>
                        <small style="color:#888;">@<?php echo htmlspecialchars($e['username']); ?></small></td>
                    <td><?php echo htmlspecialchars($e['expert_domain']); ?></td>
                    <td><?php echo $e['answer_count']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (count($activeExperts) == 0): ?>
                <tr><td colspan="4" style="text-align:center;color:#aaa;">No expert data.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Reputation Leaderboard -->
    <div class="panel">
        <div class="section-title">Reputation Leaderboard — Top 20</div>
        <table>
            <tr><th>Rank</th><th>Name</th><th>Username</th><th>Role</th><th>Reputation</th></tr>
            <?php foreach ($leaderboard as $i => $u): ?>
            <tr>
                <td class="rank"><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td>@<?php echo htmlspecialchars($u['username']); ?></td>
                <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                <td><strong><?php echo $u['reputation']; ?></strong></td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($leaderboard) == 0): ?>
            <tr><td colspan="5" style="text-align:center;color:#aaa;">No user data.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Most Viewed Questions -->
    <div class="panel">
        <div class="section-title">Most Viewed Questions</div>
        <table>
            <tr><th>#</th><th>Title</th><th>Author</th><th>Views</th><th>Votes</th><th>Answers</th></tr>
            <?php foreach ($topQuestions as $i => $q): ?>
            <tr>
                <td class="rank"><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($q['title']); ?></td>
                <td>@<?php echo htmlspecialchars($q['author_username']); ?></td>
                <td><?php echo $q['view_count']; ?></td>
                <td><?php echo $q['vote_score']; ?></td>
                <td><?php echo $q['answer_count']; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($topQuestions) == 0): ?>
            <tr><td colspan="6" style="text-align:center;color:#aaa;">No questions found.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Expert Contribution Report -->
    <div class="panel">
        <div class="section-title">Expert Contribution Report</div>
        <table>
            <tr><th>Expert</th><th>Domain</th><th>FAQs</th><th>KB Articles</th><th>Sessions</th><th>Total Views</th></tr>
            <?php foreach ($contributions as $c): ?>
            <tr>
                <td><?php echo htmlspecialchars($c['name']); ?><br>
                    <small style="color:#888;">@<?php echo htmlspecialchars($c['username']); ?></small></td>
                <td><?php echo htmlspecialchars($c['expert_domain']); ?></td>
                <td><?php echo $c['faq_count']; ?></td>
                <td><?php echo $c['kb_count']; ?></td>
                <td><?php echo $c['session_count']; ?></td>
                <td><strong><?php echo $c['total_views']; ?></strong></td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($contributions) == 0): ?>
            <tr><td colspan="6" style="text-align:center;color:#aaa;">No expert data available.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Monthly Health Report -->
    <div class="panel">
        <div class="section-title">Generate Monthly Platform Health Report</div>
        <form method="post" action="analytics_report.php">
            <div class="form-row">
                <div class="field">
                    <label>Month (1-12) *</label>
                    <input type="number" name="month" min="1" max="12"
                        value="<?php echo $reportData['month'] ? $reportData['month'] : date('n'); ?>" required>
                </div>
                <div class="field">
                    <label>Year *</label>
                    <input type="number" name="year" min="2000"
                        value="<?php echo $reportData['year'] ? $reportData['year'] : date('Y'); ?>" required>
                </div>
                <div class="field" style="flex:0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Generate</button>
                </div>
            </div>
        </form>

        <?php if ($reportData['report'] !== null):
            $r = $reportData['report'];
        ?>
        <p style="margin-top:18px;color:#555;font-size:14px;">
            Report for: <strong><?php echo date('F', mktime(0,0,0,$reportData['month'],1)); ?> <?php echo $reportData['year']; ?></strong>
        </p>
        <div class="report-grid">
            <div class="report-card blue">
                <div class="rlabel">New Users</div>
                <div class="rval"><?php echo $r['new_users']; ?></div>
            </div>
            <div class="report-card">
                <div class="rlabel">Questions</div>
                <div class="rval"><?php echo $r['questions']; ?></div>
            </div>
            <div class="report-card green">
                <div class="rlabel">Answers</div>
                <div class="rval"><?php echo $r['answers']; ?></div>
            </div>
            <div class="report-card orange">
                <div class="rlabel">Reports Processed</div>
                <div class="rval"><?php echo $r['reports_processed']; ?></div>
            </div>
            <div class="report-card red">
                <div class="rlabel">Warnings Issued</div>
                <div class="rval"><?php echo $r['warnings']; ?></div>
            </div>
            <div class="report-card">
                <div class="rlabel">Applications</div>
                <div class="rval"><?php echo $r['applications']; ?></div>
            </div>
            <div class="report-card green">
                <div class="rlabel">Approved Experts</div>
                <div class="rval"><?php echo $r['approved_apps']; ?></div>
            </div>
        </div>

        <h3 style="margin-top:24px;color:#1a252f;">Top 5 Active Users This Month</h3>
        <table>
            <tr><th>#</th><th>Name</th><th>Username</th><th>Role</th><th>Activity (Q+A)</th></tr>
            <?php foreach ($r['top_users'] as $i => $u): ?>
            <tr>
                <td class="rank"><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td>@<?php echo htmlspecialchars($u['username']); ?></td>
                <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                <td><?php echo $u['activity_count']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
