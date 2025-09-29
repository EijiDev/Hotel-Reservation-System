

<?php
class Booking {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($checkin, $checkout, $guests, $checkin_time, $contact, $email, $payment_method) {
        $sql = "INSERT INTO bookings (checkin, checkout, guests, checkin_time, contact, email, payment_method) 
                VALUES (:checkin, :checkout, :guests, :checkin_time, :contact, :email, :payment_method)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':checkin' => $checkin,
            ':checkout' => $checkout,
            ':guests' => $guests,
            ':checkin_time' => $checkin_time,
            ':contact' => $contact,
            ':email' => $email,
            ':payment_method' => $payment_method
        ]);
    }
}
