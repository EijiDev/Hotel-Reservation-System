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

    /**
     * Get all rooms with room type information
     */
    public function getAllRooms($limit = null)
    {
        $sql = "
            SELECT 
                r.RoomID,
                r.RoomNumber,
                r.Floor,
                r.Status,
                r.image,
                r.rating,
                rt.TypeID,
                rt.Name AS room_name,
                rt.Description,
                rt.Price,
                rt.Amenities
            FROM rooms r
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            ORDER BY r.RoomID ASC
        ";

        if ($limit) {
            $sql .= " LIMIT :limit";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get room by ID with type information
     */
    public function getRoomById($roomId)
    {
        $sql = "
            SELECT 
                r.RoomID,
                r.RoomNumber,
                r.Floor,
                r.Status,
                r.image,
                r.rating,
                rt.TypeID,
                rt.Name AS room_name,
                rt.Description,
                rt.Price,
                rt.Amenities
            FROM rooms r
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            WHERE r.RoomID = :room_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':room_id' => $roomId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update room availability status
     */
    public function updateAvailability($roomId, $status)
    {
        $stmt = $this->db->prepare("UPDATE rooms SET Status = :status WHERE RoomID = :room_id");
        return $stmt->execute([
            ':status' => $status,
            ':room_id' => $roomId
        ]);
    }

    /**
     * Get rooms with pagination
     */
    public function getRoomsRange($offset = 0, $limit = 6)
    {
        $sql = "
            SELECT 
                r.RoomID,
                r.RoomNumber,
                r.Floor,
                r.Status,
                r.image,
                r.rating,
                rt.TypeID,
                rt.Name AS room_name,
                rt.Description,
                rt.Price,
                rt.Amenities
            FROM rooms r
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            ORDER BY r.RoomID ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count available rooms
     */
    public function countAvailableRooms()
    {
        $sql = "SELECT COUNT(*) FROM rooms WHERE Status = 'available'";
        $stmt = $this->db->query($sql);
        return $stmt->fetchColumn();
    }

    /**
     * Get rooms by type
     */
    public function getRoomsByType($typeId)
    {
        $sql = "
            SELECT 
                r.RoomID,
                r.RoomNumber,
                r.Floor,
                r.Status,
                r.image,
                r.rating,
                rt.TypeID,
                rt.Name AS room_name,
                rt.Description,
                rt.Price,
                rt.Amenities
            FROM rooms r
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            WHERE r.TypeID = :type_id
            ORDER BY r.RoomNumber ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type_id', $typeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new room
     */
    public function createRoom($typeId, $floor, $roomNumber, $status = 'available', $image = null, $rating = null)
    {
        $sql = "
            INSERT INTO rooms (TypeID, Floor, RoomNumber, Status, image, rating)
            VALUES (:type_id, :floor, :room_number, :status, :image, :rating)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':type_id', $typeId, PDO::PARAM_INT);
        $stmt->bindValue(':floor', $floor, PDO::PARAM_INT);
        $stmt->bindValue(':room_number', $roomNumber);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':image', $image);
        $stmt->bindValue(':rating', $rating);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update room details
     */
    public function updateRoom($roomId, $data)
    {
        $allowedFields = [
            'TypeID' => 'TypeID',
            'Floor' => 'Floor',
            'RoomNumber' => 'RoomNumber',
            'Status' => 'Status',
            'image' => 'image',
            'rating' => 'rating'
        ];

        $updateFields = [];
        $params = [':room_id' => $roomId];

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

        $sql = "UPDATE rooms SET " . implode(', ', $updateFields) . " WHERE RoomID = :room_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Delete a room
     */
    public function deleteRoom($roomId)
    {
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE RoomID = :room_id");
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Check room availability for date range
     */
    public function isRoomAvailable($roomId, $checkIn, $checkOut)
    {
        $sql = "
            SELECT COUNT(*) 
            FROM bookings b
            JOIN booking_status bs ON b.StatusID = bs.StatusID
            WHERE b.RoomID = :room_id
            AND bs.StatusName IN ('pending', 'confirmed')
            AND (
                (b.CheckIn <= :check_in AND b.CheckOut > :check_in)
                OR (b.CheckIn < :check_out AND b.CheckOut >= :check_out)
                OR (b.CheckIn >= :check_in AND b.CheckOut <= :check_out)
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindValue(':check_in', $checkIn);
        $stmt->bindValue(':check_out', $checkOut);
        $stmt->execute();

        return $stmt->fetchColumn() == 0;
    }
}