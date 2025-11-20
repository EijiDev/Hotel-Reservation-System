<?php
// Start session with security settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', 0);
    session_start();
}

// Session timeout check (2 hours)
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        session_unset();
        session_destroy();
        session_start();
        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=session_expired");
        exit;
    }
    $_SESSION['last_activity'] = time();
    
    // Validate session integrity
    if (!isset($_SESSION['role'])) {
        session_unset();
        session_destroy();
        session_start();
    }
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
use App\Controllers\StaffController;
use App\Models\User;

// Database connection
$db = (new Database())->connect();

// Routing params
$controllerName = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Normalize names
$controllerName = preg_replace('/[^a-z0-9_]/i', '', $controllerName);
$action = preg_replace('/[^a-z0-9_]/i', '', $action);

$controller = null;

switch ($controllerName) {
    case 'login':
        $userModel = new User($db);
        $controller = new LoginController($userModel);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
            $controller->login($_POST['email'] ?? '', $_POST['password'] ?? '');
        } else {
            $controller->index();
        }
        exit;

    case 'signup':
        $userModel = new User($db);
        $controller = new SignUpController($userModel);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'signup') {
            $controller->signup($_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
        } else {
            $controller->index();
        }
        exit;

    case 'booking':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
            exit;
        }
        $controller = new BookingController($db);
        break;

    case 'room':
        $controller = new RoomController($db);
        break;

    case 'admin':
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin' || !isset($_SESSION['user_id'])) {
            error_log("❌ Unauthorized admin access. Session: " . print_r($_SESSION, true));
            session_unset();
            session_destroy();
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
            exit;
        }
        $controller = new AdminController($db);
        break;

    case 'staff':
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff' || !isset($_SESSION['user_id'])) {
            error_log("❌ Unauthorized staff access. Session: " . print_r($_SESSION, true));
            session_unset();
            session_destroy();
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
            exit;
        }
        $controller = new StaffController(); // No $db parameter
        break;

    case 'logout':
        $controller = new LogoutController();
        $controller->index();
        exit;

    case 'home':
    default:
        $controller = new HomeController($db);
        break;
}

// Handle booking controller actions
if ($controllerName === 'booking') {
    switch ($action) {
        case 'show':
            $roomId = $_GET['room_id'] ?? null;
            $controller->show($roomId);
            break;
        case 'store':
            $controller->store();
            break;
        case 'edit':
            $bookingId = $_GET['id'] ?? null;
            if ($bookingId) $controller->edit((int)$bookingId);
            else http_response_code(400) && exit('No booking ID provided.');
            break;
        case 'cancel':
            $bookingId = $_GET['id'] ?? null;
            if ($bookingId) $controller->cancel((int)$bookingId);
            else http_response_code(400) && exit('No booking ID provided.');
            break;
        case 'userBookings':
            $controller->userBookings();
            break;
        default:
            http_response_code(400);
            echo "Invalid booking action.";
            break;
    }
    exit;
}

// Generic dispatch for other controllers
if ($controller && method_exists($controller, $action)) {
    $ref = new ReflectionMethod($controller, $action);
    if ($ref->isPublic()) {
        $controller->$action();
        exit;
    } else {
        http_response_code(403);
        echo "Forbidden action.";
        exit;
    }
}

http_response_code(404);
echo "Controller action not found.";
exit;