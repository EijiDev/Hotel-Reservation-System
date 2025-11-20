<?php

namespace App\Helpers;

class SessionHelper
{
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_lifetime', 0); // Session expires when browser closes
            
            session_start();
        }
    }

    public static function isLoggedIn()
    {
        self::startSession();
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
            exit();
        }
    }

    public static function requireRole($allowedRoles = [])
    {
        self::requireLogin();
        
        $userRole = $_SESSION['role'] ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
            exit();
        }
    }

    public static function destroy()
    {
        self::startSession();
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
}