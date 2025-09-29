<?php
require_once __DIR__ . '/../Models/Room.php';

class RoomController {
    private $roomModel;

    public function __construct($db) {
        $this->roomModel = new Room($db);
    }

    public function index() {
        $rooms = $this->roomModel->getAllRooms();
        include __DIR__ . '/../Views/rooms.php';
    }
}
