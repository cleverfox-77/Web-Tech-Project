<?php
/*
 * expert_ajax.php
 * AJAX endpoint for the Verified Expert role.
 * Returns JSON to XMLHttpRequest calls.
 *
 * Actions supported:
 *   POST answer_session_question — submit an answer to a session question during a live session
 *   GET  get_session_questions   — fetch all questions for a session (live refresh)
 *   GET  get_faq_views           — return live view count for a specific FAQ
 *   GET  get_kb_views            — return live view count for a specific KB article
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/QaSession.php';
require_once __DIR__ . '/../models/Faq.php';
require_once __DIR__ . '/../models/KbArticle.php';

// Block direct browser access
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(array("success" => false, "message" => "Direct access not allowed."));
    exit();
}

// All expert AJAX actions require an expert session
AuthMiddleware::checkExpert();

header('Content-Type: application/json');

$action = isset($_POST['action']) ? trim($_POST['action']) : (isset($_GET['action']) ? trim($_GET['action']) : '');

// -----------------------------------------------------------------------
// POST: answer_session_question — answer a submitted question during live session
// -----------------------------------------------------------------------
if ($action == 'answer_session_question') {
    $sq_id       = isset($_POST['sq_id'])       ? (int)$_POST['sq_id']         : 0;
    $answer_text = isset($_POST['answer_text'])  ? trim($_POST['answer_text'])   : '';

    if ($sq_id == 0 || empty($answer_text)) {
        echo json_encode(array("success" => false, "message" => "Question ID and answer text are required."));
        exit();
    }

    $sessionModel = new QaSession();
    $result = $sessionModel->answerQuestion($sq_id, $answer_text);

    if ($result) {
        echo json_encode(array("success" => true, "message" => "Answer submitted successfully."));
    } else {
        echo json_encode(array("success" => false, "message" => "Failed to submit answer."));
    }

// -----------------------------------------------------------------------
// GET: get_session_questions — refresh the question list during a live session
// -----------------------------------------------------------------------
} elseif ($action == 'get_session_questions') {
    $session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;

    if ($session_id == 0) {
        echo json_encode(array("success" => false, "message" => "Session ID is required."));
        exit();
    }

    $sessionModel = new QaSession();
    $questions    = $sessionModel->getQuestions($session_id);
    echo json_encode(array("success" => true, "questions" => $questions));

// -----------------------------------------------------------------------
// GET: get_faq_views — live view count for a FAQ (increments and returns)
// -----------------------------------------------------------------------
} elseif ($action == 'get_faq_views') {
    $faq_id  = isset($_GET['faq_id']) ? (int)$_GET['faq_id'] : 0;
    if ($faq_id == 0) {
        echo json_encode(array("success" => false, "message" => "FAQ ID required."));
        exit();
    }
    $faqModel = new Faq();
    $faqModel->incrementViews($faq_id);
    $faq = $faqModel->getById($faq_id);
    echo json_encode(array("success" => true, "view_count" => $faq ? $faq['view_count'] : 0));

// -----------------------------------------------------------------------
// GET: get_kb_views — live view count for a KB article
// -----------------------------------------------------------------------
} elseif ($action == 'get_kb_views') {
    $kb_id = isset($_GET['kb_id']) ? (int)$_GET['kb_id'] : 0;
    if ($kb_id == 0) {
        echo json_encode(array("success" => false, "message" => "Article ID required."));
        exit();
    }
    $kbModel = new KbArticle();
    $kbModel->incrementViews($kb_id);
    $article = $kbModel->getById($kb_id);
    echo json_encode(array("success" => true, "view_count" => $article ? $article['view_count'] : 0));

} else {
    echo json_encode(array("success" => false, "message" => "Unknown action."));
}
?>
