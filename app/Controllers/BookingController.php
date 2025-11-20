<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Room;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PDO;

class BookingController
{
    private $bookingModel;
    private $roomModel;

    public function __construct(PDO $db)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();


        if (!isset($_SESSION['regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['regenerated'] = true;
        }

        $this->bookingModel = new Booking($db);
        $this->roomModel = new Room($db);

        // Restrict access to logged-in users
        if (!isset($_SESSION['user_id'])) {
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index");
            exit();
        }
    }

    // Show booking form (for new or editing)
    public function show($roomId = null, $bookingId = null)
    {
        $editingBooking = null;

        if ($bookingId) {
            $editingBooking = $this->bookingModel->getBookingById($bookingId);
            if (!$editingBooking) {
                http_response_code(404);
                exit("Booking not found.");
            }

            // Ownership check: only owner or staff/admin should access this
            if ($editingBooking['UserID'] != $_SESSION['user_id']) {
                $role = $_SESSION['role'] ?? 'user';
                if ($role !== 'staff' && $role !== 'admin') {
                    http_response_code(403);
                    exit("Unauthorized access.");
                }
            }

            // Extract room ID - handle both uppercase and lowercase (PDO fetch mode dependent)
            $roomId = $roomId ?? $editingBooking['RoomID'] ?? $editingBooking['roomid'] ?? null;

            if (!$roomId) {
                error_log("Failed to extract room_id from booking. Available keys: " . implode(', ', array_keys($editingBooking)));
                exit("Could not determine room for this booking.");
            }
        }

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

    // Store a new booking or update existing one
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // Basic input retrieval/sanitization - consider stronger validation
        $bookingId      = isset($_POST['booking_id']) && $_POST['booking_id'] !== '' ? (int)$_POST['booking_id'] : null;
        $checkin        = $_POST['checkin'] ?? null;
        $checkout       = $_POST['checkout'] ?? null;
        $guests         = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;
        $checkin_time   = $_POST['checkin_time'] ?? null;
        $contact        = $_POST['contact'] ?? null;
        $email          = $_POST['email'] ?? null;
        $payment_method = $_POST['payment_method'] ?? null;
        $room_id        = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
        $user_id        = $_SESSION['user_id'];

        if (!$room_id || !$checkin || !$checkout) {
            http_response_code(400);
            exit("Missing required fields.");
        }

        $room = $this->roomModel->getRoomById($room_id);
        if (!$room) {
            http_response_code(404);
            exit("Room not found.");
        }

        // Calculate nights & totals
        $nights = max(1, (int)((strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24)));
        $roomTotal = $room['price'] * $nights;
        $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;
        $total = $roomTotal + $guestFee;

        if ($bookingId) {
            // When updating an existing booking, ensure user owns it (unless staff/admin)
            $existing = $this->bookingModel->getBookingById($bookingId);
            if (!$existing) {
                http_response_code(404);
                exit("Booking not found.");
            }
            if ($existing['UserID'] != $user_id) {
                $role = $_SESSION['role'] ?? 'user';
                if ($role !== 'staff' && $role !== 'admin') {
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
                $email,
                $payment_method,
                $total
            );

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&success=updated");
            exit();
        }

        // Create new booking
        $newBookingId = $this->bookingModel->create(
            $checkin,
            $checkout,
            $guests,
            $checkin_time,
            $contact,
            $email,
            $payment_method,
            $room_id,
            $user_id,
            $total
        );

        if (!$newBookingId) {
            http_response_code(500);
            error_log("Failed to create booking for user {$user_id} room {$room_id}");
            exit("Could not create booking. Try again later.");
        }

        // Normalize status casing in DB (use 'pending' consistently)
        $this->bookingModel->updateStatus($newBookingId, 'pending');
        $this->roomModel->updateAvailability($room_id, 'Booked');

        // --- SEND EMAIL TO GUEST (non-blocking) ---
        try {
            $this->sendBookingEmail($email, $contact, $room['name'], $checkin, $checkout, $guests, $checkin_time, $payment_method, 'Pending');
        } catch (\Exception $e) {
            // Log but do not block user
            error_log("Booking email error: " . $e->getMessage());
        }

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&pending=1");
        exit();
    }

    // Function to send email
    private function sendBookingEmail($email, $contact, $roomName, $checkin, $checkout, $guests, $checkin_time, $payment_method, $status)
    {
        // Keep autoload require only if necessary in your setup
        require_once __DIR__ . '/../../vendor/autoload.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-email@gmail.com'; // replace
            $mail->Password   = 'your-app-password';    // replace with app password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('your-email@gmail.com', 'Hotel Reservation');
            $mail->addAddress($email, $contact);

            $mail->isHTML(true);
            $mail->Subject = "Booking Status: {$status}";
            $mail->Body    = "
                <h3>Thank you for your booking!</h3>
                <p>Your booking for <strong>{$roomName}</strong> from <strong>{$checkin}</strong> to <strong>{$checkout}</strong> is now <strong>{$status}</strong>.</p>
                <p>Total Guests: {$guests}<br>
                Check-in Time: {$checkin_time}<br>
                Payment Method: {$payment_method}</p>
                <p>We will notify you once your booking is confirmed.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Booking email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    // Edit booking
    public function edit($bookingId = null)
    {
        if (!$bookingId) exit("No booking ID provided.");

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) exit("Booking not found.");

        // Ownership check
        if ($booking['UserID'] != $_SESSION['user_id']) {
            $role = $_SESSION['role'] ?? 'user';
            if ($role !== 'staff' && $role !== 'admin') exit("Unauthorized access.");
        }

        // Forward to show() for rendering edit form
        // Ensure we pass correct RoomID
        $roomId = $booking['RoomID'] ?? $booking['room_id'] ?? null;
        $this->show($roomId, $bookingId);
    }


    // View user bookings
    public function userBookings()
    {
        $userId = $_SESSION['user_id'];

        $bookings = $this->bookingModel->getBookingsByUser($userId);

        error_log("Fetching bookings for UserID: " . $userId);
        error_log("Found bookings: " . count($bookings));

        include __DIR__ . '/../Views/userbookings.php';
    }

    // Cancel booking
    public function cancel($bookingId = null)
    {
        if (!$bookingId) {
            http_response_code(400);
            exit("No booking ID provided.");
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) {
            http_response_code(404);
            exit("Booking not found.");
        }

        // Ownership check
        if ($booking['UserID'] != $_SESSION['user_id']) {
            $role = $_SESSION['role'] ?? 'user';
            if ($role !== 'staff' && $role !== 'admin') {
                http_response_code(403);
                exit("Unauthorized");
            }
        }

        $this->bookingModel->updateStatus($bookingId, 'canceled'); // normalized lowercase
        $this->roomModel->updateAvailability($booking['room_id'], 'Available');

        // Send email to guest about cancellation (best-effort)
        try {
            $this->sendBookingEmail($booking['Email'], $booking['GuestName'] ?? $booking['Contact'] ?? '', $booking['RoomType'] ?? '', $booking['CheckIn'], $booking['CheckOut'], $booking['Guests'] ?? '', $booking['CheckIn_Time'] ?? '', $booking['Payment_Method'] ?? '', 'Canceled');
        } catch (\Exception $e) {
            error_log("Cancellation email failed: " . $e->getMessage());
        }

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&success=canceled");
        exit();
    }
}
