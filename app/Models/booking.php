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

    /**
     * Get all bookings for a specific user with room details
     */
    public function getBookingsByUser($userId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                b.BookingID,
                b.RoomID as room_id,
                b.UserID,
                b.CheckIn,
                b.CheckOut,
                b.Guests,
                b.CheckIn_Time,
                b.Contact,
                b.Email,
                b.Payment_Method,
                b.total_amount,
                b.status as booking_status,
                r.name as room_name,
                r.price as room_price,
                r.image as room_image,
                r.floor as room_floor,
                r.description as room_description
            FROM bookings b
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            WHERE b.UserID = :user_id
            ORDER BY b.BookingID DESC
        ");

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug log
        error_log("📋 Fetched " . count($bookings) . " bookings for UserID: " . $userId);
        if (!empty($bookings)) {
            error_log("🔍 Sample booking columns: " . implode(', ', array_keys($bookings[0])));
        }

        return $bookings;
    }

    /**
     * Get booking by ID with room details
     */
    /**
     * Get booking by ID with room details
     */
    public function getBookingById($bookingId)
    {
        $stmt = $this->db->prepare("
        SELECT 
            b.*,
            b.RoomID as room_id,
            b.status as booking_status,
            r.name as room_name,
            r.name as RoomType,
            r.price as room_price,
            r.image as room_image,
            r.floor as room_floor,
            r.description as room_description
        FROM bookings b
        LEFT JOIN rooms r ON b.RoomID = r.RoomID
        WHERE b.BookingID = :booking_id
        LIMIT 1
    ");

        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new booking
     */
    public function create($checkin, $checkout, $guests, $checkin_time, $contact, $email, $payment_method, $room_id, $user_id, $total)
    {
        $stmt = $this->db->prepare("
            INSERT INTO bookings (
                CheckIn, 
                CheckOut, 
                Guests, 
                CheckIn_Time, 
                Contact, 
                Email, 
                Payment_Method, 
                RoomID, 
                UserID, 
                total_amount,
                status
            ) VALUES (
                :checkin, 
                :checkout, 
                :guests, 
                :checkin_time, 
                :contact, 
                :email, 
                :payment_method, 
                :room_id, 
                :user_id, 
                :total,
                'pending'
            )
        ");

        $stmt->bindParam(':checkin', $checkin);
        $stmt->bindParam(':checkout', $checkout);
        $stmt->bindParam(':guests', $guests, PDO::PARAM_INT);
        $stmt->bindParam(':checkin_time', $checkin_time);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':total', $total);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update existing booking
     */
    public function updateBooking($bookingId, $checkin, $checkout, $guests, $checkin_time, $contact, $email, $payment_method, $total)
    {
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET 
                CheckIn = :checkin,
                CheckOut = :checkout,
                Guests = :guests,
                CheckIn_Time = :checkin_time,
                Contact = :contact,
                Email = :email,
                Payment_Method = :payment_method,
                total_amount = :total
            WHERE BookingID = :booking_id
        ");

        $stmt->bindParam(':checkin', $checkin);
        $stmt->bindParam(':checkout', $checkout);
        $stmt->bindParam(':guests', $guests, PDO::PARAM_INT);
        $stmt->bindParam(':checkin_time', $checkin_time);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':total', $total);
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Update booking status
     */
    public function updateStatus($bookingId, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET status = :status 
            WHERE BookingID = :booking_id
        ");

        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete booking
     */
    public function deleteBooking($bookingId)
    {
        $stmt = $this->db->prepare("DELETE FROM bookings WHERE BookingID = :booking_id");
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get all bookings (for admin/staff)
     */
    public function getAllBookings($limit = null, $offset = null)
    {
        $sql = "
            SELECT 
                b.BookingID,
                b.RoomID as room_id,
                b.UserID,
                b.CheckIn,
                b.CheckOut,
                b.Guests,
                b.CheckIn_Time,
                b.Contact,
                b.Email,
                b.Payment_Method as PaymentMethod,
                b.TotalAmount,
                b.status as booking_status,
                u.Name as GuestName,
                r.name as RoomType,
                r.price as room_price
            FROM bookings b
            LEFT JOIN useraccounts u ON b.UserID = u.UserID
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            ORDER BY b.BookingID DESC
        ";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
            }
        }

        $stmt = $this->db->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
