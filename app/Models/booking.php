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

    public function getBookingsByUser($userId)
    {
        $stmt = $this->db->prepare("
        SELECT 
            b.BookingID,
            b.RoomID AS room_id,
            b.CheckIn,
            b.CheckOut,
            b.Guests,
            b.Payment_Method,
            b.Status AS booking_status,
            r.name AS room_name,
            r.price AS room_price,
            r.image AS room_image,
            r.floor,
            r.rating,
            r.availability AS room_availability,
            r.status AS room_status
        FROM bookings b
        JOIN rooms r ON b.RoomID = r.RoomID
        WHERE b.UserID = ?
        ORDER BY b.CheckIn DESC
    ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookingById($bookingId)
    {
        $stmt = $this->db->prepare("
        SELECT 
            b.BookingID,
            b.UserID,
            b.RoomID AS room_id,
            b.CheckIn,
            b.CheckOut,
            b.Guests,
            b.Payment_Method,
            b.Status AS booking_status,
            r.name AS room_name,
            r.price AS room_price,
            r.image AS room_image,
            r.floor,
            r.rating,
            r.availability AS room_availability,
            r.status AS room_status
        FROM bookings b
        JOIN rooms r ON b.RoomID = r.RoomID
        WHERE b.BookingID = ?
        LIMIT 1
    ");
        $stmt->execute([$bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteBooking($bookingId)
    {
        $stmt = $this->db->prepare("DELETE FROM bookings WHERE BookingID = ?");
        return $stmt->execute([$bookingId]);
    }
    public function getAllBookings()
    {
        $stmt = $this->db->prepare("
        SELECT 
            b.BookingID,
            b.CheckIn,
            b.CheckOut,
            b.Guests,
            b.CheckIn_Time,
            b.Contact,
            b.Email,
            b.Payment_Method,
            b.total_amount AS TotalAmount,
            b.status AS booking_status,
            u.name AS GuestName,
            r.name AS RoomType
        FROM bookings b
        JOIN users u ON b.UserID = u.UserID
        JOIN rooms r ON b.RoomID = r.RoomID
        ORDER BY b.CheckIn DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
