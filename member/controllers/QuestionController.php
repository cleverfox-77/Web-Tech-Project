<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Answer.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Tag.php';
require_once __DIR__ . '/../models/ReportBadge.php';

class QuestionController {
    private $questionModel;
    private $answerModel;
    private $commentModel;
    private $tagModel;

    public function __construct() {
        AuthMiddleware::checkMember();
        $this->questionModel = new Question();
        $this->answerModel   = new Answer();
        $this->commentModel  = new Comment();
        $this->tagModel      = new Tag();
    }

    // Show a question with its answers and comments
    public function view($id) {
        $data = array();
        $data['question'] = $this->questionModel->getById($id);
        if (!$data['question']) {
            $_SESSION['error'] = "Question not found.";
            header("Location: ../views/member/dashboard.php");
            exit();
        }
        $data['answers']          = $this->answerModel->getByQuestion($id);
        $data['tags']             = $this->questionModel->getTags($id);
        $data['question_comments']= $this->commentModel->getByEntity('question', $id);

        // Comments for each answer
        $data['answer_comments'] = array();
        foreach ($data['answers'] as $a) {
            $data['answer_comments'][$a['id']] = $this->commentModel->getByEntity('answer', $a['id']);
        }

        $session = AuthMiddleware::getSession();
        $data['user_id'] = $session['user_id'];
        return $data;
    }

    // Show ask question form
    public function showAsk() {
        return array('errors' => array(), 'old' => array());
    }

    // Handle post question form
    public function ask() {
        $session  = AuthMiddleware::getSession();
        $user_id  = $session['user_id'];
        $errors   = array();

        $title    = isset($_POST['title']) ? trim($_POST['title']) : '';
        $body     = isset($_POST['body'])  ? trim($_POST['body'])  : '';
        $tags_raw = isset($_POST['tags'])  ? trim($_POST['tags'])  : '';

        if (empty($title))  $errors[] = "Title is required.";
        if (strlen($title) < 10) $errors[] = "Title must be at least 10 characters.";
        if (empty($body))   $errors[] = "Question body is required.";

        if (empty($errors)) {
            $question_id = $this->questionModel->create($user_id, $title, $body);

            // Attach tags
            if (!empty($tags_raw)) {
                $tag_names = explode(',', $tags_raw);
                foreach ($tag_names as $tag_name) {
                    $tag_name = trim($tag_name);
                    if (empty($tag_name)) continue;
                    $tag = $this->tagModel->findByName($tag_name);
                    if ($tag) {
                        $this->questionModel->attachTag($question_id, $tag['id']);
                    }
                }
            }

            $_SESSION['success'] = "Question posted successfully.";
            header("Location: ../views/member/question_view.php?id=" . $question_id);
            exit();
        }

        return array('errors' => $errors, 'old' => array('title' => $title, 'body' => $body, 'tags' => $tags_raw));
    }

    // Handle edit question
    public function edit() {
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];
        $id      = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
        $title   = isset($_POST['title']) ? trim($_POST['title']) : '';
        $body    = isset($_POST['body'])  ? trim($_POST['body'])  : '';

        if (empty($title) || empty($body)) {
            $_SESSION['error'] = "Title and body are required.";
        } else {
            $this->questionModel->edit($id, $user_id, $title, $body);
            $_SESSION['success'] = "Question updated.";
        }
        header("Location: ../views/member/question_view.php?id=" . $id);
        exit();
    }

    // Handle delete question
    public function delete() {
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];
        $id      = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;

        $deleted = $this->questionModel->delete($id, $user_id);
        if (!$deleted) {
            $_SESSION['error'] = "Cannot delete a question that has answers.";
            header("Location: ../views/member/question_view.php?id=" . $id);
        } else {
            $_SESSION['success'] = "Question deleted.";
            header("Location: ../views/member/dashboard.php");
        }
        exit();
    }

    // Search questions
    public function search() {
        $keyword   = isset($_GET['q'])         ? trim($_GET['q'])         : '';
        $tag       = isset($_GET['tag'])        ? trim($_GET['tag'])       : '';
        $status    = isset($_GET['status'])     ? trim($_GET['status'])    : '';
        $from_date = isset($_GET['from_date'])  ? trim($_GET['from_date']) : '';
        $to_date   = isset($_GET['to_date'])    ? trim($_GET['to_date'])   : '';

        $data = array();
        $data['results']   = array();
        $data['keyword']   = $keyword;
        $data['tag']       = $tag;
        $data['status']    = $status;
        $data['from_date'] = $from_date;
        $data['to_date']   = $to_date;

        if (!empty($keyword)) {
            $data['results'] = $this->questionModel->search($keyword, $tag, $status, $from_date, $to_date);
        }
        return $data;
    }
}
?>
