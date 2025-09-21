
<?php 

class AuthMiddleware {
    public static function checkAuth() {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: ../Views/login.php");
            exit();
        }
    }

    public static function userOnly () {
        session_start();

        if (isset($_SESSION['user_id'])) {
            header("Location: ../public/home.php");
            exit();
        }
    }
}