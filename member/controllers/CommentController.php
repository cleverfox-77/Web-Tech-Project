<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Comment.php';

class CommentController {
    private $commentModel;

    public function __construct() {
        AuthMiddleware::checkMember();
        $this->commentModel = new Comment();
    }

    public function post() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/dashboard.php");
            exit();
        }
        $session     = AuthMiddleware::getSession();
        $user_id     = $session['user_id'];
        $entity_type = isset($_POST['entity_type']) ? trim($_POST['entity_type']) : '';
        $entity_id   = isset($_POST['entity_id'])   ? (int)$_POST['entity_id']   : 0;
        $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
        $body        = isset($_POST['body']) ? trim($_POST['body']) : '';

        if (empty($body)) {
            $_SESSION['error'] = "Comment cannot be empty.";
        } else {
            $this->commentModel->create($entity_type, $entity_id, $user_id, $body);
            $_SESSION['success'] = "Comment posted.";
        }
        header("Location: ../views/member/question_view.php?id=" . $question_id);
        exit();
    }

    public function delete() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/dashboard.php");
            exit();
        }
        $session     = AuthMiddleware::getSession();
        $user_id     = $session['user_id'];
        $comment_id  = isset($_POST['comment_id'])  ? (int)$_POST['comment_id']  : 0;
        $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;

        $this->commentModel->delete($comment_id, $user_id);
        $_SESSION['success'] = "Comment deleted.";
        header("Location: ../views/member/question_view.php?id=" . $question_id);
        exit();
    }
}
?>
