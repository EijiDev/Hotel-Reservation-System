<?php

namespace App\Controllers;

use PDO;
use App\Config\Database;

class AdminController
{
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            echo "<p style='text-align:center; color:red; font-family:sans-serif;'>🚫 You do not have authorization to access this page.</p>";
            header("refresh:2;url=/Hotel_Reservation_System/app/views/login.php?error=unauthorized");
            exit;
        }

        // connect to database
        $this->db = (new Database())->connect();
    }

    public function index()
    {
        // Stats Section
        $stats = [
            'total_revenue' => $this->getValue("SELECT SUM(price) FROM bookings b JOIN rooms r ON b.RoomID = r.RoomID WHERE b.status = 'booked'"),
            'total_bookings' => $this->getValue("SELECT COUNT(*) FROM bookings"),
            'upcoming_checkins' => $this->getValue("SELECT COUNT(*) FROM bookings WHERE CheckIn >= CURDATE() AND CheckIn <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)"),
            'available_rooms' => $this->getValue("SELECT COUNT(*) FROM rooms WHERE status = 'available'")
        ];

        // Bookings List
        $sql = "
    SELECT 
        b.BookingID,
        u.Name AS GuestName,
        r.name AS RoomType,
        b.CheckIn,
        b.CheckOut,
        b.Payment_Method AS PaymentStatus,
        r.price AS TotalAmount
    FROM bookings b
    LEFT JOIN useraccounts u ON b.UserID = u.UserID
    LEFT JOIN rooms r ON b.RoomID = r.RoomID
    ORDER BY b.BookingID DESC
";

        $bookings = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    private function getValue($sql)
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn() ?: 0;
    }
}
