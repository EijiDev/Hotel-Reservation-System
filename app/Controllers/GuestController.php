<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\Booking;
use Exception;
use PDO;

class GuestController
{
    private $db;
    private $bookingModel;

    public function __construct()
    {
        // Session is already started and validated in index.php
        // No need to check authorization here again
        
        $this->db = (new Database())->connect();
        $this->bookingModel = new Booking($this->db);
    }

    /**
     * Reservations - ONLY CONFIRMED bookings ready for check-in
     */
    public function reservations()
    {
        error_log("=== RESERVATIONS PAGE LOADED ===");

        // Statistics for reservations
        $stats = [
            'total_reservations' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings b 
                LEFT JOIN guests g ON b.BookingID = g.BookingID 
                LEFT JOIN booking_status bs ON b.StatusID = bs.StatusID
                WHERE b.IsDeleted = 0 
                AND g.GuestID IS NULL 
                AND LOWER(bs.StatusName) = 'confirmed'
            "),
            'pending_bookings' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings b
                JOIN booking_status bs ON b.StatusID = bs.StatusID
                WHERE LOWER(bs.StatusName) = 'pending' 
                AND b.IsDeleted = 0
            "),
            'confirmed_today' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings b
                LEFT JOIN guests g ON b.BookingID = g.BookingID
                JOIN booking_status bs ON b.StatusID = bs.StatusID
                WHERE LOWER(bs.StatusName) = 'confirmed' 
                AND DATE(b.Created_At) = CURDATE()
                AND b.IsDeleted = 0
                AND g.GuestID IS NULL
            ")
        ];

        // Pagination
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalReservations = $this->getValue("
            SELECT COUNT(*) 
            FROM bookings b 
            LEFT JOIN guests g ON b.BookingID = g.BookingID 
            LEFT JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE b.IsDeleted = 0 
            AND g.GuestID IS NULL 
            AND LOWER(bs.StatusName) = 'confirmed'
        ");
        $totalPages = ceil($totalReservations / $limit);

        // Get ONLY CONFIRMED reservations
        $query = "
            SELECT 
                b.BookingID,
                b.CheckIn,
                b.CheckOut,
                b.Guests,
                b.CheckIn_Time,
                b.Contact,
                b.Email,
                b.Street,
                b.Barangay,
                b.City,
                b.Province,
                b.PostalCode,
                b.IDType,
                b.IDImage,
                b.Created_At,
                u.Name AS GuestName,
                rt.Name AS RoomType,
                rt.Price AS room_price,
                r.RoomNumber,
                bs.StatusName,
                p.Status AS PaymentStatus,
                p.Method AS PaymentMethod,
                g.GuestID
            FROM bookings b
            LEFT JOIN useraccounts u ON b.UserID = u.UserID
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            LEFT JOIN roomtypes rt ON r.TypeID = rt.TypeID
            LEFT JOIN booking_status bs ON b.StatusID = bs.StatusID
            LEFT JOIN payments p ON b.BookingID = p.BookingID
            LEFT JOIN guests g ON b.BookingID = g.BookingID
            WHERE b.IsDeleted = 0 
            AND g.GuestID IS NULL 
            AND LOWER(bs.StatusName) = 'confirmed'
            ORDER BY b.CheckIn ASC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Reservations found: " . count($reservations));

        include __DIR__ . '/../Views/guest/reservation.php';
    }

    /**
     * Update reservation details
     */
    public function updateReservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&error=invalid_request");
            exit();
        }

        $bookingId = intval($_POST['booking_id'] ?? 0);
        $checkin = $_POST['checkin'] ?? '';
        $checkout = $_POST['checkout'] ?? '';
        $checkinTime = $_POST['checkin_time'] ?? '14:00';
        $guests = intval($_POST['guests'] ?? 1);
        $status = $_POST['status'] ?? 'pending';
        $street = $_POST['street'] ?? '';
        $barangay = $_POST['barangay'] ?? '';
        $city = $_POST['city'] ?? '';
        $province = $_POST['province'] ?? '';
        $postalCode = $_POST['postal_code'] ?? '';
        $paymentStatus = $_POST['payment_status'] ?? '';

        if (!$bookingId) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&error=invalid_booking");
            exit();
        }

        try {
            // Get status ID
            $statusQuery = "SELECT StatusID FROM booking_status WHERE LOWER(StatusName) = LOWER(?)";
            $stmt = $this->db->prepare($statusQuery);
            $stmt->execute([$status]);
            $statusResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $statusId = $statusResult['StatusID'] ?? 1;

            // Update booking
            $updateQuery = "
                UPDATE bookings 
                SET 
                    CheckIn = ?,
                    CheckOut = ?,
                    CheckIn_Time = ?,
                    Guests = ?,
                    Street = ?,
                    Barangay = ?,
                    City = ?,
                    Province = ?,
                    PostalCode = ?,
                    StatusID = ?
                WHERE BookingID = ? AND IsDeleted = 0
            ";

            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([
                $checkin,
                $checkout,
                $checkinTime,
                $guests,
                $street,
                $barangay,
                $city,
                $province,
                $postalCode,
                $statusId,
                $bookingId
            ]);

            // Update payment status if provided
            if ($paymentStatus) {
                $paymentUpdateQuery = "UPDATE payments SET Status = ? WHERE BookingID = ?";
                $stmt = $this->db->prepare($paymentUpdateQuery);
                $stmt->execute([$paymentStatus, $bookingId]);
                error_log("✅ Payment status updated to '{$paymentStatus}' for Booking #{$bookingId}");
            }

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&success=updated");
            exit();
        } catch (\Exception $e) {
            error_log("Update reservation error: " . $e->getMessage());
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&error=update_failed");
            exit();
        }
    }

    /**
     * Check-in a reservation (move to current guests)
     */
    public function checkin()
    {
        error_log("=== CHECK-IN RESERVATION ===");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&error=invalid_method");
            exit();
        }

        $bookingId = intval($_POST['booking_id'] ?? 0);

        if (!$bookingId) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&error=invalid_booking");
            exit();
        }

        try {
            $this->db->beginTransaction();

            // Get booking details
            $query = "
                SELECT 
                    b.BookingID,
                    b.RoomID,
                    u.Name AS GuestName,
                    b.Contact,
                    b.Email,
                    bs.StatusName
                FROM bookings b
                LEFT JOIN useraccounts u ON b.UserID = u.UserID
                LEFT JOIN booking_status bs ON b.StatusID = bs.StatusID
                WHERE b.BookingID = ? AND b.IsDeleted = 0
                FOR UPDATE
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                throw new Exception("Booking not found");
            }

            // Check if already checked in
            $checkQuery = "SELECT GuestID FROM guests WHERE BookingID = ?";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute([$bookingId]);
            
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("Already checked in");
            }

            // Insert into guests table
            $insertGuestQuery = "
                INSERT INTO guests (BookingID, Name, Contact, Email, IsMainGuest) 
                VALUES (?, ?, ?, ?, 1)
            ";
            $stmt = $this->db->prepare($insertGuestQuery);
            $stmt->execute([
                $bookingId,
                $booking['GuestName'] ?? 'Guest',
                $booking['Contact'] ?? '',
                $booking['Email'] ?? ''
            ]);

            // Update room status to occupied
            $updateRoomQuery = "UPDATE rooms SET Status = 'occupied' WHERE RoomID = ?";
            $stmt = $this->db->prepare($updateRoomQuery);
            $stmt->execute([$booking['RoomID']]);

            // Update booking status to checked-in
            $statusQuery = "SELECT StatusID FROM booking_status WHERE LOWER(StatusName) = 'checked-in'";
            $stmt = $this->db->prepare($statusQuery);
            $stmt->execute();
            $statusResult = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($statusResult) {
                $updateBookingQuery = "UPDATE bookings SET StatusID = ? WHERE BookingID = ?";
                $stmt = $this->db->prepare($updateBookingQuery);
                $stmt->execute([$statusResult['StatusID'], $bookingId]);
            }

            $this->db->commit();
            error_log("✅ Check-in completed for Booking #{$bookingId}");

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&success=checked_in");
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("❌ Check-in error: " . $e->getMessage());
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations&error=checkin_failed");
            exit();
        }
    }

    /**
     * Current Guests - Guests who are currently checked in
     */
    public function currentGuests()
    {
        // Statistics
        $stats = [
            'total_current_guests' => $this->getValue("SELECT COUNT(*) FROM guests WHERE BookingID IS NOT NULL"),
            'occupied_rooms' => $this->getValue("SELECT COUNT(DISTINCT RoomID) FROM bookings b JOIN guests g ON b.BookingID = g.BookingID WHERE b.IsDeleted = 0"),
            'checkouts_today' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings b
                JOIN guests g ON b.BookingID = g.BookingID
                WHERE DATE(b.CheckOut) = CURDATE()
                AND b.IsDeleted = 0
            ")
        ];

        // Pagination
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalGuests = $this->getValue("SELECT COUNT(*) FROM guests WHERE BookingID IS NOT NULL");
        $totalPages = ceil($totalGuests / $limit);

        // Get current guests
        $query = "
            SELECT 
                g.GuestID,
                g.BookingID,
                u.Name AS GuestName,
                COALESCE(NULLIF(b.Contact, ''), g.Contact, 'N/A') AS Contact,
                u.Email,
                b.CheckIn,
                b.CheckOut,
                b.RoomID,
                rt.Name AS RoomType,
                r.RoomNumber
            FROM guests g
            LEFT JOIN bookings b ON g.BookingID = b.BookingID
            LEFT JOIN useraccounts u ON b.UserID = u.UserID
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            LEFT JOIN roomtypes rt ON r.TypeID = rt.TypeID
            WHERE g.BookingID IS NOT NULL AND b.IsDeleted = 0
            ORDER BY b.CheckIn DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        $currentGuests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/guest/current_guest.php';
    }

    /**
     * Checkout a guest (move to guest history)
     */
    public function checkout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=currentGuests&error=invalid_method");
            exit();
        }

        $guestId = intval($_POST['guest_id'] ?? 0);
        $bookingId = intval($_POST['booking_id'] ?? 0);
        $roomId = intval($_POST['room_id'] ?? 0);

        if (!$guestId || !$bookingId) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=currentGuests&error=invalid_data");
            exit();
        }

        try {
            $this->db->beginTransaction();

            // Get guest and booking details
            $query = "
                SELECT 
                    g.GuestID,
                    u.Name AS GuestName,
                    g.Contact,
                    u.Email,
                    b.CheckIn,
                    b.CheckOut,
                    b.Street,
                    b.Barangay,
                    b.City,
                    b.Province,
                    b.PostalCode,
                    rt.Name AS RoomType,
                    r.RoomNumber
                FROM guests g
                LEFT JOIN bookings b ON g.BookingID = b.BookingID
                LEFT JOIN useraccounts u ON b.UserID = u.UserID
                LEFT JOIN rooms r ON b.RoomID = r.RoomID
                LEFT JOIN roomtypes rt ON r.TypeID = rt.TypeID
                WHERE g.GuestID = ?
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$guestId]);
            $guestData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($guestData) {
                // Calculate total amount
                $checkinTimestamp = strtotime($guestData['CheckIn']);
                $checkoutTimestamp = strtotime($guestData['CheckOut']);
                $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
                $nights = max(1, $nights);

                // Get room price
                $priceQuery = "SELECT Price FROM roomtypes rt 
                              JOIN rooms r ON rt.TypeID = r.TypeID 
                              WHERE r.RoomID = ?";
                $stmt = $this->db->prepare($priceQuery);
                $stmt->execute([$roomId]);
                $priceData = $stmt->fetch(PDO::FETCH_ASSOC);
                $roomPrice = $priceData['Price'] ?? 0;
                $totalAmount = $roomPrice * $nights;

                // Get payment status
                $paymentQuery = "SELECT Status FROM payments WHERE BookingID = ?";
                $stmt = $this->db->prepare($paymentQuery);
                $stmt->execute([$bookingId]);
                $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);
                $paymentStatus = $paymentData['Status'] ?? 'pending';

                // Insert into guest_history
                $insertHistoryQuery = "
                    INSERT INTO guest_history 
                    (BookingID, GuestID, Name, Email, Contact, RoomType, RoomNumber, 
                     Street, Barangay, City, Province, PostalCode, CheckedInAt, CheckedOutAt, 
                     TotalAmount, PaymentStatus)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
                ";
                $stmt = $this->db->prepare($insertHistoryQuery);
                $stmt->execute([
                    $bookingId,
                    $guestId,
                    $guestData['GuestName'],
                    $guestData['Email'],
                    $guestData['Contact'] ?? '',
                    $guestData['RoomType'],
                    $guestData['RoomNumber'],
                    $guestData['Street'] ?? '',
                    $guestData['Barangay'] ?? '',
                    $guestData['City'] ?? '',
                    $guestData['Province'] ?? '',
                    $guestData['PostalCode'] ?? '',
                    $guestData['CheckIn'],
                    $totalAmount,
                    $paymentStatus
                ]);
            }

            // Delete guest from current guests
            $deleteGuestQuery = "DELETE FROM guests WHERE GuestID = ?";
            $stmt = $this->db->prepare($deleteGuestQuery);
            $stmt->execute([$guestId]);

            // Update room status to available
            if ($roomId) {
                $updateRoomQuery = "UPDATE rooms SET Status = 'available' WHERE RoomID = ?";
                $stmt = $this->db->prepare($updateRoomQuery);
                $stmt->execute([$roomId]);
            }

            // Update booking status to checked-out
            $statusQuery = "SELECT StatusID FROM booking_status WHERE LOWER(StatusName) = 'checked-out'";
            $stmt = $this->db->prepare($statusQuery);
            $stmt->execute();
            $statusResult = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($statusResult) {
                $updateBookingQuery = "UPDATE bookings SET StatusID = ? WHERE BookingID = ?";
                $stmt = $this->db->prepare($updateBookingQuery);
                $stmt->execute([$statusResult['StatusID'], $bookingId]);
            }

            $this->db->commit();
            error_log("✅ Checkout completed for Guest #{$guestId}, Booking #{$bookingId}");

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=currentGuests&success=checked_out");
            exit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("❌ Checkout error: " . $e->getMessage());
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=guest&action=currentGuests&error=checkout_failed");
            exit();
        }
    }

    /**
     * Guest History - Past guests and loyalty information
     */
    public function history()
    {
        // Statistics
        $stats = [
            'total_history' => $this->getValue("SELECT COUNT(*) FROM guest_history"),
            'this_month' => $this->getValue("
                SELECT COUNT(*) 
                FROM guest_history 
                WHERE MONTH(CheckedOutAt) = MONTH(CURDATE()) 
                AND YEAR(CheckedOutAt) = YEAR(CURDATE())
            "),
            'this_week' => $this->getValue("
                SELECT COUNT(*) 
                FROM guest_history 
                WHERE YEARWEEK(CheckedOutAt, 1) = YEARWEEK(CURDATE(), 1)
            ")
        ];

        // Pagination
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalHistory = $this->getValue("SELECT COUNT(*) FROM guest_history");
        $totalPages = ceil($totalHistory / $limit);

        // Get guest history with past visits count
        $query = "
            SELECT 
                gh.HistoryID,
                gh.BookingID,
                u.Name AS Name,
                COALESCE(NULLIF(gh.Email, ''), u.Email, 'N/A') AS Email,
                gh.Contact,
                gh.RoomType,
                gh.RoomNumber,
                gh.Street,
                gh.Barangay,
                gh.City,
                gh.Province,
                gh.PostalCode,
                gh.CheckedInAt,
                gh.CheckedOutAt,
                gh.TotalAmount,
                gh.PaymentStatus,
                (
                    SELECT COUNT(*) 
                    FROM guest_history gh2 
                    LEFT JOIN bookings b2 ON gh2.BookingID = b2.BookingID
                    WHERE b2.UserID = b.UserID 
                    AND gh2.CheckedOutAt <= gh.CheckedOutAt
                ) AS PastVisits
            FROM guest_history gh
            LEFT JOIN bookings b ON gh.BookingID = b.BookingID
            LEFT JOIN useraccounts u ON b.UserID = u.UserID
            ORDER BY gh.CheckedOutAt DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        $guestHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/guest/guest_history.php';
    }

    /**
     * Helper: Get single value from query
     */
    private function getValue($sql)
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn() ?: 0;
    }
}