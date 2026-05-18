<?php
/*
 * moderator_ajax.php
 * Dedicated PHP AJAX endpoint for moderator quick actions.
 * Returns JSON responses to XMLHttpRequest calls.
 * Supported actions:
 *   - dismiss_report      : quickly dismiss a report
 *   - get_report_content  : fetch content of a reported entity
 *   - get_tag_stats       : fetch live tag usage stats
 *   - search_user         : search user by username for profile lookup
 *   - get_warnings        : fetch all warnings for a user
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Content.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/Warning.php';
require_once __DIR__ . '/../config/Database.php';

// Only allow XMLHttpRequest
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(array("success" => false, "message" => "Direct access not allowed."));
    exit();
}

// Check moderator session
AuthMiddleware::checkModerator();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? trim($_POST['action']) : (isset($_GET['action']) ? trim($_GET['action']) : '');

if ($action == 'dismiss_report') {
    // Quick dismiss a report via AJAX without page reload
    $report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
    $note      = isset($_POST['note'])      ? trim($_POST['note'])      : 'Dismissed via quick action.';

    if ($report_id == 0) {
        echo json_encode(array("success" => false, "message" => "Invalid report ID."));
        exit();
    }

    $reportModel = new Report();
    $result = $reportModel->updateReportStatus($report_id, 'dismissed', $note);

    if ($result) {
        echo json_encode(array("success" => true, "message" => "Report #" . $report_id . " dismissed successfully."));
    } else {
        echo json_encode(array("success" => false, "message" => "Failed to dismiss report."));
    }

} elseif ($action == 'get_report_content') {
    // Fetch entity content for inline preview
    $entity_type = isset($_GET['entity_type']) ? trim($_GET['entity_type']) : '';
    $entity_id   = isset($_GET['entity_id'])   ? (int)$_GET['entity_id']   : 0;

    if (empty($entity_type) || $entity_id == 0) {
        echo json_encode(array("success" => false, "message" => "Entity type and ID are required."));
        exit();
    }

    $contentModel = new Content();
    $content = $contentModel->getContentByReport($entity_type, $entity_id);

    if ($content) {
        echo json_encode(array("success" => true, "content" => $content));
    } else {
        echo json_encode(array("success" => false, "message" => "Content not found."));
    }

} elseif ($action == 'get_tag_stats') {
    // Return live tag stats for the management page
    $tagModel = new Tag();
    $tags = $tagModel->getAllTagsWithStats();
    echo json_encode(array("success" => true, "tags" => $tags));

} elseif ($action == 'search_user') {
    // Search for a user by username for profile lookup
    $username = isset($_GET['username']) ? trim($_GET['username']) : '';

    if (empty($username)) {
        echo json_encode(array("success" => false, "message" => "Username is required."));
        exit();
    }

    $db = new Database();
    $conn = $db->getConnection();
    $sql = "SELECT id, name, username, role, reputation, is_active FROM users WHERE username LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $search = '%' . $username . '%';
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
    echo json_encode(array("success" => true, "users" => $users));

} elseif ($action == 'get_warnings') {
    // Fetch warning history for a specific user
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

    if ($user_id == 0) {
        echo json_encode(array("success" => false, "message" => "User ID is required."));
        exit();
    }

    $warningModel = new Warning();
    $warnings = $warningModel->getWarningsByUser($user_id);
    echo json_encode(array("success" => true, "warnings" => $warnings, "count" => count($warnings)));

} elseif ($action == 'get_pending_count') {
    // Live dashboard badge: get pending report count
    $reportModel = new Report();
    $count = $reportModel->getTotalPending();
    echo json_encode(array("success" => true, "pending" => $count));

} else {
    echo json_encode(array("success" => false, "message" => "Unknown action."));
}
?>
