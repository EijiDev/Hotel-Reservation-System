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
        }
    }
}
