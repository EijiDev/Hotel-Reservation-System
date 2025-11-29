<?php

namespace App\Controllers;

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

        // Check session timeout (30 minutes)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=session_expired");
            exit;
        }

        $_SESSION['last_activity'] = time();

        // Authorization: Admin only
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
        $paymentStats = $this->paymentModel->getPaymentStats();

        // Dashboard statistics
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
            'available_rooms' => $this->getValue("SELECT COUNT(*) FROM rooms WHERE Status = 'available'")
        ];

        // Pagination
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalBookings = $this->getValue("SELECT COUNT(*) FROM bookings");
        $totalPages = ceil($totalBookings / $limit);

        $bookings = $this->bookingModel->getAllBookings($limit, $offset);

        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    // Confirm booking
    public function confirm()
    {
        if (!isset($_GET['id'])) die("Invalid request");
        $id = intval($_GET['id']);

        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking) die("Booking not found.");

        // Check if already confirmed
        if (strtolower($booking['booking_status']) === 'confirmed') {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&error=already_confirmed");
            exit();
        }

        // Update booking and room status
        $this->bookingModel->updateStatusByName($id, 'confirmed');
        $this->roomModel->updateAvailability($booking['RoomID'], 'booked');

        // Update payment status if exists
        if (isset($booking['PaymentID'])) {
            $this->paymentModel->updateStatus($booking['PaymentID'], 'completed');
        }

        // Send confirmation email
        $emailSent = Mailer::sendBookingConfirmation(
            $booking['Email'],
            $booking['user_name'],
            $booking
        );

        $successParam = $emailSent ? 'success=confirmed' : 'success=confirmed&warning=email_failed';
        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=admin&action=index&{$successParam}");
        exit();
    }

    // Delete booking
    public function delete()
    {
        if (!isset($_GET['id'])) die("Invalid request");
        $id = intval($_GET['id']);

        $booking = $this->bookingModel->getBookingById($id);

        if ($booking) {
            // Release room and delete payment
            $this->roomModel->updateAvailability($booking['RoomID'], 'available');
            if (isset($booking['PaymentID'])) {
                $this->paymentModel->delete($booking['PaymentID']);
            }
        }

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

    // Add this method to AdminController.php

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
        $limit = 10;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        $totalArchived = $this->getValue("SELECT COUNT(*) FROM bookings WHERE IsDeleted = 1");
        $totalPages = ceil($totalArchived / $limit);

        // Get archived bookings
        $archivedBookings = $this->bookingModel->getAllBookings($limit, $offset, true);

        include __DIR__ . '/../Views/admin/history.php';
    }

    // Helper: Get single value from query
    private function getValue($sql)
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn() ?: 0;
    }
}
