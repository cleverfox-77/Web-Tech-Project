<?php
require_once __DIR__ . '/../models/ExpertUser.php';

class AuthController {
    private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new ExpertUser();
    }

    public function login() {
        $errors   = array();
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($username)) $errors[] = "Username is required.";
        if (empty($password)) $errors[] = "Password is required.";

        if (empty($errors)) {
            $hash = md5($password);
            $user = $this->userModel->login($username, $hash);
            if ($user && $user['is_active'] == 1) {
                $_SESSION['user_id']      = $user['id'];
                $_SESSION['user_name']    = $user['name'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['role']         = $user['role'];
                $_SESSION['expert_domain']= $user['expert_domain'];
                header("Location:dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid credentials or account not approved as expert.";
            }
        }
        return array('errors' => $errors);
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: ../views/expert/login.php");
        exit();
    }
}
?>
