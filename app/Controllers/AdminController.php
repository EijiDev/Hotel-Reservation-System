<?php

namespace App\Controllers;

class AdminController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index()
    {
        include __DIR__ . '/../Views/admin/dashboard.php';
    }
}
