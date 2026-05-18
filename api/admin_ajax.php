<?php
/*
 * admin_ajax.php
 * Dedicated AJAX endpoint for admin quick actions.
 * Returns JSON to XMLHttpRequest calls from the admin views.
 *
 * Supported actions:
 *   GET  search_user          — live user search for user_management.php
 *   POST approve_application  — quick approve expert application without page reload
 *   POST toggle_user_status   — quick activate / deactivate a user account
 *   GET  get_pending_count    — returns current pending application count
 *   GET  get_dashboard_stats  — returns live platform counts for dashboard refresh
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ExpertApplication.php';
require_once __DIR__ . '/../config/Database.php';

// Block direct browser access — only XMLHttpRequest allowed
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(array("success" => false, "message" => "Direct access not allowed."));
    exit();
}

// Enforce admin session
AuthMiddleware::checkAdmin();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? trim($_POST['action']) : (isset($_GET['action']) ? trim($_GET['action']) : '');

// -----------------------------------------------------------------------
// GET action: search_user — live search as user types in user_management
// -----------------------------------------------------------------------
if ($action == 'search_user') {
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    if (empty($keyword)) {
        echo json_encode(array("success" => false, "message" => "Keyword is required."));
        exit();
    }

    $userModel = new User();
    $users = $userModel->searchUsers($keyword);
    echo json_encode(array("success" => true, "users" => $users, "count" => count($users)));

// -----------------------------------------------------------------------
// POST action: approve_application — quick approve without page reload
// -----------------------------------------------------------------------
} elseif ($action == 'approve_application') {
    $app_id = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;

    if ($app_id == 0) {
        echo json_encode(array("success" => false, "message" => "Invalid application ID."));
        exit();
    }

    $session     = AuthMiddleware::getSession();
    $reviewed_by = $session['user_id'];

    $appModel = new ExpertApplication();
    $result   = $appModel->approveApplication($app_id, $reviewed_by);

    if ($result) {
        echo json_encode(array(
            "success" => true,
            "message" => "Application #" . $app_id . " approved. User promoted to Verified Expert."
        ));
    } else {
        echo json_encode(array("success" => false, "message" => "Failed to approve application."));
    }

// -----------------------------------------------------------------------
// POST action: toggle_user_status — quick activate / deactivate
// -----------------------------------------------------------------------
} elseif ($action == 'toggle_user_status') {
    $user_id   = isset($_POST['user_id'])   ? (int)$_POST['user_id']   : 0;
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : -1;

    if ($user_id == 0 || $is_active == -1) {
        echo json_encode(array("success" => false, "message" => "User ID and status are required."));
        exit();
    }

    $userModel = new User();
    if ($is_active == 1) {
        $userModel->activateUser($user_id);
        $msg = "User #" . $user_id . " activated.";
    } else {
        $userModel->deactivateUser($user_id);
        $msg = "User #" . $user_id . " deactivated.";
    }
    echo json_encode(array("success" => true, "message" => $msg));

// -----------------------------------------------------------------------
// GET action: get_pending_count — live badge on navigation
// -----------------------------------------------------------------------
} elseif ($action == 'get_pending_count') {
    $appModel = new ExpertApplication();
    $count    = $appModel->countPending();
    echo json_encode(array("success" => true, "pending" => $count));

// -----------------------------------------------------------------------
// GET action: get_dashboard_stats — refresh dashboard numbers live
// -----------------------------------------------------------------------
} elseif ($action == 'get_dashboard_stats') {
    $userModel = new User();
    $appModel  = new ExpertApplication();

    $stats = array(
        "total_users"     => $userModel->getTotalUsers(),
        "total_questions" => $userModel->getTotalQuestions(),
        "total_answers"   => $userModel->getTotalAnswers(),
        "questions_today" => $userModel->getQuestionsToday(),
        "pending_apps"    => $appModel->countPending()
    );
    echo json_encode(array("success" => true, "stats" => $stats));

// -----------------------------------------------------------------------
// Unknown action
// -----------------------------------------------------------------------
} else {
    echo json_encode(array("success" => false, "message" => "Unknown action: " . htmlspecialchars($action)));
}
?>
