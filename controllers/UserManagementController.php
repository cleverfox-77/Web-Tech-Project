<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../models/Badge.php';

class UserManagementController {
    private $userModel;
    private $auditLog;
    private $badgeModel;

    public function __construct() {
        AuthMiddleware::checkAdmin();
        $this->userModel  = new User();
        $this->auditLog   = new AuditLog();
        $this->badgeModel = new Badge();
    }

    public function index() {
        $data = array();
        $keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        if ($keyword !== '') {
            $data['users']   = $this->userModel->searchUsers($keyword);
            $data['keyword'] = $keyword;
        } else {
            $data['users']   = $this->userModel->getAllUsers();
            $data['keyword'] = '';
        }
        return $data;
    }

    public function viewProfile($user_id) {
        $data = array();
        $data['user']    = $this->userModel->getUserById($user_id);
        $data['history'] = $this->userModel->getReputationHistory($user_id);
        $data['badges']  = $this->badgeModel->getUserBadges($user_id);
        return $data;
    }

    public function handleAction() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/admin/user_management.php");
            exit();
        }

        $action   = isset($_POST['action'])  ? trim($_POST['action'])  : '';
        $user_id  = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $session  = AuthMiddleware::getSession();
        $admin_id = $session['user_id'];

        if ($user_id == 0) {
            $_SESSION['error'] = "Invalid user ID.";
            header("Location: ../views/admin/user_management.php");
            exit();
        }

        if ($action == 'promote_expert') {
            $this->userModel->updateRole($user_id, 'expert');
            $this->auditLog->log($admin_id, 'promote_expert', 'user', $user_id, 'Promoted to Verified Expert');
            $_SESSION['success'] = "User promoted to Verified Expert.";

        } elseif ($action == 'promote_moderator') {
            $this->userModel->updateRole($user_id, 'moderator');
            $this->auditLog->log($admin_id, 'promote_moderator', 'user', $user_id, 'Promoted to Moderator');
            $_SESSION['success'] = "User promoted to Moderator.";

        } elseif ($action == 'demote_member') {
            $this->userModel->updateRole($user_id, 'member');
            $this->auditLog->log($admin_id, 'demote_member', 'user', $user_id, 'Demoted to Member');
            $_SESSION['success'] = "User demoted to Member.";

        } elseif ($action == 'activate') {
            $this->userModel->activateUser($user_id);
            $this->auditLog->log($admin_id, 'activate_user', 'user', $user_id, 'Account activated');
            $_SESSION['success'] = "User account activated.";

        } elseif ($action == 'deactivate') {
            $this->userModel->deactivateUser($user_id);
            $this->auditLog->log($admin_id, 'deactivate_user', 'user', $user_id, 'Account deactivated');
            $_SESSION['success'] = "User account deactivated.";

        } else {
            $_SESSION['error'] = "Unknown action.";
        }

        header("Location: ../views/admin/user_management.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl = new UserManagementController();
    $ctrl->handleAction();
}
?>
