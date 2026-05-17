<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/ModerationActivity.php';
require_once __DIR__ . '/../models/AuditLog.php';

class ModerationActivityController {
    private $modModel;
    private $auditLog;

    public function __construct() {
        AuthMiddleware::checkAdmin();
        $this->modModel = new ModerationActivity();
        $this->auditLog = new AuditLog();
    }

    public function index() {
        $data = array();
        $data['warnings']        = $this->modModel->getAllWarnings();
        $data['suspended_users'] = $this->modModel->getSuspendedUsers();
        $data['content_actions'] = $this->modModel->getContentActions();
        return $data;
    }

    public function handleOverride() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/admin/moderation_activity.php");
            exit();
        }

        $action  = isset($_POST['action'])  ? trim($_POST['action'])  : '';
        $reason  = isset($_POST['reason'])  ? trim($_POST['reason'])  : '';
        $session = AuthMiddleware::getSession();
        $admin_id = $session['user_id'];

        if (empty($reason)) {
            $_SESSION['error'] = "A reason is required for all override actions.";
            header("Location: ../views/admin/moderation_activity.php");
            exit();
        }

        if ($action == 'lift_suspension') {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            if ($user_id == 0) {
                $_SESSION['error'] = "Invalid user ID.";
            } else {
                $this->modModel->liftSuspension($user_id, $admin_id, $reason);
                $this->auditLog->log($admin_id, 'lift_suspension', 'user', $user_id, $reason);
                $_SESSION['success'] = "Suspension lifted. User account re-activated.";
            }

        } elseif ($action == 'reinstate_content') {
            $report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
            if ($report_id == 0) {
                $_SESSION['error'] = "Invalid report ID.";
            } else {
                $this->modModel->reinstateContent($report_id, $admin_id, $reason);
                $this->auditLog->log($admin_id, 'reinstate_content', 'report', $report_id, $reason);
                $_SESSION['success'] = "Content reinstated and report re-opened for review.";
            }

        } else {
            $_SESSION['error'] = "Unknown override action.";
        }

        header("Location: ../views/admin/moderation_activity.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl = new ModerationActivityController();
    $ctrl->handleOverride();
}
?>
