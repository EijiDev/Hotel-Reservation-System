<?php

namespace App\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Booking;

class HomeController
{
    private $db;
    private $roomModel;
    private $roomTypeModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->roomModel = new Room($db);
        $this->roomTypeModel = new RoomType($db);
    }

    public function index()
    {
        // Get rooms with room type information
        $rooms = $this->roomModel->getAllRooms();
        
        // Get room types with availability info
        $roomTypes = $this->roomTypeModel->getRoomTypesWithAvailability();
        
        include __DIR__ . '/../Views/home.php';
    }

    public function userBookings()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
            exit;
        }

        $userId = $_SESSION['user_id'];

        $bookingModel = new Booking($this->db);
        $bookings = $bookingModel->getBookingsByUser($userId);

        include __DIR__ . '/../Views/userbookings.php';
    }
}

// ==========================================
// RoomController
// ==========================================

namespace App\Controllers;

use App\Models\Room;
use App\Models\RoomType;

class RoomController
{
    private $roomModel;
    private $roomTypeModel;

    public function __construct($db)
    {
        $this->roomModel = new Room($db);
        $this->roomTypeModel = new RoomType($db);
    }

    // Landing page: first 6 rooms with type info
    public function index()
    {
        $rooms = $this->roomModel->getAllRooms();
        $rooms = array_slice($rooms, 0, 6);
        
        // Get room types for filtering
        $roomTypes = $this->roomTypeModel->getAllRoomTypes();
        
        include __DIR__ . '/../Views/rooms.php';
    }

    // Rooms 7-12
    public function availableRooms()
    {
        $rooms = $this->roomModel->getRoomsRange(6, 6);
        
        // Get room types for filtering
        $roomTypes = $this->roomTypeModel->getAllRoomTypes();
        
        include __DIR__ . '/../Views/availablerooms.php';
    }

    // View rooms by type
    public function byType($typeId = null)
    {
        if (!$typeId && isset($_GET['type_id'])) {
            $typeId = intval($_GET['type_id']);
        }

        if (!$typeId) {
            http_response_code(400);
            exit("Room type ID required");
        }

        $roomType = $this->roomTypeModel->getRoomTypeById($typeId);
        if (!$roomType) {
            http_response_code(404);
            exit("Room type not found");
        }

        $rooms = $this->roomModel->getRoomsByType($typeId);

        include __DIR__ . '/../Views/rooms_by_type.php';
    }

    // Check availability for date range
    public function checkAvailability()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $roomId = isset($_POST['room_id']) ? intval($_POST['room_id']) : null;
        $checkIn = $_POST['check_in'] ?? null;
        $checkOut = $_POST['check_out'] ?? null;

        if (!$roomId || !$checkIn || !$checkOut) {
            http_response_code(400);
            exit("Missing required parameters");
        }

        $isAvailable = $this->roomModel->isRoomAvailable($roomId, $checkIn, $checkOut);

        header('Content-Type: application/json');
        echo json_encode(['available' => $isAvailable]);
        exit();
    }
}