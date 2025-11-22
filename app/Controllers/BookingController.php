<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Payment;
use App\Models\Guest;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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

    // Show booking form (for new bookings only)
    public function show($roomId = null)
    {
        if (!$roomId) {
            $roomId = $_GET['room_id'] ?? null;
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

    // Edit booking - routes to editbooking.php
    public function edit($bookingId = null)
    {
        if (!$bookingId) {
            $bookingId = $_GET['id'] ?? null;
        }

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
            if ($role !== 'staff' && $role !== 'admin') {
                http_response_code(403);
                exit("Unauthorized access.");
            }
        }

        // Route to editbooking.php
        include __DIR__ . '/../Views/editbooking.php';
    }

    // Store a new booking or update existing one
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        // Basic input retrieval
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

        if (!$room_id || !$checkin || !$checkout || !$payment_method) {
            http_response_code(400);
            exit("Missing required fields.");
        }

        $room = $this->roomModel->getRoomById($room_id);
        if (!$room) {
            http_response_code(404);
            exit("Room not found.");
        }

        // Calculate nights & totals using room_price from room type
        $nights = max(1, (int)((strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24)));
        $roomTotal = $room['room_price'] * $nights;
        $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;
        $total = $roomTotal + $guestFee;

        if ($bookingId) {
            // UPDATE existing booking
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
                $email
            );

            // Update payment if exists
            if (isset($existing['PaymentID'])) {
                $this->paymentModel->update($existing['PaymentID'], [
                    'Method' => $payment_method,
                    'Amount' => $total
                ]);
            }

            // Send update email
            try {
                $this->sendBookingEmail(
                    $email,
                    $contact,
                    $room['room_name'],
                    $checkin,
                    $checkout,
                    $guests,
                    $checkin_time,
                    $payment_method,
                    'Updated',
                    $nights,
                    $roomTotal,
                    $guestFee,
                    $total
                );
            } catch (\Exception $e) {
                error_log("Update email error: " . $e->getMessage());
            }

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&success=updated");
            exit();
        }

        // CREATE new booking
        try {
            $this->db->beginTransaction();

            // Create booking with pending status
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

            // Create payment record
            $paymentId = $this->paymentModel->create(
                $newBookingId,
                $payment_method,
                $total,
                'pending'
            );

            if (!$paymentId) {
                throw new \Exception("Failed to create payment record");
            }

            // Create guest record (main booker)
            $this->guestModel->create($newBookingId, $contact, $contact, $email);

            // Update room status
            $this->roomModel->updateAvailability($room_id, 'booked');

            $this->db->commit();

            // Send email notification with total amount
            try {
                $this->sendBookingEmail(
                    $email,
                    $contact,
                    $room['room_name'],
                    $checkin,
                    $checkout,
                    $guests,
                    $checkin_time,
                    $payment_method,
                    'Pending',
                    $nights,
                    $roomTotal,
                    $guestFee,
                    $total
                );
            } catch (\Exception $e) {
                error_log("Booking email error: " . $e->getMessage());
            }

            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&success=booking_created");
            exit();

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Booking creation failed: " . $e->getMessage());
            http_response_code(500);
            exit("Could not create booking. Try again later.");
        }
    }

    // Function to send email with total amount
    private function sendBookingEmail($toEmail, $toName, $roomName, $checkin, $checkout, $guests, $checkin_time, $payment_method, $status, $nights, $roomTotal, $guestFee, $total)
    {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hpl78910@gmail.com';
            $mail->Password   = 'cknn jsoq vhmm oedl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender & recipient
            $mail->setFrom('hpl78910@gmail.com', 'Lunera Hotel');
            $mail->addAddress($toEmail, $toName);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "Booking {$status} - Lunera Hotel";
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;'>
                    <div style='background-color: #ffffff; padding: 30px; border-radius: 10px;'>
                        <h2 style='color: #2c3e50; text-align: center;'>Booking {$status}</h2>
                        
                        <p style='font-size: 16px; color: #34495e;'>Hi <strong>{$toName}</strong>,</p>
                        
                        <p style='font-size: 14px; color: #34495e;'>
                            Thank you for booking with Lunera Hotel. Your booking is now <strong>{$status}</strong>.
                        </p>
                        
                        <div style='background-color: #ecf0f1; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                            <h3 style='color: #2c3e50; margin-top: 0;'>Booking Details</h3>
                            <ul style='list-style: none; padding: 0;'>
                                <li style='padding: 8px 0; border-bottom: 1px solid #bdc3c7;'>
                                    <strong>Room:</strong> {$roomName}
                                </li>
                                <li style='padding: 8px 0; border-bottom: 1px solid #bdc3c7;'>
                                    <strong>Check-in:</strong> {$checkin}
                                </li>
                                <li style='padding: 8px 0; border-bottom: 1px solid #bdc3c7;'>
                                    <strong>Check-out:</strong> {$checkout}
                                </li>
                                <li style='padding: 8px 0; border-bottom: 1px solid #bdc3c7;'>
                                    <strong>Number of Nights:</strong> {$nights}
                                </li>
                                <li style='padding: 8px 0; border-bottom: 1px solid #bdc3c7;'>
                                    <strong>Guests:</strong> {$guests}
                                </li>
                                <li style='padding: 8px 0; border-bottom: 1px solid #bdc3c7;'>
                                    <strong>Check-in Time:</strong> {$checkin_time}
                                </li>
                                <li style='padding: 8px 0; border-bottom: 1px solid #bdc3c7;'>
                                    <strong>Payment Method:</strong> {$payment_method}
                                </li>
                            </ul>
                            
                            <div style='margin-top: 20px; padding-top: 20px; border-top: 2px solid #2c3e50;'>
                                <h4 style='color: #2c3e50; margin: 10px 0;'>Payment Breakdown</h4>
                                <ul style='list-style: none; padding: 0;'>
                                    <li style='padding: 5px 0;'>
                                        Room Total ({$nights} nights): ₱" . number_format($roomTotal, 2) . "
                                    </li>
                                    <li style='padding: 5px 0;'>
                                        Additional Guest Fee: ₱" . number_format($guestFee, 2) . "
                                    </li>
                                </ul>
                                <p style='font-size: 18px; font-weight: bold; color: #27ae60; margin: 15px 0;'>
                                    <strong>Total Amount:</strong> ₱" . number_format($total, 2) . "
                                </p>
                            </div>
                        </div>
                        
                        <p style='font-size: 14px; color: #34495e;'>
                            We look forward to welcoming you!
                        </p>
                        
                        <p style='font-size: 12px; color: #7f8c8d; margin-top: 30px;'>
                            If you have any questions, please contact us.<br>
                            <strong>Lunera Hotel</strong>
                        </p>
                    </div>
                </div>
            ";

            $mail->AltBody = "Hi {$toName},\n\n" .
                "Thank you for booking with Lunera Hotel. Your booking is now {$status}.\n\n" .
                "Booking Details:\n" .
                "- Room: {$roomName}\n" .
                "- Check-in: {$checkin}\n" .
                "- Check-out: {$checkout}\n" .
                "- Number of Nights: {$nights}\n" .
                "- Guests: {$guests}\n" .
                "- Check-in Time: {$checkin_time}\n" .
                "- Payment Method: {$payment_method}\n\n" .
                "Payment Breakdown:\n" .
                "- Room Total ({$nights} nights): ₱" . number_format($roomTotal, 2) . "\n" .
                "- Additional Guest Fee: ₱" . number_format($guestFee, 2) . "\n" .
                "- Total Amount: ₱" . number_format($total, 2) . "\n\n" .
                "We look forward to welcoming you!\n\n" .
                "Lunera Hotel";

            $mail->send();
            error_log("✅ Email sent successfully to: " . $toEmail);
        } catch (Exception $e) {
            error_log("❌ Booking email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    // View user bookings
    public function userBookings()
    {
        if (!isset($_SESSION['user_id'])) {
            error_log("❌ Session lost! No user_id in session.");
            header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=session_expired");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['role'] ?? 'user';

        error_log("🔍 userBookings called - UserID: {$userId}, Role: {$userRole}");

        // Fetch bookings with payment info
        $bookings = $this->bookingModel->getBookingsByUser($userId);

        error_log("📊 Found bookings: " . count($bookings));

        include __DIR__ . '/../Views/userbookings.php';
    }

    // Cancel booking (no email sent)
    public function cancel($bookingId = null)
    {
        if (!$bookingId) {
            $bookingId = $_GET['id'] ?? null;
        }

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

        // Update booking status to cancelled
        $this->bookingModel->updateStatusByName($bookingId, 'cancelled');
        
        // Update room status back to available
        $this->roomModel->updateAvailability($booking['RoomID'], 'available');

        // Update payment status if exists
        if (isset($booking['PaymentID'])) {
            $this->paymentModel->updateStatus($booking['PaymentID'], 'refunded');
        }

        header("Location: /Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings&success=canceled");
        exit();
    }
}