
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../Models/Booking.php";
require_once __DIR__ . "/../Controllers/BookingController.php";
require_once __DIR__ . "/../Models/User.php";
require_once __DIR__ . "/../Controllers/HomeController.php";
require_once __DIR__ . "/../Controllers/AuthController.php";

// Connect database
$db = (new Database())->connect();

// Handle routing
$controllerName = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

if ($controllerName === 'booking') {
    $controller = new BookingController($db);
} elseif ($controllerName === 'auth') {
    $controller = new AuthController($db);
} else {
    $controller = new HomeController();
}

// Call action if exists
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    echo "Error: Action '$action' not found in $controllerName controller.";
}
