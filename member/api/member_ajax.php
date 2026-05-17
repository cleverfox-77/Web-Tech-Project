<?php
/*
 * member_ajax.php
 * AJAX endpoint for Member role.
 * Returns JSON to XMLHttpRequest calls.
 *
 * Actions supported:
 *   GET  check_username      — live username availability check (registration)
 *   GET  tag_autocomplete    — suggest existing tags as user types
 *   GET  get_feed            — return question feed (newest/unanswered/most_voted/trending)
 *   GET  search_questions    — search with filters
 *   POST cast_vote           — upvote / downvote question or answer
 *   POST mark_notif_read     — mark one notification as read
 *   POST mark_all_read       — mark all notifications as read
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Vote.php';
require_once __DIR__ . '/../models/Notification.php';

// Only XMLHttpRequest allowed
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(array("success" => false, "message" => "Direct access not allowed."));
    exit();
}

header('Content-Type: application/json');

$action = isset($_POST['action']) ? trim($_POST['action']) : (isset($_GET['action']) ? trim($_GET['action']) : '');

// -----------------------------------------------------------------------
// GET: check_username — live check during registration (no session needed)
// -----------------------------------------------------------------------
if ($action == 'check_username') {
    $username  = isset($_GET['username']) ? trim($_GET['username']) : '';
    $userModel = new User();
    $exists    = $userModel->usernameExists($username);
    echo json_encode(array("success" => true, "exists" => $exists));

// -----------------------------------------------------------------------
// GET: tag_autocomplete — suggest tags as user types
// -----------------------------------------------------------------------
} elseif ($action == 'tag_autocomplete') {
    AuthMiddleware::checkMember();
    $prefix   = isset($_GET['prefix']) ? trim($_GET['prefix']) : '';
    $tagModel = new Tag();
    $tags     = $tagModel->search($prefix);
    echo json_encode(array("success" => true, "tags" => $tags));

// -----------------------------------------------------------------------
// GET: get_feed — switch dashboard feed tab without page reload
// -----------------------------------------------------------------------
} elseif ($action == 'get_feed') {
    AuthMiddleware::checkMember();
    $feed = isset($_GET['feed']) ? trim($_GET['feed']) : 'newest';
    $questionModel = new Question();
    $questions = $questionModel->getFeed($feed);
    echo json_encode(array("success" => true, "questions" => $questions));

// -----------------------------------------------------------------------
// GET: search_questions — filter search results via AJAX
// -----------------------------------------------------------------------
} elseif ($action == 'search_questions') {
    AuthMiddleware::checkMember();
    $keyword   = isset($_GET['q'])         ? trim($_GET['q'])         : '';
    $tag       = isset($_GET['tag'])        ? trim($_GET['tag'])       : '';
    $status    = isset($_GET['status'])     ? trim($_GET['status'])    : '';
    $from_date = isset($_GET['from_date'])  ? trim($_GET['from_date']) : '';
    $to_date   = isset($_GET['to_date'])    ? trim($_GET['to_date'])   : '';

    if (empty($keyword)) {
        echo json_encode(array("success" => false, "message" => "Keyword is required."));
        exit();
    }
    $questionModel = new Question();
    $results = $questionModel->search($keyword, $tag, $status, $from_date, $to_date);
    echo json_encode(array("success" => true, "results" => $results));

// -----------------------------------------------------------------------
// POST: cast_vote — upvote or downvote via AJAX
// -----------------------------------------------------------------------
} elseif ($action == 'cast_vote') {
    AuthMiddleware::checkMember();
    $session     = AuthMiddleware::getSession();
    $user_id     = $session['user_id'];
    $entity_type = isset($_POST['entity_type']) ? trim($_POST['entity_type']) : '';
    $entity_id   = isset($_POST['entity_id'])   ? (int)$_POST['entity_id']   : 0;
    $value       = isset($_POST['value'])        ? (int)$_POST['value']       : 0;

    if (!in_array($value, array(1, -1)) || empty($entity_type) || $entity_id == 0) {
        echo json_encode(array("success" => false, "message" => "Invalid vote data."));
        exit();
    }

    $voteModel = new Vote();
    $result    = $voteModel->castVote($user_id, $entity_type, $entity_id, $value);
    echo json_encode(array("success" => true, "new_score" => $result['new_score']));

// -----------------------------------------------------------------------
// POST: mark_notif_read — mark one notification as read
// -----------------------------------------------------------------------
} elseif ($action == 'mark_notif_read') {
    AuthMiddleware::checkMember();
    $session  = AuthMiddleware::getSession();
    $user_id  = $session['user_id'];
    $notif_id = isset($_POST['notif_id']) ? (int)$_POST['notif_id'] : 0;

    $notifModel = new Notification();
    $notifModel->markRead($notif_id, $user_id);
    echo json_encode(array("success" => true));

// -----------------------------------------------------------------------
// POST: mark_all_read — mark all notifications as read
// -----------------------------------------------------------------------
} elseif ($action == 'mark_all_read') {
    AuthMiddleware::checkMember();
    $session = AuthMiddleware::getSession();
    $user_id = $session['user_id'];

    $notifModel = new Notification();
    $notifModel->markAllRead($user_id);
    echo json_encode(array("success" => true));

} else {
    echo json_encode(array("success" => false, "message" => "Unknown action."));
}
?>
