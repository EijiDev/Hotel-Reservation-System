<?php
namespace App\Models;
use PDO;
class Room {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllRooms() {
        $stmt = $this->db->query("SELECT * FROM rooms");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
