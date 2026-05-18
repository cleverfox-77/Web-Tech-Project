<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/ExpertApplication.php';
require_once __DIR__ . '/../models/AuditLog.php';

class ApplicationController {
    private $appModel;
    private $auditLog;

    public function __construct() {
        AuthMiddleware::checkAdmin();
        $this->appModel = new ExpertApplication();
        $this->auditLog = new AuditLog();
    }

    public function index() {
        $data = array();
        $data['pending']  = $this->appModel->getPendingApplications();
        $data['all_apps'] = $this->appModel->getAllApplications();
        return $data;
    }

    public function handleAction() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/admin/expert_applications.php");
            exit();
        }

        $action  = isset($_POST['action']) ? trim($_POST['action']) : '';
        $app_id  = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;
        $session = AuthMiddleware::getSession();
        $reviewed_by = $session['user_id'];

        if ($app_id == 0) {
            $_SESSION['error'] = "Invalid application ID.";
            header("Location: ../views/admin/expert_applications.php");
            exit();
        }

        if ($action == 'approve') {
            $this->appModel->approveApplication($app_id, $reviewed_by);
            $this->auditLog->log($reviewed_by, 'approve_application', 'expert_application', $app_id, 'Approved');
            $_SESSION['success'] = "Application approved successfully. User has been promoted to Verified Expert.";

        } elseif ($action == 'reject') {
            $reason = isset($_POST['reject_reason']) ? trim($_POST['reject_reason']) : '';
            if (empty($reason)) {
                $_SESSION['error'] = "A rejection reason is required.";
                header("Location: ../views/admin/expert_applications.php");
                exit();
            }
            $this->appModel->rejectApplication($app_id, $reviewed_by, $reason);
            $this->auditLog->log($reviewed_by, 'reject_application', 'expert_application', $app_id, $reason);
            $_SESSION['success'] = "Application rejected.";

        } else {
            $_SESSION['error'] = "Unknown action.";
        }

        header("Location: ../views/admin/expert_applications.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl = new ApplicationController();
    $ctrl->handleAction();
}
?>
