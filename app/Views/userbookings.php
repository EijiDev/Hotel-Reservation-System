<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings</title>
  <link rel="stylesheet" href="../public/css/userbookings.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <?php include "layouts/navigation.php"; ?>

  <div class="container">
    <h1 class="title">My Bookings</h1>

    <?php if (!empty($bookings)) : ?>
      <?php foreach ($bookings as $booking) : ?>
        <?php if (!empty($booking['BookingID'])) : ?>
          <div class="booking-card">
            <img src="../public/assets/<?= htmlspecialchars($booking['room_image']) ?>"
              alt="<?= htmlspecialchars($booking['room_name']) ?>" class="room-image">

            <div class="card-content">
              <div class="room-header">
                <h2><?= htmlspecialchars($booking['room_name']) ?></h2>
                <span class="status <?= strtolower($booking['booking_status']) ?>">
                  <?= htmlspecialchars($booking['booking_status']) ?>
                </span>
              </div>

              <div class="room-details">
                <p><i class="fa fa-door-closed"></i> Room ID: <?= htmlspecialchars($booking['room_id']) ?></p>
                <p><i class="fa fa-building"></i> Floor: <?= htmlspecialchars($booking['floor'] ?? 'N/A') ?></p>
              </div>

              <div class="check-info">
                <div>
                  <p class="label">Check-in</p>
                  <p class="date"><?= htmlspecialchars($booking['CheckIn']) ?></p>
                </div>
                <div>
                  <p class="label">Check-out</p>
                  <p class="date"><?= htmlspecialchars($booking['CheckOut']) ?></p>
                </div>
              </div>

              <div class="price">
                <p>₱<?= htmlspecialchars($booking['room_price']) ?> <span style="color:#555">/ night</span></p>
              </div>

              <div class="buttons">
                <?php if ($booking['booking_status'] === 'pending') : ?>
                  <a href="/Hotel_Reservation_System/app/public/index.php?controller=booking&action=edit&id=<?= urlencode($booking['BookingID']) ?>"
                    class="modify-btn">Modify</a>
                <?php endif; ?>
                <button class="cancel-btn" data-id="<?= $booking['BookingID'] ?>">Cancel</button>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php else : ?>
      <p style="text-align:center; color:#555;">No bookings found.</p>
    <?php endif; ?>
  </div>

  <script>
    // Cancel button
    document.querySelectorAll(".cancel-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        if (confirm("Do you want to cancel this booking?")) {
          window.location.href = `/Hotel_Reservation_System/app/public/index.php?controller=booking&action=cancel&id=${id}`;
        }
      });
    });
  </script>
</body>

</html>