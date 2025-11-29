<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

class Mailer
{
    /**
     * Send booking confirmation email with calculated totals
     * 
     * @param string $toEmail Recipient email
     * @param string $toName Recipient name
     * @param array $bookingDetails Booking details array with all necessary fields
     * @return bool Success status
     */
    public static function sendBookingConfirmation($toEmail, $toName, $bookingDetails)
    {
        $mail = new PHPMailer(true);

        try {
            // Calculate totals if not already provided
            $checkin = $bookingDetails['CheckIn'] ?? $bookingDetails['checkin'] ?? '';
            $checkout = $bookingDetails['CheckOut'] ?? $bookingDetails['checkout'] ?? '';
            $guests = $bookingDetails['Guests'] ?? $bookingDetails['guests'] ?? 1;
            $roomPrice = $bookingDetails['room_price'] ?? 0;
            $checkinTime = $bookingDetails['CheckIn_Time'] ?? $bookingDetails['checkin_time'] ?? '14:00';
            
            // Calculate nights
            $nights = max(1, (int)((strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24)));
            
            // Calculate room total
            $roomTotal = $roomPrice * $nights;
            
            // Calculate guest fee (₱300 per additional guest)
            $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;
            
            // Calculate extra night fee (₱500 if check-in after 6 PM)
            $extraNightFee = 0;
            if ($checkinTime) {
                list($hours, $minutes) = explode(':', $checkinTime);
                $hours = (int)$hours;
                if ($hours >= 18) {
                    $extraNightFee = 500;
                }
            }
            
            // Calculate total
            $total = $roomTotal + $guestFee + $extraNightFee;

            // Extract other details
            $roomName = $bookingDetails['room_name'] ?? 'N/A';
            $paymentMethod = $bookingDetails['payment_method'] ?? 'Cash';

            // SMTP settings from .env
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'];

            // Sender & recipient
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($toEmail, $toName);

            // Simple plain text email
            $mail->isHTML(false);
            $mail->Subject = 'Booking Confirmed - Lunera Hotel';
            $mail->Body = "Hi {$toName},\n\n" .
                "Great news! Your booking with Lunera Hotel has been CONFIRMED!\n\n" .
                "BOOKING DETAILS\n:" .
                "Room: {$roomName}\n" .
                "Check-in: {$checkin}\n" .
                "Check-out: {$checkout}\n" .
                "Number of Nights: {$nights}\n" .
                "Guests: {$guests}\n" .
                "Check-in Time: {$checkinTime}\n" .
                "Payment Method: {$paymentMethod}\n\n" .
                "PAYMENT BREAKDOWN:\n" .
                "Room Total ({$nights} nights): ₱" . number_format($roomTotal, 2) . "\n" .
                "Additional Guest Fee: ₱" . number_format($guestFee, 2) . "\n" .
                "Extra Night Fee (After 6 PM): ₱" . number_format($extraNightFee, 2) . "\n" .
                "----------------\n" .
                "TOTAL AMOUNT: ₱" . number_format($total, 2) . "\n\n" .
                "We look forward to welcoming you!\n\n" .
                "If you have any questions, please contact us.\n\n" .
                "Best regards,\n" .
                "Lunera Hotel";

            $mail->send();
            error_log("✅ Confirmation email sent successfully to: " . $toEmail);
            return true;
            
        } catch (Exception $e) {
            error_log("❌ Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}