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
     * Get bookings for a specific user with detailed information
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

        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("🔍 getBookingsByUser called for UserID: {$userId}");
        error_log("📊 Found " . count($bookings) . " bookings");

        return $bookings;
    }

    /**
     * Get a single booking by ID with full details
     */
    public function getBookingById($bookingId)
    {
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
                u.Name AS user_name,
                u.Email AS user_email
            FROM bookings b
            JOIN rooms r ON b.RoomID = r.RoomID
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            JOIN useraccounts u ON b.UserID = u.UserID
            LEFT JOIN payments p ON b.BookingID = p.BookingID
            WHERE b.BookingID = :booking_id
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
        // Default to 'pending' status if not provided
        if ($statusId === null) {
            $statusId = $this->getStatusIdByName('pending');
        }

        $sql = "
            INSERT INTO bookings (
                CheckIn, 
                CheckOut, 
                Guests, 
                CheckIn_Time, 
                Contact, 
                Email, 
                RoomID, 
                UserID, 
                StatusID
            ) VALUES (
                :checkin, 
                :checkout, 
                :guests, 
                :checkin_time, 
                :contact, 
                :email, 
                :room_id, 
                :user_id, 
                :status_id
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

        if ($stmt->execute()) {
            $newId = $this->db->lastInsertId();
            error_log("✅ Created booking ID {$newId} for UserID {$user_id}");
            return $newId;
        }

        error_log("❌ Failed to create booking for UserID {$user_id}");
        return false;
    }

    public function updateBooking($bookingId, $checkin, $checkout, $guests, $checkin_time, $contact, $email)
    {
        $sql = "
            UPDATE bookings 
            SET 
                CheckIn = :checkin,
                CheckOut = :checkout,
                Guests = :guests,
                CheckIn_Time = :checkin_time,
                Contact = :contact,
                Email = :email
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

    public function updateStatus($bookingId, $statusId)
    {
        $sql = "UPDATE bookings SET StatusID = :status_id WHERE BookingID = :booking_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status_id', $statusId, PDO::PARAM_INT);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            error_log("✅ Updated booking {$bookingId} status to StatusID '{$statusId}'");
            return true;
        }

        error_log("❌ Failed to update booking {$bookingId} status");
        return false;
    }

    public function updateStatusByName($bookingId, $statusName)
    {
        $statusId = $this->getStatusIdByName($statusName);
        if (!$statusId) {
            error_log("❌ Invalid status name: {$statusName}");
            return false;
        }

        return $this->updateStatus($bookingId, $statusId);
    }

    public function deleteBooking($bookingId)
    {
        try {
            // Start transaction to ensure all deletions happen together
            $this->db->beginTransaction();

            // Delete related guests first (child records)
            $sqlGuests = "DELETE FROM guests WHERE BookingID = :booking_id";
            $stmtGuests = $this->db->prepare($sqlGuests);
            $stmtGuests->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmtGuests->execute();
            
            $guestsDeleted = $stmtGuests->rowCount();
            error_log("🗑️ Deleted {$guestsDeleted} guest(s) for BookingID {$bookingId}");

            // Delete related payments (if any)
            $sqlPayments = "DELETE FROM payments WHERE BookingID = :booking_id";
            $stmtPayments = $this->db->prepare($sqlPayments);
            $stmtPayments->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmtPayments->execute();
            
            $paymentsDeleted = $stmtPayments->rowCount();
            error_log("🗑️ Deleted {$paymentsDeleted} payment(s) for BookingID {$bookingId}");

            // Finally, delete the booking itself (parent record)
            $sqlBooking = "DELETE FROM bookings WHERE BookingID = :booking_id";
            $stmtBooking = $this->db->prepare($sqlBooking);
            $stmtBooking->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
            $stmtBooking->execute();

            // Commit transaction
            $this->db->commit();
            
            error_log("✅ Successfully deleted booking {$bookingId} and all related records");
            return true;

        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("❌ Failed to delete booking {$bookingId}: " . $e->getMessage());
            return false;
        }
    }

    public function softDeleteBooking($bookingId)
    {
        $cancelledStatusId = $this->getStatusIdByName('cancelled');
        
        if (!$cancelledStatusId) {
            error_log("❌ 'cancelled' status not found in booking_status table");
            return false;
        }

        $sql = "UPDATE bookings SET StatusID = :status_id WHERE BookingID = :booking_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status_id', $cancelledStatusId, PDO::PARAM_INT);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            error_log("✅ Soft deleted (cancelled) booking {$bookingId}");
            return true;
        }

        error_log("❌ Failed to soft delete booking {$bookingId}");
        return false;
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

        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("🔍 getBookingsByRoomId called for RoomID: {$roomId}");
        error_log("📊 Found " . count($bookings) . " bookings");

        return $bookings;
    }

    /**
     * Get all bookings (for admin/staff)
     */
    public function getAllBookings($limit = null, $offset = null)
    {
        $sql = "
            SELECT 
                b.BookingID,
                b.CheckIn,
                b.CheckOut,
                b.Guests,
                b.Created_At,
                bs.StatusName AS booking_status,
                u.Name AS GuestName,
                u.UserID,
                u.Email,
                rt.Name AS RoomType,
                rt.Price,
                r.RoomNumber,
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

    /**
     * Get status ID by status name
     */
    public function getStatusIdByName($statusName)
    {
        $stmt = $this->db->prepare("SELECT StatusID FROM booking_status WHERE StatusName = :status_name LIMIT 1");
        $stmt->bindParam(':status_name', $statusName, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['StatusID'] : null;
    }

    /**
     * Get all booking statuses
     */
    public function getAllStatuses()
    {
        $stmt = $this->db->query("SELECT * FROM booking_status ORDER BY StatusID");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}