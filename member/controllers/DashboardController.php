<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/Follow.php';
require_once __DIR__ . '/../models/Notification.php';

class DashboardController {
    private $questionModel;
    private $followModel;
    private $notifModel;

    public function __construct() {
        AuthMiddleware::checkMember();
        $this->questionModel = new Question();
        $this->followModel   = new Follow();
        $this->notifModel    = new Notification();
    }

    public function index() {
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];
        $feed    = isset($_GET['feed']) ? trim($_GET['feed']) : 'newest';

        $data = array();
        $data['feed']      = $feed;
        $data['questions'] = $this->questionModel->getFeed($feed, $user_id);
        $data['unread']    = $this->notifModel->countUnread($user_id);
        $data['user_name'] = $session['user_name'];
        return $data;
    }
}
?>
