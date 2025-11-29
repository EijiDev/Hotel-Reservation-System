<?php

namespace App\Models;

use PDO;
use PDOException;

class Booking
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get bookings for a specific user
     */
    public function getBookingsByUser($userId)
    {
        $sql = "
            SELECT 
                b.BookingID,
                b.CheckIn,
                b.CheckOut,
                b.Guests,
                b.CheckIn_Time,
                b.Contact,
                b.Email,
                b.Created_At,
                b.UserID,
                bs.StatusName AS booking_status,
                r.RoomID,
                r.RoomNumber,
                r.Floor,
                r.Status AS room_status,
                r.image AS room_image,
                rt.Name AS room_name,
                rt.Price AS room_price,
                rt.Description AS room_description,
                rt.Amenities,
                p.PaymentID,
                p.Method AS payment_method,
                p.Amount,
                p.Status AS payment_status,
                p.DatePaid
            FROM bookings b
            JOIN rooms r ON b.RoomID = r.RoomID
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            LEFT JOIN payments p ON b.BookingID = p.BookingID
            WHERE b.UserID = :user_id
            ORDER BY b.BookingID DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single booking by ID
     */
    public function getBookingById($bookingId, $includeDeleted = false)
    {
        $deletedCondition = $includeDeleted ? "" : "AND b.IsDeleted = 0";

        $sql = "
        SELECT 
            b.*,
            bs.StatusName AS booking_status,
            r.RoomID,
            r.RoomNumber,
            r.Floor,
            rt.Name AS room_name,
            rt.Price,
            rt.Description,
            rt.Amenities,
            p.Method AS payment_method,
            p.Amount,
            p.Status AS payment_status,
            p.DatePaid,
            p.PaymentID,
            u.Name AS user_name,
            u.Email AS user_email
        FROM bookings b
        JOIN rooms r ON b.RoomID = r.RoomID
        JOIN roomtypes rt ON r.TypeID = rt.TypeID
        JOIN booking_status bs ON b.StatusID = bs.StatusID
        JOIN useraccounts u ON b.UserID = u.UserID
        LEFT JOIN payments p ON b.BookingID = p.BookingID
        WHERE b.BookingID = :booking_id {$deletedCondition}
        LIMIT 1
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new booking
     */
    public function create($checkin, $checkout, $guests, $checkin_time, $contact, $email, $room_id, $user_id, $statusId = null)
    {
        if ($statusId === null) {
            $statusId = $this->getStatusIdByName('pending');
        }

        $sql = "
            INSERT INTO bookings (
                CheckIn, CheckOut, Guests, CheckIn_Time, 
                Contact, Email, RoomID, UserID, StatusID
            ) VALUES (
                :checkin, :checkout, :guests, :checkin_time, 
                :contact, :email, :room_id, :user_id, :status_id
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':checkin', $checkin);
        $stmt->bindValue(':checkout', $checkout);
        $stmt->bindValue(':guests', $guests, PDO::PARAM_INT);
        $stmt->bindValue(':checkin_time', $checkin_time);
        $stmt->bindValue(':contact', $contact);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);

        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    /**
     * Update booking details
     */
    public function updateBooking($bookingId, $checkin, $checkout, $guests, $checkin_time, $contact, $email)
    {
        $sql = "
            UPDATE bookings 
            SET CheckIn = :checkin, CheckOut = :checkout, Guests = :guests,
                CheckIn_Time = :checkin_time, Contact = :contact, Email = :email
            WHERE BookingID = :booking_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':checkin', $checkin);
        $stmt->bindValue(':checkout', $checkout);
        $stmt->bindValue(':guests', $guests, PDO::PARAM_INT);
        $stmt->bindValue(':checkin_time', $checkin_time);
        $stmt->bindValue(':contact', $contact);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Update booking status
     */
    public function updateStatus($bookingId, $statusId)
    {
        $sql = "UPDATE bookings SET StatusID = :status_id WHERE BookingID = :booking_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Update booking status by name
     */
    public function updateStatusByName($bookingId, $statusName)
    {
        $statusId = $this->getStatusIdByName($statusName);
        return $statusId ? $this->updateStatus($bookingId, $statusId) : false;
    }

    /**
     * Delete booking and related records
     */
    public function deleteBooking($bookingId)
    {
        try {
            $this->db->beginTransaction();

            // Delete guests
            $stmt = $this->db->prepare("DELETE FROM guests WHERE BookingID = :booking_id");
            $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete payments
            $stmt = $this->db->prepare("DELETE FROM payments WHERE BookingID = :booking_id");
            $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete booking
            $stmt = $this->db->prepare("DELETE FROM bookings WHERE BookingID = :booking_id");
            $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Failed to delete booking {$bookingId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete booking (cancel)
     */
    public function softDeleteBooking($bookingId)
    {
        $cancelledStatusId = $this->getStatusIdByName('cancelled');

        if (!$cancelledStatusId) {
            return false;
        }

        $sql = "UPDATE bookings SET StatusID = :status_id WHERE BookingID = :booking_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status_id', $cancelledStatusId, PDO::PARAM_INT);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get bookings by room ID
     */
    public function getBookingsByRoomId($roomId)
    {
        $sql = "
            SELECT b.*, bs.StatusName
            FROM bookings b
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE b.RoomID = :room_id
            AND bs.StatusName IN ('pending', 'confirmed')
            ORDER BY b.CheckIn ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restore($bookingId)
    {
        $sql = "UPDATE bookings SET IsDeleted = 0, DeletedAt = NULL WHERE BookingID = :booking_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            error_log("✅ Restored booking {$bookingId}");
            return true;
        }

        error_log("❌ Failed to restore booking {$bookingId}");
        return false;
    }

    //get
    public function getAllBookings($limit = null, $offset = null)
    {
        $sql = "
            SELECT 
                b.BookingID,
                b.CheckIn,
                b.CheckOut,
                b.CheckIn_Time,
                b.Guests,
                b.RoomID,
                b.UserID,
                b.Created_At,
                bs.StatusName AS booking_status,
                u.Name AS GuestName,
                u.Email,
                rt.Name AS RoomType,
                rt.Price AS room_price,
                r.RoomNumber,
                p.PaymentID,
                p.Method AS payment_method,
                p.Amount AS TotalAmount,
                p.Status AS payment_status
            FROM bookings b
            LEFT JOIN useraccounts u ON b.UserID = u.UserID
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            LEFT JOIN roomtypes rt ON r.TypeID = rt.TypeID
            LEFT JOIN booking_status bs ON b.StatusID = bs.StatusID
            LEFT JOIN payments p ON b.BookingID = p.BookingID
            ORDER BY b.BookingID DESC
        ";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //get status by id
    public function getStatusIdByName($statusName)
    {
        $stmt = $this->db->prepare("SELECT StatusID FROM booking_status WHERE StatusName = :status_name LIMIT 1");
        $stmt->bindParam(':status_name', $statusName, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['StatusID'] : null;
    }

    //Get all booking statuses
    public function getAllStatuses()
    {
        $stmt = $this->db->query("SELECT * FROM booking_status ORDER BY StatusID");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
