<?php

namespace App\Models;

use PDO;

class RoomType
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all room types
     */
    public function getAllRoomTypes()
    {
        $stmt = $this->db->query("
            SELECT TypeID, Name, Description, Price, Amenities
            FROM roomtypes
            ORDER BY Price ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get room type by ID
     */
    public function getRoomTypeById($typeId)
    {
        $stmt = $this->db->prepare("
            SELECT TypeID, Name, Description, Price, Amenities
            FROM roomtypes
            WHERE TypeID = :type_id
            LIMIT 1
        ");

        $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get room type by name
     */
    public function getRoomTypeByName($name)
    {
        $stmt = $this->db->prepare("
            SELECT TypeID, Name, Description, Price, Amenities
            FROM roomtypes
            WHERE Name = :name
            LIMIT 1
        ");

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new room type
     */
    public function create($name, $description, $price, $amenities = '')
    {
        $stmt = $this->db->prepare("
            INSERT INTO roomtypes (Name, Description, Price, Amenities)
            VALUES (:name, :description, :price, :amenities)
        ");

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':amenities', $amenities, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                $typeId = $this->db->lastInsertId();
                error_log("✅ Room type created successfully. TypeID: " . $typeId);
                return $typeId;
            }
        } catch (\PDOException $e) {
            error_log("❌ Failed to create room type: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Update room type
     */
    public function update($typeId, $data)
    {
        $allowedFields = [
            'Name' => 'Name',
            'Description' => 'Description',
            'Price' => 'Price',
            'Amenities' => 'Amenities'
        ];

        $updateFields = [];
        $params = [':type_id' => $typeId];

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

        $sql = "UPDATE roomtypes SET " . implode(', ', $updateFields) . " WHERE TypeID = :type_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Delete room type
     */
    public function delete($typeId)
    {
        $stmt = $this->db->prepare("DELETE FROM roomtypes WHERE TypeID = :type_id");
        $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get available rooms count by type
     */
    public function getAvailableRoomsCount($typeId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM rooms 
            WHERE TypeID = :type_id AND Status = 'available'
        ");

        $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Get room types with availability info
     */
    public function getRoomTypesWithAvailability()
    {
        $stmt = $this->db->query("
            SELECT 
                rt.TypeID,
                rt.Name,
                rt.Description,
                rt.Price,
                rt.Amenities,
                COUNT(r.RoomID) as total_rooms,
                SUM(CASE WHEN r.Status = 'available' THEN 1 ELSE 0 END) as available_rooms
            FROM roomtypes rt
            LEFT JOIN rooms r ON rt.TypeID = r.TypeID
            GROUP BY rt.TypeID, rt.Name, rt.Description, rt.Price, rt.Amenities
            ORDER BY rt.Price ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}