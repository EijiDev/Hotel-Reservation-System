<?php

namespace App\Models;

use PDO;

class Room
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getRoomById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getAllRooms()
    {
        $sql = "SELECT * FROM rooms";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $result;
    }
}
