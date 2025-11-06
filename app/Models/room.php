<?php

namespace App\Models;

use PDO;

class Room
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllRooms()
    {
        $stmt = $this->db->query("SELECT * FROM rooms");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomById($roomId)
    {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE RoomID = :room_id");
        $stmt->execute([':room_id' => $roomId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAvailability($roomId, $status)
    {
        $stmt = $this->db->prepare("UPDATE rooms SET availability = :status WHERE RoomID = :room_id");
        return $stmt->execute([
            ':status' => $status,
            ':room_id' => $roomId
        ]);
    }
}
