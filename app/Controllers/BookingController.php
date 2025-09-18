
<?php
include_once __DIR__ . "/../Models/booking.php";

class BookingController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index()
    {
        include_once __DIR__ . "/../Views/home.php";
    }
}

?>