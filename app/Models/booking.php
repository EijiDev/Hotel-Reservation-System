

<?php

class Booking
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getBooking($db) {
        $checkIn = $_POST['checkin'];
    }
}

?>