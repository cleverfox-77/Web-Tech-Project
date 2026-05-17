<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/ExpertUser.php';
require_once __DIR__ . '/../models/Faq.php';
require_once __DIR__ . '/../models/KbArticle.php';
require_once __DIR__ . '/../models/QaSession.php';

class DashboardController {
    private $userModel;
    private $faqModel;
    private $kbModel;
    private $sessionModel;

    public function __construct() {
        AuthMiddleware::checkExpert();
        $this->userModel    = new ExpertUser();
        $this->faqModel     = new Faq();
        $this->kbModel      = new KbArticle();
        $this->sessionModel = new QaSession();
    }

    public function index() {
        $session   = AuthMiddleware::getSession();
        $expert_id = $session['user_id'];

        $data = array();
        $data['expert']       = $this->userModel->getById($expert_id);
        $data['faq_count']    = count($this->faqModel->getByExpert($expert_id));
        $data['kb_count']     = count($this->kbModel->getByExpert($expert_id));
        $data['session_count']= count($this->sessionModel->getByExpert($expert_id));

        // Content performance: total views across all content
        $data['faq_views']    = $this->faqModel->getTotalViews($expert_id);
        $data['kb_views']     = $this->kbModel->getTotalViews($expert_id);
        $data['session_views']= $this->sessionModel->getTotalViews($expert_id);
        $data['total_views']  = $data['faq_views'] + $data['kb_views'] + $data['session_views'];

        // Active sessions
        $all_sessions = $this->sessionModel->getByExpert($expert_id);
        $data['active_sessions'] = array();
        foreach ($all_sessions as $s) {
            if ($s['status'] == 'active') {
                $data['active_sessions'][] = $s;
            }
        }

        return $data;
    }
}
?>
