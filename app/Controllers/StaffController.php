<?php

namespace App\Controllers;

use PDO;
use App\Config\Database;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Helpers\Mailer;

class StaffController
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

        // Check session timeout (30 minutes = 1800 seconds) - UNIFIED
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
            header("refresh:2;url=/Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
            exit;
        }

        $this->db = (new Database())->connect();
        $this->bookingModel = new Booking($this->db);
        $this->paymentModel = new Payment($this->db);
        $this->roomModel = new Room($this->db);
    }

    // Staff Dashboard
    public function index()
    {
        // Stats using new structure
        $stats = [
            'total_bookings' => $this->getValue("SELECT COUNT(*) FROM bookings"),
            'upcoming_checkins' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings b
                JOIN booking_status bs ON b.StatusID = bs.StatusID
                WHERE b.CheckIn >= CURDATE() 
                AND b.CheckIn <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND bs.StatusName IN ('confirmed', 'pending')
            "),
            'available_rooms' => $this->getValue("SELECT COUNT(*) FROM rooms WHERE Status = 'available'"),
            'pending_bookings' => $this->getValue("
                SELECT COUNT(*) 
                FROM bookings b
                JOIN booking_status bs ON b.StatusID = bs.StatusID
                WHERE bs.StatusName = 'pending'
            ")
        ];

        // Pagination
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalBookings = $this->getValue("SELECT COUNT(*) FROM bookings");
        $totalPages = ceil($totalBookings / $limit);

        // Get bookings with new structure
        $bookings = $this->bookingModel->getAllBookings($limit, $offset);

        include __DIR__ . '/../Views/staff/staffdashboard.php';
    }

    // Confirm Booking
    public function confirm($bookingId = null)
    {
        $bookingId = $bookingId ?? $_GET['booking_id'] ?? null;
        if (!$bookingId) die("Booking ID is required for confirmation.");

        // Fetch booking info with new structure
        $booking = $this->bookingModel->getBookingById($bookingId);

        if (!$booking) die("Booking not found.");

        // Check if already confirmed
        if (strtolower($booking['booking_status']) === 'confirmed') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&error=already_confirmed");
            exit();
        }

        // Update booking status
        $this->bookingModel->updateStatusByName($bookingId, 'confirmed');

        // Update room status
        $this->roomModel->updateAvailability($booking['RoomID'], 'booked');

        // Update payment status if exists
        if (isset($booking['PaymentID'])) {
            $this->paymentModel->updateStatus($booking['PaymentID'], 'completed');
        }

        // Send email confirmation
        error_log("📧 Attempting to send confirmation email for Booking ID: {$bookingId}");
        error_log("📧 Email will be sent to: " . $booking['Email']);

        $emailSent = Mailer::sendBookingConfirmation(
            $booking['Email'],
            $booking['user_name'],
            $booking
        );

        if ($emailSent) {
            error_log("✅ Email sent successfully for Booking ID: {$bookingId}");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&success=confirmed");
        } else {
            error_log("⚠️ Booking confirmed but email failed for Booking ID: {$bookingId}");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&success=confirmed&warning=email_failed");
        }
        exit();
    }

    public function delete()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) die("Invalid request");

        // Get booking details before deletion
        $booking = $this->bookingModel->getBookingById($id);

        if ($booking) {
            // Update room status back to available
            $this->roomModel->updateAvailability($booking['RoomID'], 'available');

            // Delete payment if exists
            if (isset($booking['PaymentID'])) {
                $this->paymentModel->delete($booking['PaymentID']);
            }
        }

        // Delete booking
        $this->bookingModel->deleteBooking($id);

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&success=deleted");
        exit();
    }

    // Check-in a booking
    public function checkin()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) die("Invalid request");

        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking) die("Booking not found");

        // Update status to checked-in
        $this->bookingModel->updateStatusByName($id, 'checked-in');

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&success=checked_in");
        exit();
    }

    // Check-out a booking
    public function checkout()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) die("Invalid request");

        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking) die("Booking not found");

        // Update status to checked-out
        $this->bookingModel->updateStatusByName($id, 'checked-out');

        // Update room back to available
        $this->roomModel->updateAvailability($booking['RoomID'], 'available');

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=staff&action=index&success=checked_out");
        exit();
    }

    // Helper to fetch a single value
    private function getValue($sql)
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn() ?: 0;
    }
}
