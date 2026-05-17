<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Answer.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Question.php';

class AnswerController {
    private $answerModel;
    private $notifModel;
    private $questionModel;

    public function __construct() {
        AuthMiddleware::checkMember();
        $this->answerModel   = new Answer();
        $this->notifModel    = new Notification();
        $this->questionModel = new Question();
    }

    public function post() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/dashboard.php");
            exit();
        }
        $session     = AuthMiddleware::getSession();
        $user_id     = $session['user_id'];
        $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
        $body        = isset($_POST['body']) ? trim($_POST['body']) : '';

        if (empty($body)) {
            $_SESSION['error'] = "Answer body is required.";
        } elseif ($question_id == 0) {
            $_SESSION['error'] = "Invalid question.";
        } else {
            $answer_id = $this->answerModel->create($question_id, $user_id, $body);

            // Notify question author
            $q = $this->questionModel->getById($question_id);
            if ($q && $q['author_id'] != $user_id) {
                $this->notifModel->create(
                    $q['author_id'],
                    'new_answer',
                    $session['user_name'] . ' answered your question: ' . $q['title'],
                    '../views/member/question_view.php?id=' . $question_id
                );
            }
            $_SESSION['success'] = "Answer posted.";
        }
        header("Location: ../views/member/question_view.php?id=" . $question_id);
        exit();
    }

    public function edit() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/dashboard.php");
            exit();
        }
        $session     = AuthMiddleware::getSession();
        $user_id     = $session['user_id'];
        $answer_id   = isset($_POST['answer_id'])   ? (int)$_POST['answer_id']   : 0;
        $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
        $body        = isset($_POST['body']) ? trim($_POST['body']) : '';

        if (empty($body)) {
            $_SESSION['error'] = "Answer body is required.";
        } else {
            $this->answerModel->edit($answer_id, $user_id, $body);
            $_SESSION['success'] = "Answer updated.";
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
        $answer_id   = isset($_POST['answer_id'])   ? (int)$_POST['answer_id']   : 0;
        $question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;

        $this->answerModel->delete($answer_id, $user_id);
        $_SESSION['success'] = "Answer deleted.";
        header("Location: ../views/member/question_view.php?id=" . $question_id);
        exit();
    }

    public function accept() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/dashboard.php");
            exit();
        }
        $session         = AuthMiddleware::getSession();
        $user_id         = $session['user_id'];
        $answer_id       = isset($_POST['answer_id'])   ? (int)$_POST['answer_id']   : 0;
        $question_id     = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;

        $this->answerModel->accept($answer_id, $question_id, $user_id);

        // Notify the answerer
        $a = $this->answerModel->getById($answer_id);
        if ($a && $a['author_id'] != $user_id) {
            $this->notifModel->create(
                $a['author_id'],
                'answer_accepted',
                $session['user_name'] . ' accepted your answer.',
                '../views/member/question_view.php?id=' . $question_id
            );
        }

        $_SESSION['success'] = "Answer accepted.";
        header("Location: ../views/member/question_view.php?id=" . $question_id);
        exit();
    }
}
?>
