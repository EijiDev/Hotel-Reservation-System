<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\SignUpController;
use App\Controllers\BookingController;
use App\Controllers\RoomController;
use App\Models\User;

// Connect database
$db = (new Database())->connect();

// Initialize models and controllers
$userModel = new User($db);
$loginController = new LoginController($userModel);
$signUpController = new SignUpController($userModel);

// Handle routing
$controllerName = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginController->login($_POST['email'], $_POST['password']);
} elseif ($action === 'signup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $signUpController->signup($_POST['name'], $_POST['email'], $_POST['password']);
} elseif ($controllerName === 'booking') {
    $controller = new BookingController($db);
} elseif ($controllerName === 'room') {
    $controller = new RoomController($db);
} else {
    $controller = new HomeController();
}

// Call action if exists
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    echo "Error: Action '$action' not found in " . get_class($controller) . " controller.";
}
