<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../models/Warning.php';

AuthMiddleware::checkModerator();

$session      = AuthMiddleware::getSession();
$mod_id       = $session['user_id'];
$warningModel = new Warning();

$own_warnings = $warningModel->getWarningsByModerator($mod_id);
$all_warnings = $warningModel->getAllWarnings();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Warning History</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; 
            background: #f5f5f5; }
        .navbar { background: #2c3e50; 
        color: white; padding: 12px 24px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; }
        .navbar a { 
            color: white; 
            text-decoration: none;
             margin-left: 16px;
             }
        .navbar a:hover { 
            text-decoration: underline; 
        }
        .container { 
            max-width: 1100px; 
            margin: 30px auto; 
            padding: 0 16px; }
        h2 { 
            color: #2c3e50; 
         }
        .panel { background: white; 
        border-radius: 6px; 
        padding: 22px; 
        margin-bottom: 24px;
                 box-shadow: 0 1px 4px rgba(0,0,0,0.1); 
                }
        .section-title
         { 
            font-size: 15px; 
            font-weight: bold; 
            color: #2c3e50;
                         border-bottom: 2px solid #2c3e50;
                          padding-bottom: 6px; 
                          margin-bottom: 14px; 
                        }
        table 
        { 
            width: 100%;
             border-collapse: collapse; 
            }
        th, td 
        { 
            padding: 10px 14px; 
            text-align: left; 
            border-bottom: 1px solid #eee;
             font-size: 14px; }
        th 
        { 
            background: #f8f9fa; 
            font-weight: bold; 
            color: #555;
         }
        tr:hover td
         { background: #fafafa;
         }
        .no-data 
        { 
            color: #aaa;
             font-style: italic; 
             text-align: center; 
             padding: 24px; }
        .count-badge 
        { 
            display: inline-block;
             background: #2c3e50;
              color: white;
                       padding: 3px 12px;
                        border-radius: 20px;
                         font-size: 13px; 
                         margin-left: 8px; 
                        }
        .alert
         { 
            padding: 12px 16px;
             border-radius: 4px; 
             margin-bottom: 16px; 
            }
        .alert-success
         { 
            background: #d4edda; color: #155724;
         }
        .alert-error  
         { background: #f8d7da;
          color: #721c24; 
        }
    </style>
</head>
<body>
<div class="navbar">
    <span><strong>Community Forum</strong> &mdash; Warning History</span>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="report_queue.php">Report Queue</a>
        <a href="tag_management.php">Tags</a>
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

    <h2>Warning History</h2>

    <!-- My Own Warnings -->
    <div class="panel">
        <div class="section-title">
            My Issued Warnings
            <span class="count-badge"><?php echo count($own_warnings); ?></span>
        </div>
        <?php if (count($own_warnings) == 0): ?>
            <p class="no-data">You have not issued any warnings yet.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Warned User</th>
                <th>Reason</th>
                <th>Date</th>
            </tr>
            <?php foreach ($own_warnings as $i => $w): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($w['warned_name']); ?>
                    <small style="color:#888;">&nbsp;@<?php echo htmlspecialchars($w['warned_username']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($w['reason']); ?></td>
                <td><?php echo htmlspecialchars($w['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

    <!-- All Platform Warnings -->
    <div class="panel">
        <div class="section-title">
            All Warnings &mdash; Platform-Wide
            <span class="count-badge"><?php echo count($all_warnings); ?></span>
        </div>
        <?php if (count($all_warnings) == 0): ?>
            <p class="no-data">No warnings have been issued on the platform yet.</p>
        <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <th>Warned User</th>
                <th>Reason</th>
                <th>Issued By</th>
                <th>Date</th>
            </tr>
            <?php foreach ($all_warnings as $i => $w): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($w['warned_name']); ?>
                    <small style="color:#888;">&nbsp;@<?php echo htmlspecialchars($w['warned_username']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($w['reason']); ?></td>
                <td><?php echo htmlspecialchars($w['mod_username']); ?></td>
                <td><?php echo htmlspecialchars($w['created_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
