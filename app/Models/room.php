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
    public function getAllRooms()
    {
        $sql = "SELECT * FROM rooms";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $result;
    }
}
