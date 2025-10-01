<?php

namespace App\Controllers;

class AdminController
{
    private $db;

    public function __construct()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo "<p>You do not have authorization to access this page.</p>";
            header("Location: /Hotel_Reservation_System/app/views/login.php?error=unauthorized");
            exit;
        }
    }

    public function index()
    {
        include __DIR__ . '/../Views/admin/dashboard.php';
    }
}
