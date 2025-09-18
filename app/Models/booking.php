

<?php

class Booking
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }
}

?>