<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use App\Controllers\SignUpController;
use App\Controllers\BookingController;
use App\Controllers\RoomController;
use App\Controllers\AdminController;
use App\Controllers\LogoutController;
use App\Models\User;


// Database connection
$db = (new Database())->connect();

// Routing params
$controllerName = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Controllers
switch ($controllerName) {
    case 'login':
        $userModel = new User($db);
        $controller = new LoginController($userModel);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
            $controller->login($_POST['email'], $_POST['password']);
            exit;
        } else {
            $controller->index();
            exit;
        }

    case 'signup':
        $userModel = new User($db);
        $controller = new SignUpController($userModel);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'signup') {
            $controller->signup($_POST['name'], $_POST['email'], $_POST['password']);
            exit;
        } else {
            $controller->index();
            exit;
        }
    case 'booking':
        $controller = new BookingController($db);

        if ($action === 'show') {
            $roomId = $_GET['room_id'] ?? null;
            $controller->show($roomId);
        } elseif ($action === 'store') {
            $controller->store();
        } elseif ($action === 'cancel') {
            $bookingId = $_GET['id'] ?? null;
            if ($bookingId) {
                $controller->cancel($bookingId);
            } else {
                echo "No booking ID provided.";
            }
        } elseif ($action === 'userBookings') {
            $controller->userBookings();
        }
        break;

    case 'room':
        $controller = new RoomController($db);
        break;

    case 'admin':
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
            exit;
        }
        $controller = new AdminController();
        break;

    case 'logout':
        $controller = new LogoutController();
        $controller->index();
        exit;

    default:
        $controller = new HomeController($db);
        break;
}

// Run action if exists
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    echo "Error: Action '$action' not found in " . get_class($controller);
}
