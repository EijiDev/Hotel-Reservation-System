<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Room;

class BookingController
{
    private $bookingModel;
    private $roomModel;

    public function __construct($db)
    {
        $this->bookingModel = new Booking($db);
        $this->roomModel = new Room($db); 
    }

    public function show($roomId)
    {
        if (!$roomId) {
            die("No room ID provided.");
        }

        $room = $this->roomModel->getRoomById($roomId);

        if (!$room) {
            die("Room not found.");
        }

        include __DIR__ . '/../Views/roombookings.php';
    }


    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $checkin = $_POST['checkin'];
            $checkout = $_POST['checkout'];
            $guests = $_POST['guests'];
            $checkin_time = $_POST['checkin_time'];
            $contact = $_POST['contact'];
            $email = $_POST['email'];
            $payment_method = $_POST['payment_method'];

            $this->bookingModel->create(
                $checkin,
                $checkout,
                $guests,
                $checkin_time,
                $contact,
                $email,
                $payment_method
            );
            
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index&success=1");
            exit();
        }
    }
}
