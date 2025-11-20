<?php
// app/Controllers/PaymentController.php
namespace App\Controllers;

class PaymentController
{
    public function qrCheckout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $paymentIntentId = $_GET['payment_intent_id'] ?? null;
        $clientKey       = $_GET['client_key'] ?? null;

        if (!$paymentIntentId || !$clientKey) {
            die("Payment details missing.");
        }

        include __DIR__ . '/../Views/paymongo_gc_qr.php';
    }

    // Webhook to confirm payment
    public function webhook()
    {
        require_once __DIR__ . '/../Config/paymongo.php';
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['data']['attributes']['status']) && $input['data']['attributes']['status'] === 'succeeded') {
            $description = $input['data']['attributes']['description'] ?? '';
            preg_match('/Booking ID: (\d+)/', $description, $matches);
            $bookingId = $matches[1] ?? null;

            if ($bookingId) {
                require_once __DIR__ . '/../Models/Booking.php';
                $db = (new \App\Config\Database())->connect();
                $bookingModel = new \App\Models\Booking($db);
                $bookingModel->updateStatus($bookingId, 'Paid');
            }
        }

        http_response_code(200);
    }
}
