<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/PlatformSetting.php';
require_once __DIR__ . '/../models/Badge.php';
require_once __DIR__ . '/../models/AuditLog.php';

class SettingsController {
    private $settingModel;
    private $badgeModel;
    private $auditLog;

    public function __construct() {
        AuthMiddleware::checkAdmin();
        $this->settingModel = new PlatformSetting();
        $this->badgeModel   = new Badge();
        $this->auditLog     = new AuditLog();
    }

    public function index() {
        $data = array();
        $data['settings'] = $this->settingModel->getAllSettings();
        $data['badges']   = $this->badgeModel->getAllBadges();
        return $data;
    }

    public function handleAction() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/admin/platform_settings.php");
            exit();
        }

        $action  = isset($_POST['action']) ? trim($_POST['action']) : '';
        $session = AuthMiddleware::getSession();
        $admin_id = $session['user_id'];

        if ($action == 'update_reputation') {
            $upvote_q  = isset($_POST['upvote_question'])  ? (int)$_POST['upvote_question']  : 5;
            $upvote_a  = isset($_POST['upvote_answer'])    ? (int)$_POST['upvote_answer']    : 10;
            $accept_b  = isset($_POST['accept_bonus'])     ? (int)$_POST['accept_bonus']     : 15;
            $downvote  = isset($_POST['downvote_penalty']) ? (int)$_POST['downvote_penalty'] : 2;

            if ($upvote_q < 0 || $upvote_a < 0 || $accept_b < 0 || $downvote < 0) {
                $_SESSION['error'] = "Reputation values must be positive numbers.";
            } else {
                $this->settingModel->upsertSetting('upvote_question',  $upvote_q);
                $this->settingModel->upsertSetting('upvote_answer',    $upvote_a);
                $this->settingModel->upsertSetting('accept_bonus',     $accept_b);
                $this->settingModel->upsertSetting('downvote_penalty', $downvote);
                $this->auditLog->log($admin_id, 'update_reputation_rules', 'settings', 0, 'Updated scoring rules');
                $_SESSION['success'] = "Reputation scoring rules updated.";
            }

        } elseif ($action == 'post_announcement') {
            $message = isset($_POST['announcement']) ? trim($_POST['announcement']) : '';
            if (empty($message)) {
                $_SESSION['error'] = "Announcement message cannot be empty.";
            } else {
                $this->settingModel->postAnnouncement($message);
                $this->auditLog->log($admin_id, 'post_announcement', 'settings', 0, $message);
                $_SESSION['success'] = "Announcement posted to all users.";
            }

        } elseif ($action == 'add_badge') {
            $name            = isset($_POST['badge_name'])      ? trim($_POST['badge_name'])      : '';
            $description     = isset($_POST['badge_desc'])      ? trim($_POST['badge_desc'])      : '';
            $icon            = isset($_POST['badge_icon'])      ? trim($_POST['badge_icon'])      : '';
            $threshold_type  = isset($_POST['threshold_type'])  ? trim($_POST['threshold_type'])  : '';
            $threshold_value = isset($_POST['threshold_value']) ? (int)$_POST['threshold_value'] : 0;

            if (empty($name) || empty($threshold_type)) {
                $_SESSION['error'] = "Badge name and threshold type are required.";
            } else {
                $this->badgeModel->addBadge($name, $description, $icon, $threshold_type, $threshold_value);
                $this->auditLog->log($admin_id, 'add_badge', 'badge', 0, 'Added badge: ' . $name);
                $_SESSION['success'] = "Badge '" . $name . "' added successfully.";
            }

        } elseif ($action == 'award_badge') {
            $user_id  = isset($_POST['user_id'])  ? (int)$_POST['user_id']  : 0;
            $badge_id = isset($_POST['badge_id']) ? (int)$_POST['badge_id'] : 0;

            if ($user_id == 0 || $badge_id == 0) {
                $_SESSION['error'] = "User ID and Badge ID are required.";
            } else {
                $awarded = $this->badgeModel->awardBadge($user_id, $badge_id);
                if ($awarded) {
                    $this->auditLog->log($admin_id, 'award_badge', 'user', $user_id, 'Awarded badge ID ' . $badge_id);
                    $_SESSION['success'] = "Badge awarded successfully.";
                } else {
                    $_SESSION['error'] = "User already has this badge.";
                }
            }

        } elseif ($action == 'set_featured_questions') {
            $ids_csv = isset($_POST['featured_question_ids']) ? trim($_POST['featured_question_ids']) : '';
            $this->settingModel->setFeaturedQuestions($ids_csv);
            $this->auditLog->log($admin_id, 'set_featured_questions', 'settings', 0, 'IDs: ' . $ids_csv);
            $_SESSION['success'] = "Featured questions updated.";

        } elseif ($action == 'set_featured_faqs') {
            $ids_csv = isset($_POST['featured_faq_ids']) ? trim($_POST['featured_faq_ids']) : '';
            $this->settingModel->setFeaturedFaqs($ids_csv);
            $this->auditLog->log($admin_id, 'set_featured_faqs', 'settings', 0, 'IDs: ' . $ids_csv);
            $_SESSION['success'] = "Featured FAQ articles updated.";

        } elseif ($action == 'set_featured_kb') {
            $ids_csv = isset($_POST['featured_kb_ids']) ? trim($_POST['featured_kb_ids']) : '';
            $this->settingModel->setFeaturedKbArticles($ids_csv);
            $this->auditLog->log($admin_id, 'set_featured_kb', 'settings', 0, 'IDs: ' . $ids_csv);
            $_SESSION['success'] = "Featured knowledge base articles updated.";

        } else {
            $_SESSION['error'] = "Unknown action.";
        }

        header("Location: ../views/admin/platform_settings.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl = new SettingsController();
    $ctrl->handleAction();
}
?>
