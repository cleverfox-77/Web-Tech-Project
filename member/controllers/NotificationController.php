<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $notifModel;

    public function __construct() {
        AuthMiddleware::checkMember();
        $this->notifModel = new Notification();
    }

    public function index() {
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];
        $data = array();
        $data['notifications'] = $this->notifModel->getByUser($user_id);
        $data['unread']        = $this->notifModel->countUnread($user_id);
        return $data;
    }

    public function markRead() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/notifications.php");
            exit();
        }
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];
        $id      = isset($_POST['notif_id']) ? (int)$_POST['notif_id'] : 0;

        $this->notifModel->markRead($id, $user_id);
        header("Location: ../views/member/notifications.php");
        exit();
    }

    public function markAllRead() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/notifications.php");
            exit();
        }
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];
        $this->notifModel->markAllRead($user_id);
        $_SESSION['success'] = "All notifications marked as read.";
        header("Location: ../views/member/notifications.php");
        exit();
    }
}
?>
