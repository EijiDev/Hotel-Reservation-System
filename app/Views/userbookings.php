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
  <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
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
            <img src="../public/assets/<?= htmlspecialchars($booking['room_image'] ?? 'default-room.jpg') ?>"
              alt="<?= htmlspecialchars($booking['room_name']) ?>" class="room-image">

            <div class="card-content">
              <div class="room-header">
                <!-- room_name comes from roomtypes table -->
                <h2><?= htmlspecialchars($booking['room_name']) ?></h2>
                <!-- booking_status comes from booking_status table -->
                <span class="status <?= strtolower($booking['booking_status']) ?>">
                  <?= htmlspecialchars($booking['booking_status']) ?>
                </span>
              </div>

              <div class="room-details">
                <!-- RoomID is still available -->
                <p><i class="fa fa-door-closed"></i> Room #<?= htmlspecialchars($booking['RoomNumber'] ?? $booking['RoomID']) ?></p>
                <!-- Floor comes from rooms table -->
                <p><i class="fa fa-building"></i> Floor: <?= htmlspecialchars($booking['Floor'] ?? 'N/A') ?></p>
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
                <!-- room_price comes from roomtypes table (as Price) -->
                <p>₱<?= htmlspecialchars(number_format($booking['room_price'] ?? 0, 2)) ?> <span style="color:#555">/ night</span></p>
                
                <!-- Display payment info if available -->
                <?php if (isset($booking['payment_method']) && $booking['payment_method']) : ?>
                  <p style="font-size: 14px; color: #666; margin-top: 5px;">
                    <i class="fa fa-credit-card"></i> <?= htmlspecialchars($booking['payment_method']) ?>
                    <?php if (isset($booking['Amount'])) : ?>
                      - Total: ₱<?= htmlspecialchars(number_format($booking['Amount'], 2)) ?>
                    <?php endif; ?>
                  </p>
                <?php endif; ?>
              </div>

              <div class="buttons">
                <!-- Only show modify button for pending bookings -->
                <?php if (strtolower($booking['booking_status']) === 'pending') : ?>
                  <a href="/Hotel_Reservation_System/app/public/index.php?controller=booking&action=edit&id=<?= urlencode($booking['BookingID']) ?>"
                    class="modify-btn">Modify</a>
                <?php endif; ?>
                
                <!-- Show cancel button for pending and confirmed bookings -->
                <?php if (in_array(strtolower($booking['booking_status']), ['pending', 'confirmed'])) : ?>
                  <button class="cancel-btn" data-id="<?= $booking['BookingID'] ?>">Cancel</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php else : ?>
        <p class="no-bookings">No bookings found.</p>
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