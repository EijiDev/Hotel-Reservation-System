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

    public function getAllRooms($limit = null)
    {
        if ($limit) {
            $stmt = $this->db->prepare("SELECT * FROM rooms LIMIT :limit");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->db->query("SELECT * FROM rooms");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
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

    public function getRoomsRange($offset = 0, $limit = 6)
    {
        $stmt = $this->db->prepare("SELECT * FROM rooms ORDER BY RoomID ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAvailableRooms()
    {
        $sql = "SELECT COUNT(*) FROM rooms WHERE availability = 'available'";
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn();
    }
}
