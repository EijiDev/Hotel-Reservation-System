<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Helpers\Mailer;
use Exception;
use PDO;

class AdminController
{
    private $db;
    private $bookingModel;
    private $paymentModel;
    private $roomModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check session timeout (30 minutes)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=session_expired");
            exit;
        }

        $_SESSION['last_activity'] = time();

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo "<p style='text-align:center; color:red; font-family:sans-serif;'>🚫 You do not have authorization to access this page.</p>";
            header("refresh:2;url=/Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
            exit;
        }

        $this->db = (new Database())->connect();
        $this->bookingModel = new Booking($this->db);
        $this->paymentModel = new Payment($this->db);
        $this->roomModel = new Room($this->db);
    }

    // Dashboard index
    public function index()
    {
        try {
            $revenueQuery = "
            SELECT 
                SUM(
                    (rt.Price * GREATEST(1, DATEDIFF(b.CheckOut, b.CheckIn))) + 
                    (CASE WHEN b.Guests > 1 THEN (b.Guests - 1) * 300 ELSE 0 END) +
                    (CASE WHEN TIME(b.CheckIn_Time) >= '18:00:00' THEN 500 ELSE 0 END)
                ) AS total_revenue
            FROM bookings b
            JOIN rooms r ON b.RoomID = r.RoomID
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE bs.StatusName IN ('confirmed', 'checked-in', 'checked-out')
            AND b.IsDeleted = 0
        ";

            $stmt = $this->db->prepare($revenueQuery);
            $stmt->execute();
            $revenueResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalRevenue = $revenueResult['total_revenue'] ?? 0;

            // Dashboard statistics
            $stats = [
                'total_revenue' => $totalRevenue,
                'total_bookings' => $this->getValue("SELECT COUNT(*) FROM bookings WHERE IsDeleted = 0"),
                'upcoming_checkins' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings b
                JOIN booking_status bs ON b.StatusID = bs.StatusID
                WHERE b.CheckIn >= CURDATE() 
                AND b.CheckIn <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND bs.StatusName IN ('confirmed', 'pending')
                AND b.IsDeleted = 0
            "),
                'available_rooms' => $this->getValue("SELECT COUNT(*) FROM rooms WHERE Status = 'available'")
            ];

            // ✅ FIXED: Validation on pagination
            $limit = 5;
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $offset = ($page - 1) * $limit;

            $totalBookings = $this->getValue("SELECT COUNT(*) FROM bookings WHERE IsDeleted = 0");
            $totalPages = max(1, ceil($totalBookings / $limit));

            $bookings = $this->bookingModel->getAllBookings($limit, $offset);

            include __DIR__ . '/../Views/admin/dashboard.php';
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            echo "<p style='color:red;'>Failed to load dashboard. Please try again.</p>";
            return;
        }
    }

    public function confirm()
    {
        if (!isset($_GET['id'])) die("Invalid request");
        $id = intval($_GET['id']);

        error_log("=== CONFIRM BOOKING #{$id} ===");

        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking) die("Booking not found.");

        // Check if already confirmed
        if (strtolower($booking['booking_status']) === 'confirmed') {
            error_log("Already confirmed, redirecting");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&error=already_confirmed");
            exit();
        }

        try {
            $this->db->beginTransaction();
            error_log("Transaction started");

            // CRITICAL: Check if guest entry already exists and DELETE it
            $checkGuestQuery = "SELECT GuestID FROM guests WHERE BookingID = ?";
            $stmt = $this->db->prepare($checkGuestQuery);
            $stmt->execute([$id]);
            $existingGuest = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingGuest) {
                error_log("⚠️ WARNING: Guest entry already exists (GuestID: {$existingGuest['GuestID']})! DELETING IT.");
                $deleteGuestQuery = "DELETE FROM guests WHERE BookingID = ?";
                $stmt = $this->db->prepare($deleteGuestQuery);
                $stmt->execute([$id]);
                error_log("✅ Guest entry deleted");
            } else {
                error_log("✅ No guest entry found (correct for Confirm action)");
            }

            // Update status to confirmed
            error_log("Updating status to confirmed");
            $this->bookingModel->updateStatusByName($id, 'confirmed');

            // Update room status to booked
            error_log("Updating room status to booked");
            $this->roomModel->updateAvailability($booking['RoomID'], 'booked');

            // Update payment status if exists (but keep cash payments as pending)
            if (isset($booking['PaymentID'])) {
                $paymentMethodQuery = "SELECT Method FROM payments WHERE PaymentID = ?";
                $stmt = $this->db->prepare($paymentMethodQuery);
                $stmt->execute([$booking['PaymentID']]);
                $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($paymentData) {
                    $paymentMethod = strtolower(trim($paymentData['Method']));

                    if ($paymentMethod === 'cash') {
                        error_log("💵 Payment is Cash - keeping status as PENDING");
                    } else {
                        $this->paymentModel->updateStatus($booking['PaymentID'], 'completed');
                        error_log("✅ Payment status updated to COMPLETED (Method: {$paymentData['Method']})");
                    }
                }
            }
            $this->db->commit();
            error_log("✅ Transaction committed - Booking confirmed WITHOUT guest entry");

            // Prepare booking details for email
            $bookingDetails = [
                'CheckIn' => $booking['CheckIn'] ?? '',
                'CheckOut' => $booking['CheckOut'] ?? '',
                'Guests' => $booking['Guests'] ?? 1,
                'room_price' => $booking['room_price'] ?? $booking['Price'] ?? $booking['RoomPrice'] ?? 0,
                'CheckIn_Time' => $booking['CheckIn_Time'] ?? '14:00',
                'room_name' => $booking['RoomType'] ?? $booking['room_name'] ?? 'N/A',
                'payment_method' => $booking['payment_method'] ?? 'Cash'
            ];

            // Send confirmation email
            $emailSent = Mailer::sendBookingConfirmation(
                $booking['Email'],
                $booking['user_name'] ?? $booking['GuestName'] ?? 'Guest',
                $bookingDetails
            );

            error_log("Email sent: " . ($emailSent ? 'YES' : 'NO'));

            // Redirect back to Bookings dashboard
            $successParam = $emailSent ? 'success=confirmed' : 'success=confirmed&warning=email_failed';
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&{$successParam}");
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("❌ Confirm error: " . $e->getMessage());
            error_log($e->getTraceAsString());
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&error=confirm_failed");
            exit();
        }
    }

    // Archive booking (soft delete - moves to history without affecting user view or room status)
    public function delete()
    {
        if (!isset($_GET['id'])) die("Invalid request");
        $id = intval($_GET['id']);

        $booking = $this->bookingModel->getBookingById($id);

        if ($booking) {
            // ONLY soft delete the booking (set IsDeleted = 1)
            // DO NOT release room or delete payment
            // Users can still see their booking, it just moves to admin history
            $this->bookingModel->deleteBooking($id);

            error_log("📦 Booking {$id} archived to history (visible to user, hidden from admin dashboard)");
        }

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&success=archived");
        exit();
    }

    // View all payments
    public function payments()
    {
        try {
            $limit = 10;
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $offset = ($page - 1) * $limit;

            $totalPayments = $this->getValue("SELECT COUNT(*) FROM payments");
            $totalPages = max(1, ceil($totalPayments / $limit));

            // ✅ FIXED: Ensure page doesn't exceed total pages
            if ($page > $totalPages && $totalPages > 0) {
                $page = $totalPages;
                $offset = ($page - 1) * $limit;
            }

            $payments = $this->paymentModel->getAllPayments($limit, $offset);
            $paymentStats = $this->paymentModel->getPaymentStats();

            include __DIR__ . '/../Views/admin/payments.php';
        } catch (Exception $e) {
            error_log("Payments page error: " . $e->getMessage());
            echo "<p style='color:red;'>Failed to load payments. Please try again.</p>";
        }
    }

    // Restore booking from history
    public function restore()
    {
        if (!isset($_GET['id'])) die("Invalid request");
        $id = intval($_GET['id']);

        $booking = $this->bookingModel->getBookingById($id, true); // Get even if deleted

        if ($booking) {
            // Restore booking
            $this->bookingModel->restore($id);

            // Update room status back to booked if booking was confirmed
            if (strtolower($booking['booking_status']) === 'confirmed') {
                $this->roomModel->updateAvailability($booking['RoomID'], 'booked');
            }

            error_log("✅ Booking {$id} restored from history");
        }

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=history&success=restored");
        exit();
    }

    // View booking history
    public function history()
    {
        // Statistics for archived bookings
        $stats = [
            'total_archived' => $this->getValue("SELECT COUNT(*) FROM bookings WHERE IsDeleted = 1"),
            'cancelled_count' => $this->getValue("
            SELECT COUNT(*) 
            FROM bookings b
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE b.IsDeleted = 1 AND bs.StatusName = 'cancelled'
        "),
            'completed_count' => $this->getValue("
            SELECT COUNT(*) 
            FROM bookings b
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE b.IsDeleted = 1 AND bs.StatusName = 'checked-out'
        ")
        ];

        // Pagination
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalArchived = $this->getValue("SELECT COUNT(*) FROM bookings WHERE IsDeleted = 1");
        $totalPages = ceil($totalArchived / $limit);

        // Get archived bookings
        $archivedBookings = $this->bookingModel->getAllBookings($limit, $offset, true);

        include __DIR__ . '/../Views/admin/history.php';
    }


    public function reservations()
    {
        // Add debugging
        error_log("=== RESERVATIONS PAGE LOADED ===");

        // Statistics for reservations - bookings without guest entries
        $stats = [
            'total_reservations' => $this->getValue("
            SELECT COUNT(*) 
            FROM bookings b 
            LEFT JOIN guests g ON b.BookingID = g.BookingID 
            LEFT JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE b.IsDeleted = 0 
            AND g.GuestID IS NULL 
            AND LOWER(bs.StatusName) NOT IN ('checked-out', 'cancelled')
        "),
            'pending_bookings' => $this->getValue("
            SELECT COUNT(*) 
            FROM bookings b
            LEFT JOIN guests g ON b.BookingID = g.BookingID
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE LOWER(bs.StatusName) = 'pending' 
            AND b.IsDeleted = 0
            AND g.GuestID IS NULL
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

        error_log("Total reservations: " . $stats['total_reservations']);

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
        AND LOWER(bs.StatusName) NOT IN ('checked-out', 'cancelled')
    ");
        $totalPages = ceil($totalReservations / $limit);

        // Get all reservations (bookings WITHOUT guests table entry)
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
        AND LOWER(bs.StatusName) NOT IN ('checked-out', 'cancelled')
        ORDER BY b.Created_At DESC
        LIMIT ? OFFSET ?
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Reservations found: " . count($reservations));
        foreach ($reservations as $r) {
            error_log("Booking ID: {$r['BookingID']}, Status: {$r['StatusName']}, GuestID: " . ($r['GuestID'] ?? 'NULL'));
        }

        include __DIR__ . '/../Views/admin/reservation.php';
    }

    // Update reservation
    public function updateReservation()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=invalid_request");
            exit();
        }

        $bookingId = intval($_POST['booking_id'] ?? 0);
        $guestName = $_POST['guest_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $contact = $_POST['contact'] ?? '';
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
        $paymentStatus = $_POST['payment_status'] ?? ''; // ✅ NEW

        if (!$bookingId) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=invalid_booking");
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
                Contact = ?,
                Email = ?,
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
                $contact,
                $email,
                $street,
                $barangay,
                $city,
                $province,
                $postalCode,
                $statusId,
                $bookingId
            ]);

            if ($paymentStatus) {
                $paymentUpdateQuery = "UPDATE payments SET Status = ? WHERE BookingID = ?";
                $stmt = $this->db->prepare($paymentUpdateQuery);
                $stmt->execute([$paymentStatus, $bookingId]);
                error_log("✅ Payment status updated to '{$paymentStatus}' for Booking #{$bookingId}");
            }

            // Update guest information if exists
            $guestQuery = "UPDATE guests SET Contact = ?, Email = ? WHERE BookingID = ?";
            $stmt = $this->db->prepare($guestQuery);
            $stmt->execute([$contact, $email, $bookingId]);

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&success=updated");
            exit();
        } catch (\Exception $e) {
            error_log("Update reservation error: " . $e->getMessage());
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=update_failed");
            exit();
        }
    }

    public function checkinReservation()
    {
        error_log("=== CHECK-IN RESERVATION ===");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("❌ Not a POST request");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=invalid_method");
            exit();
        }

        $bookingId = intval($_POST['booking_id'] ?? 0);
        error_log("Booking ID: $bookingId");

        if (!$bookingId) {
            error_log("❌ Invalid booking ID");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=invalid_booking");
            exit();
        }

        try {
            $this->db->beginTransaction();
            error_log("Transaction started");

            // Get booking details
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
                u.Name AS GuestName,
                rt.Name AS RoomType,
                r.RoomNumber,
                b.RoomID,
                bs.StatusName
            FROM bookings b
            LEFT JOIN useraccounts u ON b.UserID = u.UserID
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            LEFT JOIN roomtypes rt ON r.TypeID = rt.TypeID
            LEFT JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE b.BookingID = ? AND b.IsDeleted = 0
            FOR UPDATE
        ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                error_log("❌ Booking not found");
                $this->db->rollBack();
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=booking_not_found");
                exit();
            }

            error_log("✅ Booking found: {$booking['GuestName']}, Status: {$booking['StatusName']}");

            // Check if already checked in (has guest entry)
            $checkQuery = "SELECT GuestID FROM guests WHERE BookingID = ?";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute([$bookingId]);
            $existingGuest = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingGuest) {
                error_log("❌ Already checked in (GuestID: {$existingGuest['GuestID']})");
                $this->db->rollBack();
                header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=already_checked_in");
                exit();
            }

            error_log("✅ No existing guest entry, proceeding");

            // Insert into guests table
            $insertGuestQuery = "
            INSERT INTO guests 
            (BookingID, Name, Contact, Email, IsMainGuest) 
            VALUES (?, ?, ?, ?, 1)
        ";

            $stmt = $this->db->prepare($insertGuestQuery);
            $result = $stmt->execute([
                $bookingId,
                $booking['GuestName'] ?? 'Guest',
                $booking['Contact'] ?? '',
                $booking['Email'] ?? ''
            ]);

            if ($result) {
                $guestId = $this->db->lastInsertId();
                error_log("✅ Guest entry created (GuestID: $guestId)");
            } else {
                throw new Exception("Failed to insert guest");
            }

            // Update room status to occupied
            if ($booking['RoomID']) {
                $updateRoomQuery = "UPDATE rooms SET Status = 'occupied' WHERE RoomID = ?";
                $stmt = $this->db->prepare($updateRoomQuery);
                $stmt->execute([$booking['RoomID']]);
                error_log("✅ Room {$booking['RoomID']} set to occupied");
            }

            // Update booking status to checked-in if exists
            $statusQuery = "SELECT StatusID FROM booking_status WHERE LOWER(StatusName) = 'checked-in'";
            $stmt = $this->db->prepare($statusQuery);
            $stmt->execute();
            $statusResult = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($statusResult) {
                $updateBookingQuery = "UPDATE bookings SET StatusID = ? WHERE BookingID = ?";
                $stmt = $this->db->prepare($updateBookingQuery);
                $stmt->execute([$statusResult['StatusID'], $bookingId]);
                error_log("✅ Booking status updated to checked-in");
            } else {
                error_log("⚠️ No 'checked-in' status found, keeping as confirmed");
            }

            $this->db->commit();
            error_log("✅✅✅ Check-in completed successfully!");

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&success=checked_in");
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("❌ Check-in error: " . $e->getMessage());
            error_log($e->getTraceAsString());
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations&error=checkin_failed&msg=" . urlencode($e->getMessage()));
            exit();
        }
    }

    // Current Guests page
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

        // Pagination - Changed to 5
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalGuests = $this->getValue("SELECT COUNT(*) FROM guests WHERE BookingID IS NOT NULL");
        $totalPages = ceil($totalGuests / $limit);

        // Get current guests with booking details
        $query = "
        SELECT 
            g.GuestID,
            g.BookingID,
            g.Name AS GuestName,
            g.Contact,
            g.Email,
            b.CheckIn,
            b.CheckOut,
            b.RoomID,
            rt.Name AS RoomType,
            r.RoomNumber
        FROM guests g
        LEFT JOIN bookings b ON g.BookingID = b.BookingID
        LEFT JOIN rooms r ON b.RoomID = r.RoomID
        LEFT JOIN roomtypes rt ON r.TypeID = rt.TypeID
        WHERE g.BookingID IS NOT NULL
        ORDER BY b.CheckIn DESC
        LIMIT ? OFFSET ?
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        $currentGuests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/current_guest.php';
    }

    // Checkout guest
    public function checkoutGuest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Stay on current guests page
            $this->currentGuests();
            return;
        }

        $guestId = intval($_POST['guest_id'] ?? 0);
        $bookingId = intval($_POST['booking_id'] ?? 0);
        $roomId = intval($_POST['room_id'] ?? 0);

        if (!$guestId || !$bookingId) {
            // Stay on current guests page
            $this->currentGuests();
            return;
        }

        try {
            $this->db->beginTransaction();

            // Get guest and booking details before deleting
            $query = "
            SELECT 
                g.GuestID,
                g.Name AS GuestName,
                g.Contact,
                g.Email,
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
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            LEFT JOIN roomtypes rt ON r.TypeID = rt.TypeID
            WHERE g.GuestID = ?
        ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$guestId]);
            $guestData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($guestData) {
                // Insert into guest_history table with proper column names
                $insertHistoryQuery = "
                INSERT INTO guest_history 
                (BookingID, GuestID, Name, Email, Contact, RoomType, RoomNumber, 
                 Street, Barangay, City, Province, PostalCode, CheckedInAt, CheckedOutAt, 
                 TotalAmount, PaymentStatus)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
            ";

                // Calculate total amount
                $checkinTimestamp = strtotime($guestData['CheckIn']);
                $checkoutTimestamp = strtotime($guestData['CheckOut']);
                $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
                $nights = max(1, $nights);

                // Get room price and calculate total
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

            // Delete guest from current guests table
            $deleteGuestQuery = "DELETE FROM guests WHERE GuestID = ?";
            $stmt = $this->db->prepare($deleteGuestQuery);
            $stmt->execute([$guestId]);

            // Update room status to available
            if ($roomId) {
                $updateRoomQuery = "UPDATE rooms SET Status = 'available' WHERE RoomID = ?";
                $stmt = $this->db->prepare($updateRoomQuery);
                $stmt->execute([$roomId]);
            }

            // Update booking status to checked-out if that status exists
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

            // Stay on current guests page
            $this->currentGuests();
            return;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Checkout error: " . $e->getMessage());
            // Stay on current guests page
            $this->currentGuests();
            return;
        }
    }

    // Guest History page
    public function guestHistory()
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

        // Pagination - Changed to 5
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalHistory = $this->getValue("SELECT COUNT(*) FROM guest_history");
        $totalPages = ceil($totalHistory / $limit);

        // Get guest history
        $query = "
        SELECT 
            HistoryID,
            Name,
            Email,
            Contact,
            RoomType,
            RoomNumber,
            Street,
            Barangay,
            City,
            Province,
            PostalCode,
            CheckedInAt,
            CheckedOutAt,
            TotalAmount,
            PaymentStatus
        FROM guest_history
        ORDER BY CheckedOutAt DESC
        LIMIT ? OFFSET ?
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        $guestHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/guest_history.php';
    }

    // Helper: Get single value from query
    private function getValue($sql)
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn() ?: 0;
    }
}
