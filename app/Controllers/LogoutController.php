<?php

namespace App\Controllers;

class LogoutController
{
    public function index()
    {
        session_unset();
        session_destroy();

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index");
        exit;
    }
}
