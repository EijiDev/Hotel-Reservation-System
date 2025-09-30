<?php
namespace App\Controllers;

use App\Models\Room;

class HomeController {
    private $roomModel;

    public function __construct($db) {
        $this->roomModel = new Room($db);
    }

    public function index() {
        $rooms = $this->roomModel->getAllRooms();

        include __DIR__ . '/../Views/home.php';
    }
}
