<?php

namespace App\Models;

use PDO;

class Booking
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create($checkin, $checkout, $guests, $checkin_time, $contact, $email, $payment_method)
    {
        $stmt = $this->db->prepare("
            INSERT INTO bookings (checkin, checkout, guests, checkin_time, contact, email, payment_method)
            VALUES (:checkin, :checkout, :guests, :checkin_time, :contact, :email, :payment_method)
        ");
        return $stmt->execute([
            ':checkin' => $checkin,
            ':checkout' => $checkout,
            ':guests' => $guests,
            ':checkin_time' => $checkin_time,
            ':contact' => $contact,
            ':email' => $email,
            ':payment_method' => $payment_method
        ]);
    }
}
