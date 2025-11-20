<?php

namespace App\Controllers;

use App\Models\User;

class LoginController
{
    private $userModel;

    public function __construct(User $userModel)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If already logged in, redirect
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            $this->redirectToDashboard();
        }

        $this->userModel = $userModel;
    }

    public function index()
    {
        include __DIR__ . '/../Views/login.php';
    }

    public function login($email, $password)
    {
        // Validate input
        if (empty($email) || empty($password)) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=empty_fields");
            exit();
        }

        // Authenticate user
        $user = $this->userModel->getUserByEmail($email);

        if ($user && isset($user['Password']) && password_verify($password, $user['Password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role']; // Now normalized from 'role' to 'Role'
            $_SESSION['name'] = $user['Name'];
            $_SESSION['last_activity'] = time();

            error_log("✅ User logged in: UserID=" . $user['UserID'] . ", Email=" . $email . ", Role=" . $user['Role']);

            $this->redirectToDashboard();
        } else {
            error_log("❌ Failed login attempt for: " . $email);
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=invalid_credentials");
            exit();
        }
    }

    private function redirectToDashboard()
    {
        $role = $_SESSION['role'] ?? 'user';

        error_log("🔄 Redirecting to dashboard. Role: " . $role);

        switch ($role) {
            case 'admin':
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index");
                break;
            case 'staff':
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index");
                break;
            default:
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index");
                break;
        }
        exit();
    }
}