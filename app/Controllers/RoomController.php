<?php

namespace App\Controllers;

use App\Models\Room;

class RoomController
{
    private $roomModel;

    public function __construct($db)
    {
        // NO login check here - allow public browsing
        $this->roomModel = new Room($db);
    }

    // Landing page: first 6 rooms
    public function index()
    {
        $rooms = $this->roomModel->getAllRooms();
        $rooms = array_slice($rooms, 0, 6);
        include __DIR__ . '/../Views/rooms.php';
    }

    // Rooms 7-12
    public function availableRooms()
    {
        $rooms = $this->roomModel->getRoomsRange(6, 6); // fetch rooms 7–12
        include __DIR__ . '/../Views/availablerooms.php';
    }
}