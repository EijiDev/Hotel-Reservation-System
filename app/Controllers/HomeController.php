<?php
namespace App\Controllers;
class HomeController
{
    public function index()
    {
        $title = "Home Page";
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/home.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }
}
