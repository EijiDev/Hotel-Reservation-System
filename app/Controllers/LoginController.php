<?php

namespace App\Controllers;

use App\Models\User;

class LoginController
{
    private $userModel;

    public function __construct($userModel)
    {
        $this->userModel = $userModel;
    }

    public function index()
    {
        // Check if there's an error message from login
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']); // clear it after showing once
        include __DIR__ . '/../Views/login.php';
    }

    public function login($email, $password)
    {
        $user = $this->userModel->authenticate($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['role'];

            if (strtolower($user['role']) === 'admin') {
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index");
                exit;
            } else {
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index");
                exit;
            }
        } else {
            // Invalid credentials — store error message in session
            $_SESSION['error'] = "Invalid email or password. Please try again.";
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
            exit;
        }
    }
}
