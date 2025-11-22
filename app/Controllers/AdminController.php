<?php

namespace App\Controllers;

use PDO;
use App\Config\Database;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Helpers\Mailer;

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

        // Check session timeout (30 minutes = 1800 seconds) - UNIFIED
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=session_expired");
            exit;
        }

        $_SESSION['last_activity'] = time();

        // Authorization: Only admin
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
        // Get payment statistics
        $paymentStats = $this->paymentModel->getPaymentStats();

        // Stats Section using new structure
        $stats = [
            'total_revenue' => $paymentStats['total_revenue'] ?? 0,
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
            'pending_payments' => $paymentStats['pending_amount'] ?? 0
        ];

        // Pagination setup
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalBookings = $this->getValue("SELECT COUNT(*) FROM bookings");
        $totalPages = ceil($totalBookings / $limit);

        // Get bookings with new structure
        $bookings = $this->bookingModel->getAllBookings($limit, $offset);

        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    // Confirm Booking
    // Confirm Booking
    public function confirm()
    {
        if (!isset($_GET['id'])) die("Invalid request");
        $id = intval($_GET['id']);

        // Fetch booking with new structure
        $booking = $this->bookingModel->getBookingById($id);

        if (!$booking) die("Booking not found.");

        // Check if already confirmed
        if (strtolower($booking['booking_status']) === 'confirmed') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&error=already_confirmed");
            exit();
        }

        // Confirm booking using status name
        $this->bookingModel->updateStatusByName($id, 'confirmed');

        // Update room status
        $this->roomModel->updateAvailability($booking['RoomID'], 'booked');

        // Update payment status if exists
        if (isset($booking['PaymentID'])) {
            $this->paymentModel->updateStatus($booking['PaymentID'], 'completed');
        }

        // Send email confirmation
        error_log("📧 Attempting to send confirmation email for Booking ID: {$id}");
        error_log("📧 Email will be sent to: " . $booking['Email']);

        $emailSent = Mailer::sendBookingConfirmation(
            $booking['Email'],
            $booking['user_name'],
            $booking
        );

        if ($emailSent) {
            error_log("✅ Email sent successfully for Booking ID: {$id}");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&success=confirmed");
        } else {
            error_log("⚠️ Booking confirmed but email failed for Booking ID: {$id}");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&success=confirmed&warning=email_failed");
        }
        exit();
    }

    public function delete()
    {
        if (!isset($_GET['id'])) die("Invalid request");
        $id = intval($_GET['id']);

        // Get booking details before deletion
        $booking = $this->bookingModel->getBookingById($id);

        if ($booking) {
            // Update room status back to available
            $this->roomModel->updateAvailability($booking['RoomID'], 'available');

            // Delete associated payment if exists
            if (isset($booking['PaymentID'])) {
                $this->paymentModel->delete($booking['PaymentID']);
            }
        }

        // Delete booking
        $this->bookingModel->deleteBooking($id);

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&success=deleted");
        exit();
    }

    // View all payments
    public function payments()
    {
        $limit = 10;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $payments = $this->paymentModel->getAllPayments($limit, $offset);
        $paymentStats = $this->paymentModel->getPaymentStats();

        include __DIR__ . '/../Views/admin/payments.php';
    }

    // Helper function to get single value
    private function getValue($sql)
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn() ?: 0;
    }
}
