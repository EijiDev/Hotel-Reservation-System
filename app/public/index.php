
<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../Models/Booking.php";
require_once __DIR__ . "/../Controllers/BookingController.php";
require_once __DIR__ . "/../Models/User.php";
require_once __DIR__ . "/../Controllers/HomeController.php";
require_once __DIR__ . "/../Controllers/LoginController.php";
require_once __DIR__ . "/../Controllers/SignUpController.php";

// Connect database
$db = (new Database())->connect();


// Initialize models and controllers
$userModel = new User($db);
$loginController = new LoginController($userModel);
$SignUpController = new SignUpController($userModel);

// Handle routing
$controllerName = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginController->login($_POST['email'], $_POST['password']);
} elseif ($action === 'signup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $SignUpController->signup($_POST['name'], $_POST['email'], $_POST['password']);
} elseif ($controllerName === 'booking') {
    $controller = new BookingController($db);
} else {
    $controller = new HomeController();
}

// Call action if exists
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    echo "Error: Action '$action' not found in $controllerName controller.";
}
