<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';
class Mailer
{
    public static function sendBookingConfirmation($toEmail, $toName, $bookingDetails)
    {
        $mail = new PHPMailer(true);

        try {
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

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Booking Confirmation - Lunera Hotel';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #28a745;'>Booking Confirmation</h2>
                    <p>Hi <strong>{$toName}</strong>,</p>
                    <p>Thank you for booking with Lunera Hotel. Your booking has been <strong>confirmed</strong>!</p>
                    
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Booking Details</h3>
                        <ul style='list-style: none; padding: 0;'>
                            <li><strong>Room:</strong> {$bookingDetails['room_name']}</li>
                            <li><strong>Check-in:</strong> {$bookingDetails['checkin']}</li>
                            <li><strong>Check-out:</strong> {$bookingDetails['checkout']}</li>
                            <li><strong>Guests:</strong> {$bookingDetails['guests']}</li>
                            <li><strong>Check-in Time:</strong> {$bookingDetails['checkin_time']}</li>
                            <li><strong>Payment Method:</strong> {$bookingDetails['payment_method']}</li>
                            <li><strong>Total Amount:</strong> ₱" . number_format($bookingDetails['total'], 2) . "</li>
                        </ul>
                    </div>
                    
                    <p>We look forward to welcoming you!</p>
                    <p style='color: #6c757d; font-size: 12px; margin-top: 30px;'>
                        If you have any questions, please contact us.<br>
                        Lunera Hotel
                    </p>
                </div>
            ";

            $mail->AltBody = "Hi {$toName},\n\nThank you for booking with Lunera Hotel. Your booking has been confirmed!\nBooking Details:\n- Room: {$bookingDetails['room_name']}\n- Check-in: {$bookingDetails['checkin']}\n- Check-out: {$bookingDetails['checkout']}\n- Guests: {$bookingDetails['guests']}\n- Check-in Time: {$bookingDetails['checkin_time']}\n- Payment Method: {$bookingDetails['payment_method']}\n- Total: ₱" . number_format($bookingDetails['total'], 2);

            $mail->send();
            error_log("✅ Email sent successfully to: " . $toEmail);
            return true;
        } catch (Exception $e) {
            error_log("❌ Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}