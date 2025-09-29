<?php

namespace App\Controllers;

use App\Models\User;

class SignUpController
{
    private $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function signup(string $name, string $email, string $password): void
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if ($this->userModel->create($email, $name, $hashedPassword)) {
            // Redirect to login page after successful signup
            header("Location: /Hotel_Reservation_System/app/Views/login.php");
            exit();
        } else {
            echo "Error during sign up. Please try again.";
            include __DIR__ . '/../Views/signup.php';
        }
    }
}
