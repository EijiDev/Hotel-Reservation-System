<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Payment;
use App\Models\Guest;
use PDO;

class BookingController
{
    private $bookingModel;
    private $roomModel;
    private $paymentModel;
    private $guestModel;
    private $db;

    public function __construct(PDO $db)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['regenerated'] = true;
        }

        $this->db = $db;
        $this->bookingModel = new Booking($db);
        $this->roomModel = new Room($db);
        $this->paymentModel = new Payment($db);
        $this->guestModel = new Guest($db);

        // Restrict access to logged-in users
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
            exit();
        }
    }

    // Show booking form for new bookings
    public function show($roomId = null)
    {
        $roomId = $roomId ?? $_GET['room_id'] ?? null;

        if (!$roomId) {
            http_response_code(400);
            exit("No room selected.");
        }

        $room = $this->roomModel->getRoomById($roomId);
        if (!$room) {
            http_response_code(404);
            exit("Room not found.");
        }

        include __DIR__ . '/../Views/roombookings.php';
    }

    // Edit existing booking
    public function edit($bookingId = null)
    {
        $bookingId = $bookingId ?? $_GET['id'] ?? null;

        if (!$bookingId) {
            http_response_code(400);
            exit("No booking ID provided.");
        }

        $editingBooking = $this->bookingModel->getBookingById($bookingId);
        if (!$editingBooking) {
            http_response_code(404);
            exit("Booking not found.");
        }

        // Ownership check
        if ($editingBooking['UserID'] != $_SESSION['user_id']) {
            $role = $_SESSION['role'] ?? 'user';
            if (!in_array($role, ['staff', 'admin'])) {
                http_response_code(403);
                exit("Unauthorized access.");
            }
        }

        include __DIR__ . '/../Views/editbooking.php';
    }

    // Store new booking or update existing
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // Get form inputs
        $bookingId      = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
        $checkin        = $_POST['checkin'] ?? null;
        $checkout       = $_POST['checkout'] ?? null;
        $guests         = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
        $checkin_time   = $_POST['checkin_time'] ?? null;
        $contact        = $_POST['contact'] ?? null;
        $email          = $_POST['email'] ?? null;
        $payment_method = $_POST['payment_method'] ?? null;
        $room_id        = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
        $user_id        = $_SESSION['user_id'];

        // Validate required fields
        if (!$room_id || !$checkin || !$checkout || !$payment_method) {
            http_response_code(400);
            exit("Missing required fields.");
        }

        $room = $this->roomModel->getRoomById($room_id);
        if (!$room) {
            http_response_code(404);
            exit("Room not found.");
        }

        // Calculate total amount
        $checkinTimestamp = strtotime($checkin);
        $checkoutTimestamp = strtotime($checkout);
        $nights = max(1, (int)ceil(($checkoutTimestamp - $checkinTimestamp) / 86400));

        $roomTotal = $room['room_price'] * $nights;
        $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;

        $extraNightFee = 0;
        if ($checkin_time && (int)explode(':', $checkin_time)[0] >= 18) {
            $extraNightFee = 500;
        }

        $total = $roomTotal + $guestFee + $extraNightFee;

        // Debug logging
        error_log("=== BOOKING CALCULATION ===");
        error_log("Check-in: {$checkin}, Check-out: {$checkout}");
        error_log("Nights: {$nights}");
        error_log("Room Price: {$room['room_price']}");
        error_log("Room Total: {$roomTotal}");
        error_log("Guests: {$guests}, Guest Fee: {$guestFee}");
        error_log("Check-in Time: {$checkin_time}, Extra Night Fee: {$extraNightFee}");
        error_log("TOTAL: {$total}");

        // UPDATE existing booking
        if ($bookingId) {
            $existing = $this->bookingModel->getBookingById($bookingId);
            if (!$existing) {
                http_response_code(404);
                exit("Booking not found.");
            }

            // Authorization check
            if ($existing['UserID'] != $user_id) {
                $role = $_SESSION['role'] ?? 'user';
                if (!in_array($role, ['staff', 'admin'])) {
                    http_response_code(403);
                    exit("Unauthorized to update this booking.");
                }
            }

            $this->bookingModel->updateBooking(
                $bookingId,
                $checkin,
                $checkout,
                $guests,
                $checkin_time,
                $contact,
                $email
            );

            // Update payment amount
            if (isset($existing['PaymentID'])) {
                $this->paymentModel->update($existing['PaymentID'], [
                    'Method' => $payment_method,
                    'Amount' => $total
                ]);
            }

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&success=updated");
            exit();
        }

        // CREATE new booking
        try {
            $this->db->beginTransaction();

            // Create booking
            $newBookingId = $this->bookingModel->create(
                $checkin,
                $checkout,
                $guests,
                $checkin_time,
                $contact,
                $email,
                $room_id,
                $user_id
            );

            if (!$newBookingId) {
                throw new \Exception("Failed to create booking");
            }

            // Create payment record with calculated total
            $paymentId = $this->paymentModel->create(
                $newBookingId,
                $payment_method,
                $total,
                'pending'
            );

            if (!$paymentId) {
                throw new \Exception("Failed to create payment record");
            }

            // Create guest record
            $this->guestModel->create($newBookingId, $contact, $contact, $email);

            // Update room status
            $this->roomModel->updateAvailability($room_id, 'booked');

            $this->db->commit();

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&success=booking_created");
            exit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Booking creation failed: " . $e->getMessage());
            http_response_code(500);
            exit("Could not create booking. Try again later.");
        }
    }

    // View user bookings
    public function userBookings()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=session_expired");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $bookings = $this->bookingModel->getBookingsByUser($userId);

        include __DIR__ . '/../Views/userbookings.php';
    }

    // Cancel booking
    public function cancel($bookingId = null)
    {
        $bookingId = $bookingId ?? $_GET['id'] ?? null;

        error_log("🔴 CANCEL CALLED - Booking ID: {$bookingId}");

        if (!$bookingId) {
            error_log("❌ No booking ID");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings");
            exit();
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        error_log("📋 Booking found: " . print_r($booking, true));

        if (!$booking) {
            error_log("❌ Booking not found");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings");
            exit();
        }

        // Update to cancelled
        $result = $this->bookingModel->updateStatusByName($bookingId, 'cancelled');
        error_log("📝 Update result: " . ($result ? "SUCCESS" : "FAILED"));

        $this->roomModel->updateAvailability($booking['RoomID'], 'available');

        if (isset($booking['PaymentID'])) {
            $this->paymentModel->updateStatus($booking['PaymentID'], 'refunded');
        }

        error_log("✅ Cancel complete, redirecting...");
        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings");
        exit();
    }
}
