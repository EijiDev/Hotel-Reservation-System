<?php

namespace App\Controllers;

class LogoutController
{
    public function index()
    {
        // Destroy session
        session_unset();
        session_destroy();

        // Redirect to Home page
        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index");
        exit;
    }
}
