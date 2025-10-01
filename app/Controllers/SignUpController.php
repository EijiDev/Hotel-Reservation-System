<?php

namespace App\Controllers;
use App\Models\User;

class SignUpController {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function index() {
        include __DIR__ . '/../Views/signup.php';
    }

    public function signup($name, $email, $password) {
        $this->userModel->create($name, $email, $password);
        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
        exit;
    }
}
