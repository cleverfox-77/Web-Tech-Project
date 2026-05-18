<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    public function showRegister() {
        $data = array('errors' => array(), 'old' => array());
        return $data;
    }

    public function register() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $errors = array();
        $name     = isset($_POST['name'])     ? trim($_POST['name'])     : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $confirm  = isset($_POST['confirm'])  ? trim($_POST['confirm'])  : '';

        if (empty($name))     $errors[] = "Name is required.";
        if (empty($username)) $errors[] = "Username is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        if ($password !== $confirm) $errors[] = "Passwords do not match.";

        if (empty($errors) && $this->userModel->usernameExists($username)) {
            $errors[] = "Username is already taken.";
        }

        if (empty($errors)) {
            $hash = md5($password);
            $this->userModel->register($name, $username, $email, $hash);
            $_SESSION['success'] = "Registration successful. Please log in.";
            header("Location: ../views/member/login.php");
            exit();
        }

        return array('errors' => $errors, 'old' => array(
            'name' => $name, 'username' => $username, 'email' => $email
        ));
    }

    public function login() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $errors   = array();
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($username)) $errors[] = "Username is required.";
        if (empty($password)) $errors[] = "Password is required.";

        if (empty($errors)) {
            $hash = md5($password);
            $user = $this->userModel->login($username, $hash);
            if ($user && $user['is_active'] == 1) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];
                header("Location: ../views/member/dashboard.php");
                exit();
            } else {
                $errors[] = "Invalid username or password.";
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
        header("Location: ../views/member/login.php");
        exit();
    }
}
?>
