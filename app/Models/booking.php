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

    public function create($checkin, $checkout, $guests, $checkin_time, $contact, $email, $payment_method, $room_id, $user_id)
    {
        $stmt = $this->db->prepare("
            INSERT INTO bookings 
            (CheckIn, CheckOut, Guests, CheckIn_Time, Contact, Email, Payment_Method, RoomID, UserID)
            VALUES 
            (:checkin, :checkout, :guests, :checkin_time, :contact, :email, :payment_method, :room_id, :user_id)
        ");

        return $stmt->execute([
            ':checkin' => $checkin,
            ':checkout' => $checkout,
            ':guests' => $guests,
            ':checkin_time' => $checkin_time,
            ':contact' => $contact,
            ':email' => $email,
            ':payment_method' => $payment_method,
            ':room_id' => $room_id,
            ':user_id' => $user_id
        ]);
    }
}
