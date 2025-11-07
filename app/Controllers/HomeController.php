<?php

namespace App\Controllers;

use App\Models\Room;
use App\Models\Booking;

class HomeController
{
    private $db;
    private $roomModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->roomModel = new Room($db);
    }

    public function index()
    {
        $rooms = $this->roomModel->getAllRooms();
        include __DIR__ . '/../Views/home.php';
    }

    public function userBookings()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=auth&action=login");
            exit;
        }

        $userId = $_SESSION['user_id'];

        $bookingModel = new Booking($this->db);
        $bookings = $bookingModel->getBookingsByUser($userId);

        include __DIR__ . '/../Views/userbookings.php';
    }
}
