<?php

namespace App\Controllers;

class LogoutController
{
    public function index()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Log the logout action
        error_log("🚪 Logging out user: " . ($_SESSION['user_id'] ?? 'unknown'));

        // Unset all session variables
        $_SESSION = array();

        // Delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destroy the session
        session_destroy();

        // Prevent caching
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Redirect to login page
        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
        exit();
    }
}