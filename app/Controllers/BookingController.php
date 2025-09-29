<?php
namespace App\Controllers;
use App\Models\Booking;

class BookingController {
    private $bookingModel;

    public function __construct($db) {
        $this->bookingModel = new Booking($db);
    }

    public function store() {
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

            // Redirect after saving
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=home&action=index&success=1");
            exit();
        }
    }
}
