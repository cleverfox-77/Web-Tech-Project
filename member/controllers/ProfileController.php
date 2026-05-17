<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ReportBadge.php';
require_once __DIR__ . '/../models/ExpertApplication.php';
require_once __DIR__ . '/../models/Question.php';

class ProfileController {
    private $userModel;
    private $badgeModel;
    private $appModel;
    private $questionModel;

    public function __construct() {
        AuthMiddleware::checkMember();
        $this->userModel     = new User();
        $this->badgeModel    = new Badge();
        $this->appModel      = new ExpertApplication();
        $this->questionModel = new Question();
    }

    public function index() {
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];

        $data = array();
        $data['user']        = $this->userModel->getById($user_id);
        $data['stats']       = $this->userModel->getActivityStats($user_id);
        $data['badges']      = $this->badgeModel->getUserBadges($user_id);
        $data['all_badges']  = $this->badgeModel->getAll();
        $data['rep_history'] = $this->userModel->getReputationHistory($user_id);
        $data['questions']   = $this->questionModel->getByAuthor($user_id);
        $data['application'] = $this->appModel->getByUser($user_id);
        return $data;
    }

    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/profile.php");
            exit();
        }
        $session = AuthMiddleware::getSession();
        $user_id = $session['user_id'];
        $bio     = isset($_POST['bio']) ? trim($_POST['bio']) : '';
        $pic     = '';

        // Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $ext     = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $filename = 'uploads/' . $user_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], '../../' . $filename);
                $pic = $filename;
            }
        }

        $this->userModel->updateProfile($user_id, $bio, $pic);
        $_SESSION['success'] = "Profile updated.";
        header("Location: ../views/member/profile.php");
        exit();
    }

    public function updatePassword() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/profile.php");
            exit();
        }
        $session  = AuthMiddleware::getSession();
        $user_id  = $session['user_id'];
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $confirm  = isset($_POST['confirm'])  ? trim($_POST['confirm'])  : '';

        if (strlen($password) < 6) {
            $_SESSION['error'] = "Password must be at least 6 characters.";
        } elseif ($password !== $confirm) {
            $_SESSION['error'] = "Passwords do not match.";
        } else {
            $this->userModel->updatePassword($user_id, md5($password));
            $_SESSION['success'] = "Password updated.";
        }
        header("Location: ../views/member/profile.php");
        exit();
    }

    public function applyExpert() {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            header("Location: ../views/member/profile.php");
            exit();
        }
        $session     = AuthMiddleware::getSession();
        $user_id     = $session['user_id'];
        $domain      = isset($_POST['domain'])      ? trim($_POST['domain'])      : '';
        $credentials = isset($_POST['credentials']) ? trim($_POST['credentials']) : '';
        $motivation  = isset($_POST['motivation'])  ? trim($_POST['motivation'])  : '';

        if (empty($domain) || empty($credentials) || empty($motivation)) {
            $_SESSION['error'] = "All fields are required for expert application.";
        } elseif ($this->appModel->hasPending($user_id)) {
            $_SESSION['error'] = "You already have a pending application.";
        } else {
            $this->appModel->create($user_id, $domain, $credentials, $motivation);
            $_SESSION['success'] = "Expert application submitted. Please wait for admin review.";
        }
        header("Location: ../views/member/profile.php");
        exit();
    }
}
?>
