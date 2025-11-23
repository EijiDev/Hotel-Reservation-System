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

    public function contact()
    {
        include __DIR__ . '/../Views/contact.php';
    }
}
