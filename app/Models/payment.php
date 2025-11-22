<?php

namespace App\Models;

use PDO;

class Payment
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new payment record
     */
    public function create($bookingId, $method, $amount, $status = 'pending')
    {
        $sql = "
            INSERT INTO payments (BookingID, Method, Amount, Status)
            VALUES (:booking_id, :method, :amount, :status)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':method', $method, PDO::PARAM_STR);
        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                $paymentId = $this->db->lastInsertId();
                error_log("✅ Payment created successfully. PaymentID: {$paymentId}");
                return $paymentId;
            }
        } catch (\PDOException $e) {
            error_log("❌ Failed to create payment: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Get payment by ID
     */
    public function getPaymentById($paymentId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM payments WHERE PaymentID = :payment_id LIMIT 1
        ");

        $stmt->bindParam(':payment_id', $paymentId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment by booking ID
     */
    public function getPaymentByBookingId($bookingId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM payments WHERE BookingID = :booking_id LIMIT 1
        ");

        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update payment status
     */
    public function updateStatus($paymentId, $status, $datePaid = null)
    {
        if ($datePaid === null && $status === 'completed') {
            $datePaid = date('Y-m-d H:i:s');
        }

        $sql = "
            UPDATE payments 
            SET Status = :status, DatePaid = :date_paid 
            WHERE PaymentID = :payment_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':date_paid', $datePaid);
        $stmt->bindValue(':payment_id', $paymentId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            error_log("✅ Payment {$paymentId} status updated to '{$status}'");
            return true;
        }

        error_log("❌ Failed to update payment {$paymentId} status");
        return false;
    }

    /**
     * Update payment details
     */
    public function update($paymentId, $data)
    {
        $allowedFields = [
            'Method' => 'Method',
            'Amount' => 'Amount',
            'Status' => 'Status',
            'DatePaid' => 'DatePaid'
        ];

        $updateFields = [];
        $params = [':payment_id' => $paymentId];

        foreach ($data as $key => $value) {
            if (isset($allowedFields[$key])) {
                $dbColumn = $allowedFields[$key];
                $updateFields[] = "$dbColumn = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $sql = "UPDATE payments SET " . implode(', ', $updateFields) . " WHERE PaymentID = :payment_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Delete payment
     */
    public function delete($paymentId)
    {
        $stmt = $this->db->prepare("DELETE FROM payments WHERE PaymentID = :payment_id");
        $stmt->bindParam(':payment_id', $paymentId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get all payments with booking details
     */
    public function getAllPayments($limit = null, $offset = null)
    {
        $sql = "
            SELECT 
                p.PaymentID,
                p.BookingID,
                p.Method,
                p.Amount,
                p.Status,
                p.DatePaid,
                b.CheckIn,
                b.CheckOut,
                u.Name AS CustomerName,
                u.Email AS CustomerEmail,
                rt.Name AS RoomType
            FROM payments p
            JOIN bookings b ON p.BookingID = b.BookingID
            JOIN useraccounts u ON b.UserID = u.UserID
            JOIN rooms r ON b.RoomID = r.RoomID
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            ORDER BY p.PaymentID DESC
        ";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats()
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN Status = 'completed' THEN Amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN Status = 'pending' THEN Amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN Status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN Status = 'pending' THEN 1 ELSE 0 END) as pending_count
            FROM payments
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get payments by date range
     */
    public function getPaymentsByDateRange($startDate, $endDate)
    {
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                b.CheckIn,
                u.Name AS CustomerName
            FROM payments p
            JOIN bookings b ON p.BookingID = b.BookingID
            JOIN useraccounts u ON b.UserID = u.UserID
            WHERE p.DatePaid BETWEEN :start_date AND :end_date
            ORDER BY p.DatePaid DESC
        ");

        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}