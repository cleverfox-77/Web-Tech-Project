<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/QaSession.php';

class SessionController {
    private $sessionModel;

    public function __construct() {
        AuthMiddleware::checkExpert();
        $this->sessionModel = new QaSession();
    }

    public function index() {
        $session   = AuthMiddleware::getSession();
        $expert_id = $session['user_id'];
        $data = array();
        $data['sessions'] = $this->sessionModel->getByExpert($expert_id);
        return $data;
    }

    public function view($id) {
        $session   = AuthMiddleware::getSession();
        $expert_id = $session['user_id'];
        $data = array();
        $data['session']   = $this->sessionModel->getById($id);
        $data['questions'] = $this->sessionModel->getQuestions($id);
        $data['expert_id'] = $expert_id;
        return $data;
    }

    public function create() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/session_list.php");
            exit();
        }
        $session      = AuthMiddleware::getSession();
        $expert_id    = $session['user_id'];
        $title        = isset($_POST['title'])        ? trim($_POST['title'])        : '';
        $description  = isset($_POST['description'])  ? trim($_POST['description'])  : '';
        $scheduled_at = isset($_POST['scheduled_at']) ? trim($_POST['scheduled_at']) : '';
        $duration     = isset($_POST['duration'])     ? (int)$_POST['duration']     : 60;
        $max_q        = isset($_POST['max_questions'])? (int)$_POST['max_questions']: 20;

        if (empty($title) || empty($scheduled_at)) {
            $_SESSION['error'] = "Title and scheduled date are required.";
        } elseif ($duration < 1) {
            $_SESSION['error'] = "Duration must be at least 1 minute.";
        } else {
            $this->sessionModel->create($expert_id, $title, $description, $scheduled_at, $duration, $max_q);
            $_SESSION['success'] = "Q&A session scheduled.";
        }
        header("Location: ../views/expert/session_list.php");
        exit();
    }

    public function edit() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/session_list.php");
            exit();
        }
        $sess         = AuthMiddleware::getSession();
        $expert_id    = $sess['user_id'];
        $id           = isset($_POST['session_id'])   ? (int)$_POST['session_id']   : 0;
        $title        = isset($_POST['title'])        ? trim($_POST['title'])        : '';
        $description  = isset($_POST['description'])  ? trim($_POST['description'])  : '';
        $scheduled_at = isset($_POST['scheduled_at']) ? trim($_POST['scheduled_at']) : '';
        $duration     = isset($_POST['duration'])     ? (int)$_POST['duration']     : 60;
        $max_q        = isset($_POST['max_questions'])? (int)$_POST['max_questions']: 20;

        if (empty($title) || empty($scheduled_at) || $id == 0) {
            $_SESSION['error'] = "All fields are required.";
        } else {
            $this->sessionModel->edit($id, $expert_id, $title, $description, $scheduled_at, $duration, $max_q);
            $_SESSION['success'] = "Session updated.";
        }
        header("Location: ../views/expert/session_list.php");
        exit();
    }

    public function cancel() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/session_list.php");
            exit();
        }
        $sess      = AuthMiddleware::getSession();
        $expert_id = $sess['user_id'];
        $id        = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;

        $this->sessionModel->cancel($id, $expert_id);
        $_SESSION['success'] = "Session cancelled.";
        header("Location: ../views/expert/session_list.php");
        exit();
    }

    public function activate() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/session_list.php");
            exit();
        }
        $sess      = AuthMiddleware::getSession();
        $expert_id = $sess['user_id'];
        $id        = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;

        $this->sessionModel->activate($id, $expert_id);
        $_SESSION['success'] = "Session is now active. Members can submit questions.";
        header("Location: ../views/expert/session_view.php?id=" . $id);
        exit();
    }

    public function close() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/session_list.php");
            exit();
        }
        $sess      = AuthMiddleware::getSession();
        $expert_id = $sess['user_id'];
        $id        = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;

        $this->sessionModel->close($id, $expert_id);
        $_SESSION['success'] = "Session closed. All answered questions form the session transcript.";
        header("Location: ../views/expert/session_view.php?id=" . $id);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl   = new SessionController();
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    if      ($action == 'create')   $ctrl->create();
    elseif  ($action == 'edit')     $ctrl->edit();
    elseif  ($action == 'cancel')   $ctrl->cancel();
    elseif  ($action == 'activate') $ctrl->activate();
    elseif  ($action == 'close')    $ctrl->close();
    else { header("Location: ../views/expert/session_list.php"); exit(); }
}
?>
