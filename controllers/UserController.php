<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Warning.php';

class UserController {
    private $userModel;
    private $warningModel;

    public function __construct() {
        AuthMiddleware::checkModerator();
        $this->userModel    = new User();
        $this->warningModel = new Warning();
    }

    public function viewProfile($user_id) {
        $user_id = (int)$user_id;
        $data = array();
        $data['user']     = $this->userModel->getUserById($user_id);
        $data['stats']    = $this->userModel->getUserActivityStats($user_id);
        $data['warnings'] = $this->warningModel->getWarningsByUser($user_id);
        return $data;
    }

    public function handleSuspend() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/moderator/dashboard.php");
            exit();
        }
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $days    = isset($_POST['days'])    ? (int)$_POST['days']    : 0;

        if ($user_id == 0) {
            $_SESSION['error'] = "Invalid user.";
        } elseif ($days < 1 || $days > 365) {
            $_SESSION['error'] = "Suspension period must be between 1 and 365 days.";
        } else {
            $this->userModel->suspendUser($user_id, $days);
            $_SESSION['success'] = "User suspended for " . $days . " day(s).";
        }
        header("Location: ../views/moderator/user_profile.php?id=" . $user_id);
        exit();
    }

    public function handleActivate() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/moderator/dashboard.php");
            exit();
        }
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        if ($user_id > 0) {
            $this->userModel->activateUser($user_id);
            $_SESSION['success'] = "User account re-activated.";
        } else {
            $_SESSION['error'] = "Invalid user.";
        }
        header("Location: ../views/moderator/user_profile.php?id=" . $user_id);
        exit();
    }
}

// Route POST actions when this file is called directly
if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl   = new UserController();
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    if ($action == 'suspend') {
        $ctrl->handleSuspend();
    } elseif ($action == 'activate') {
        $ctrl->handleActivate();
    } else {
        header("Location: ../views/moderator/dashboard.php");
        exit();
    }
}
?>
