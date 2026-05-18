<?php
class AuthMiddleware {
    public static function checkExpert() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../views/expert/login.php");
            exit();
        }
        if ($_SESSION['role'] !== 'expert') {
            header("Location: ../views/expert/login.php");
            exit();
        }
    }

    public static function getSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION;
    }

    public static function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
?>
