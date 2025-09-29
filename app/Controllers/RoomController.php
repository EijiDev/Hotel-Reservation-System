<?php

namespace App\Controllers;

use App\Models\Room;

class RoomController
{
    private $roomModel;

    public function __construct($db)
    {
        $this->roomModel = new Room($db);
    }

    public function index(): void
    {
        $rooms = $this->roomModel->getAllRooms();

        // load the view
        include __DIR__ . '/../Views/rooms.php';
    }
}
