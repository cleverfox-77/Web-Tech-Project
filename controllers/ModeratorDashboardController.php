<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Warning.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Content.php';

class ModeratorDashboardController {
    private $reportModel;
    private $warningModel;
    private $userModel;
    private $contentModel;

    public function __construct() {
        AuthMiddleware::checkModerator();
        $this->reportModel  = new Report();
        $this->warningModel = new Warning();
        $this->userModel    = new User();
        $this->contentModel = new Content();
    }

    public function index() {
        $data = array();
        $data['pending_total']    = $this->reportModel->getTotalPending();
        $data['pending_by_type']  = $this->reportModel->getPendingCountByType();
        $data['edits_today']      = $this->contentModel->getEditsToday();
        $data['warnings_week']    = $this->warningModel->getWarningsThisWeek();
        $data['new_users_week']   = $this->userModel->getNewUsersThisWeek();
        $session = AuthMiddleware::getSession();
        $data['mod_name'] = isset($session['user_name']) ? $session['user_name'] : 'Moderator';
        return $data;
    }
}
?>
