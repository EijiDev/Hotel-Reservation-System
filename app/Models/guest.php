<?php

namespace App\Models;

use PDO;

class Guest
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new guest record
     */
    public function create($bookingId, $name, $contact = null, $email = null)
    {
        $sql = "
            INSERT INTO guests (BookingID, Name, Contact, Email)
            VALUES (:booking_id, :name, :contact, :email)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':contact', $contact);
        $stmt->bindValue(':email', $email);

        try {
            if ($stmt->execute()) {
                $guestId = $this->db->lastInsertId();
                error_log("✅ Guest created successfully. GuestID: {$guestId}");
                return $guestId;
            }
        } catch (\PDOException $e) {
            error_log("❌ Failed to create guest: " . $e->getMessage());
            return false;
        }

        return false;
    }

    /**
     * Get guest by ID
     */
    public function getGuestById($guestId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM guests WHERE GuestID = :guest_id LIMIT 1
        ");

        $stmt->bindParam(':guest_id', $guestId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all guests for a specific booking
     */
    public function getGuestsByBookingId($bookingId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM guests WHERE BookingID = :booking_id ORDER BY GuestID ASC
        ");

        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update guest information
     */
    public function update($guestId, $data)
    {
        $allowedFields = [
            'Name' => 'Name',
            'Contact' => 'Contact',
            'Email' => 'Email'
        ];

        $updateFields = [];
        $params = [':guest_id' => $guestId];

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

        $sql = "UPDATE guests SET " . implode(', ', $updateFields) . " WHERE GuestID = :guest_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Delete a guest record
     */
    public function delete($guestId)
    {
        $stmt = $this->db->prepare("DELETE FROM guests WHERE GuestID = :guest_id");
        $stmt->bindParam(':guest_id', $guestId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Delete all guests for a booking
     */
    public function deleteByBookingId($bookingId)
    {
        $stmt = $this->db->prepare("DELETE FROM guests WHERE BookingID = :booking_id");
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Create multiple guests for a booking
     */
    public function createMultiple($bookingId, array $guests)
    {
        $createdGuests = [];

        try {
            $this->db->beginTransaction();

            foreach ($guests as $guest) {
                $guestId = $this->create(
                    $bookingId,
                    $guest['Name'],
                    $guest['Contact'] ?? null,
                    $guest['Email'] ?? null
                );

                if ($guestId) {
                    $createdGuests[] = $guestId;
                } else {
                    throw new \Exception("Failed to create guest: " . $guest['Name']);
                }
            }

            $this->db->commit();
            error_log("✅ Created " . count($createdGuests) . " guests for BookingID: {$bookingId}");
            return $createdGuests;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("❌ Failed to create multiple guests: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all guests with booking details
     */
    public function getAllGuestsWithBookings($limit = null, $offset = null)
    {
        $sql = "
            SELECT 
                g.GuestID,
                g.Name AS GuestName,
                g.Contact,
                g.Email,
                b.BookingID,
                b.CheckIn,
                b.CheckOut,
                rt.Name AS RoomType,
                r.RoomNumber
            FROM guests g
            JOIN bookings b ON g.BookingID = b.BookingID
            JOIN rooms r ON b.RoomID = r.RoomID
            JOIN roomtypes rt ON r.TypeID = rt.TypeID
            ORDER BY b.CheckIn DESC
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
     * Count guests for a booking
     */
    public function countGuestsByBooking($bookingId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM guests WHERE BookingID = :booking_id
        ");

        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }
}