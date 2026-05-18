<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/KbArticle.php';

class KbController {
    private $kbModel;

    public function __construct() {
        AuthMiddleware::checkExpert();
        $this->kbModel = new KbArticle();
    }

    public function index() {
        $session   = AuthMiddleware::getSession();
        $expert_id = $session['user_id'];
        $data = array();
        $data['articles'] = $this->kbModel->getByExpert($expert_id);
        return $data;
    }

    public function create() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/kb_list.php");
            exit();
        }
        $session   = AuthMiddleware::getSession();
        $expert_id = $session['user_id'];
        $title     = isset($_POST['title'])  ? trim($_POST['title'])  : '';
        $body      = isset($_POST['body'])   ? trim($_POST['body'])   : '';
        $tag_id    = isset($_POST['tag_id']) ? (int)$_POST['tag_id'] : 0;

        if (empty($title) || empty($body)) {
            $_SESSION['error'] = "Title and body are required.";
        } else {
            $this->kbModel->create($expert_id, $tag_id, $title, $body);
            $_SESSION['success'] = "Knowledge base article created.";
        }
        header("Location: ../views/expert/kb_list.php");
        exit();
    }

    public function edit() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/kb_list.php");
            exit();
        }
        $session   = AuthMiddleware::getSession();
        $expert_id = $session['user_id'];
        $id        = isset($_POST['kb_id'])  ? (int)$_POST['kb_id']  : 0;
        $title     = isset($_POST['title'])  ? trim($_POST['title'])  : '';
        $body      = isset($_POST['body'])   ? trim($_POST['body'])   : '';
        $tag_id    = isset($_POST['tag_id']) ? (int)$_POST['tag_id'] : 0;

        if (empty($title) || empty($body) || $id == 0) {
            $_SESSION['error'] = "All fields are required.";
        } else {
            $this->kbModel->edit($id, $expert_id, $title, $body, $tag_id);
            $_SESSION['success'] = "Article updated.";
        }
        header("Location: ../views/expert/kb_list.php");
        exit();
    }

    public function delete() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/kb_list.php");
            exit();
        }
        $session   = AuthMiddleware::getSession();
        $expert_id = $session['user_id'];
        $id        = isset($_POST['kb_id']) ? (int)$_POST['kb_id'] : 0;

        $this->kbModel->delete($id, $expert_id);
        $_SESSION['success'] = "Article deleted.";
        header("Location: ../views/expert/kb_list.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl   = new KbController();
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    if ($action == 'create') $ctrl->create();
    elseif ($action == 'edit')   $ctrl->edit();
    elseif ($action == 'delete') $ctrl->delete();
    else { header("Location: ../views/expert/kb_list.php"); exit(); }
}
?>
