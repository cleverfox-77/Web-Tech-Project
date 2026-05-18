<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ExpertApplication.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/PlatformSetting.php';

class AdminDashboardController {
    private $userModel;
    private $appModel;
    private $analyticsModel;
    private $settingModel;

    public function __construct() {
        AuthMiddleware::checkAdmin();
        $this->userModel      = new User();
        $this->appModel       = new ExpertApplication();
        $this->analyticsModel = new Analytics();
        $this->settingModel   = new PlatformSetting();
    }

    public function index() {
        $data = array();

        // User counts by role
        $data['role_counts']     = $this->userModel->countByRole();
        $data['total_users']     = $this->userModel->getTotalUsers();
        $data['total_questions'] = $this->userModel->getTotalQuestions();
        $data['total_answers']   = $this->userModel->getTotalAnswers();
        $data['questions_today'] = $this->userModel->getQuestionsToday();

        // Pending expert applications
        $data['pending_apps']    = $this->appModel->countPending();

        // Active Q&A sessions
        $data['active_sessions'] = $this->analyticsModel->getActiveSessionCount();

        // Current announcement
        $data['announcement']    = $this->settingModel->getAnnouncement();

        // Admin name from session
        $session = AuthMiddleware::getSession();
        $data['admin_name'] = isset($session['user_name']) ? $session['user_name'] : 'Admin';

        return $data;
    }
}
?>
