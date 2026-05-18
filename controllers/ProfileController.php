<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/ExpertUser.php';
require_once __DIR__ . '/../models/Faq.php';
require_once __DIR__ . '/../models/KbArticle.php';
require_once __DIR__ . '/../models/QaSession.php';

class ProfileController {
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
        $data['expert']        = $this->userModel->getById($expert_id);
        $data['faq_views']     = $this->faqModel->getTotalViews($expert_id);
        $data['kb_views']      = $this->kbModel->getTotalViews($expert_id);
        $data['session_views'] = $this->sessionModel->getTotalViews($expert_id);
        $data['total_views']   = $data['faq_views'] + $data['kb_views'] + $data['session_views'];
        $data['faq_count']     = count($this->faqModel->getByExpert($expert_id));
        $data['kb_count']      = count($this->kbModel->getByExpert($expert_id));
        $data['session_count'] = count($this->sessionModel->getByExpert($expert_id));
        return $data;
    }

    public function update() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/expert/profile.php");
            exit();
        }
        $session      = AuthMiddleware::getSession();
        $expert_id    = $session['user_id'];
        $bio          = isset($_POST['bio'])          ? trim($_POST['bio'])          : '';
        $expert_domain= isset($_POST['expert_domain'])? trim($_POST['expert_domain']): '';

        $this->userModel->updateProfile($expert_id, $bio, $expert_domain);
        $_SESSION['success'] = "Profile updated.";
        header("Location: ../views/expert/profile.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $ctrl = new ProfileController();
    $ctrl->update();
}
?>
