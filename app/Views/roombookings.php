<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Models\Room;

$db = (new Database())->connect();
$roomModel = new Room($db);

$roomId = $_GET['room_id'] ?? null;

if ($roomId) {
  $room = $roomModel->getRoomById($roomId);
  if (!$room) {
    die("Room not found.");
  }
} else {
  die("No room selected.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Hotel Booking</title>
  <link rel="stylesheet" href="../public/css/roombookings.css">
  <link
    href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
    rel="stylesheet" />
</head>

<body>
  <?php include "layouts/navigation.php";?>

  <div class="container">
    <!-- Room Card -->
    <div class="room-card">
      <img src="../public/assets/<?= htmlspecialchars($room['image']) ?>"
        alt="<?= htmlspecialchars($room['name']) ?>"
        class="room-image" />
      <div class="room-details">
        <h2>
          <?= htmlspecialchars($room['name']) ?>
          <span class="rating">
            <i class="bx bxs-star"></i> <?= htmlspecialchars($room['rating']) ?>
          </span>
        </h2>
        <p class="description">
          <?= htmlspecialchars($room['description']) ?>
        </p>
        <p class="price">
          <span class="amount">$<?= number_format($room['price'], 2) ?></span>
          <span class="per-night">/night</span>
        </p>
      </div>
    </div>

    <!-- Booking Form -->
    <div class="booking-form">
      <div class="booking-header">
        <h2>Complete Your Booking</h2>
        <button type="button" class="back-btn" onclick="history.back()">
          <i class="bx bx-arrow-back"></i> Back
        </button>
      </div>

      <p class="subtext">Confirm the details for your stay.</p>
      <h3>Reservation Details</h3>
      <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=booking&action=store">
        <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['id']) ?>">

        <div class="form-row">
          <div>
            <label>Check-in</label>
            <div class="input-group">
              <i class="bx bx-calendar"></i>
              <input type="date" name="checkin" required />
            </div>
          </div>
          <div>
            <label>Check-out</label>
            <div class="input-group">
              <i class="bx bx-calendar"></i>
              <input type="date" name="checkout" required />
            </div>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Guests</label>
            <div class="input-group">
              <i class="bx bx-user"></i>
              <input type="number" name="guests" min="1" value="1" required />
            </div>
          </div>
          <div>
            <label>Check-in Time</label>
            <div class="input-group">
              <i class="bx bx-time"></i>
              <input type="time" name="checkin_time" value="14:00" required />
            </div>
          </div>
        </div>

        <!-- Billing Info -->
        <h3>Billing Information</h3>
        <div class="form-row">
          <div>
            <label>Contact</label>
            <div class="input-group">
              <i class="bx bx-phone"></i>
              <input type="text" placeholder="Contact Number" name="contact" required />
            </div>
          </div>
          <div>
            <label>Email Address</label>
            <div class="input-group">
              <i class="bx bx-envelope"></i>
              <input type="email" placeholder="example@gmail.com" name="email" required />
            </div>
          </div>
        </div>

        <label>Payment Option</label>
        <div class="input-group">
          <i class="bx bx-credit-card"></i>
          <select name="payment_method" required>
            <option value="">Select a payment method</option>
            <option value="gcash">Gcash</option>
            <option value="cash">Cash</option>
          </select>
        </div>

        <button type="submit">
          <i class="bx bx-send"></i> Confirm Booking
        </button>
      </form>
    </div>
  </div>
</body>

</html>