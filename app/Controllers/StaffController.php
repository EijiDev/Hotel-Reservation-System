<?php

namespace App\Controllers;

use PDO;
use App\Config\Database;
use App\Helpers\Mailer;

class StaffController
{
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check session timeout (30 minutes = 1800 seconds)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=session_expired");
            exit;
        }

        $_SESSION['last_activity'] = time();

        // Authorization: staff only
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
            echo "<p style='text-align:center; color:red; font-family:sans-serif;'>🚫 You do not have authorization to access this page.</p>";
            header("refresh:2;url=/Hotel_Reservation_System/app/views/login.php?error=unauthorized");
            exit;
        }

        $this->db = (new Database())->connect();
    }
    // Staff Dashboard
    public function index()
    {
        $stats = [
            'total_bookings' => $this->getValue("SELECT COUNT(*) FROM bookings"),
            'upcoming_checkins' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings 
                WHERE CheckIn >= CURDATE() 
                AND CheckIn <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            "),
            'available_rooms' => $this->getValue("SELECT COUNT(*) FROM rooms WHERE status = 'available'")
        ];

        // Pagination
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalBookings = $this->getValue("SELECT COUNT(*) FROM bookings");
        $totalPages = ceil($totalBookings / $limit);

        $sql = "
            SELECT 
                b.BookingID,
                u.Name AS GuestName,
                r.name AS RoomType,
                b.CheckIn,
                b.CheckIn_Time,
                b.CheckOut,
                b.status AS booking_status,
                b.Payment_Method AS PaymentMethod,
                r.price AS TotalAmount
            FROM bookings b
            LEFT JOIN useraccounts u ON b.UserID = u.UserID
            LEFT JOIN rooms r ON b.RoomID = r.RoomID
            ORDER BY b.BookingID DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/staff/staffdashboard.php';
    }

    // Confirm Booking
    public function confirm($bookingId = null)
    {
        $bookingId = $bookingId ?? $_GET['booking_id'] ?? null;
        if (!$bookingId) die("Booking ID is required for confirmation.");

        // Fetch booking info BEFORE updating
        $stmt = $this->db->prepare("
        SELECT 
            b.UserID,
            b.RoomID,
            u.Name AS guest_name, 
            b.Email AS guest_email,
            r.name AS room_name,
            b.CheckIn AS checkin,
            b.CheckOut AS checkout,
            b.Guests AS guests,
            b.CheckIn_Time AS checkin_time,
            b.Payment_Method AS payment_method,
            r.price AS total,
            b.status
        FROM bookings b
        JOIN useraccounts u ON b.UserID = u.UserID
        JOIN rooms r ON b.RoomID = r.RoomID
        WHERE b.BookingID = ?
    ");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            die("Booking not found.");
        }

        // Prevent double-confirmation
        if (strtolower($booking['status']) === 'confirmed') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&error=already_confirmed");
            exit;
        }

        // Update booking status
        $stmt = $this->db->prepare("UPDATE bookings SET status='confirmed' WHERE BookingID=?");
        $stmt->execute([intval($bookingId)]);

        // Update room status to 'Booked'
        $stmt = $this->db->prepare("UPDATE rooms SET status='Booked' WHERE RoomID=?");
        $stmt->execute([$booking['RoomID']]);

        // Send confirmation email
        error_log("📧 Sending email to: " . $booking['guest_email']);

        $emailSent = Mailer::sendBookingConfirmation(
            $booking['guest_email'],
            $booking['guest_name'],
            $booking
        );

        if (!$emailSent) {
            error_log("❌ Failed to send confirmation email for BookingID: {$bookingId}");
        } else {
            error_log("✅ Confirmation email sent for BookingID: {$bookingId}");
        }

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&success=confirmed");
        exit;
    }
    // Delete Booking
    public function delete()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) die("Invalid request");

        $stmt = $this->db->prepare("DELETE FROM bookings WHERE BookingID = ?");
        $stmt->execute([$id]);

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&success=deleted");
        exit;
    }

    // Helper to fetch a single value
    private function getValue($sql)
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn() ?: 0;
    }
}
