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
          <?php
          // Calculate total - SAME calculation as admin/staff dashboard
          $checkin = $booking['CheckIn'];
          $checkout = $booking['CheckOut'];
          
          // Use ceil() like JavaScript for nights calculation
          $checkinTimestamp = strtotime($checkin);
          $checkoutTimestamp = strtotime($checkout);
          $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
          $nights = max(1, $nights); // Minimum 1 night
          
          $roomPrice = $booking['room_price'] ?? 0;
          $guests = $booking['Guests'] ?? 1;
          $checkinTime = $booking['CheckIn_Time'] ?? '14:00';
          
          // Room total
          $roomTotal = $roomPrice * $nights;
          
          // Guest fee: ₱300 per additional guest (first guest is free)
          $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;
          
          // Extra night fee: ₱500 if check-in time is after 6 PM (18:00)
          $extraNightFee = 0;
          if ($checkinTime) {
            list($hours, $minutes) = explode(':', $checkinTime);
            $hours = (int)$hours;
            if ($hours >= 18) {
              $extraNightFee = 500;
            }
          }
          
          // Total = Room + Guest Fee + Extra Night Fee (EXACT same as dashboards)
          $displayTotal = $roomTotal + $guestFee + $extraNightFee;
          
          $bookingStatus = strtolower($booking['booking_status']);
          ?>
          
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
                    - Total: ₱<?= htmlspecialchars(number_format($displayTotal, 2)) ?>
                  </p>
                <?php endif; ?>
              </div>

              <div class="buttons">
                <!-- Only show modify button for pending bookings -->
                <?php if (strtolower($booking['booking_status']) === 'pending') : ?>
                  <a href="/Hotel_Reservation_System/app/public/index.php?controller=booking&action=edit&id=<?= urlencode($booking['BookingID']) ?>"
                    class="modify-btn">Modify</a>
                <?php endif; ?>
                
                <!-- FIXED: Only show cancel button for PENDING bookings (not confirmed) -->
                <?php if (strtolower($booking['booking_status']) === 'pending') : ?>
                  <button class="cancel-btn" data-id="<?= $booking['BookingID'] ?>">Cancel</button>
                <?php endif; ?>
                
                <!-- Show informational message for confirmed bookings -->
                <?php if (strtolower($booking['booking_status']) === 'confirmed') : ?>
                  <p style="font-size: 13px; color: #28a745; margin-top: 10px;">
                    <i class="fa fa-check-circle"></i> Booking confirmed by staff. Contact hotel for changes.
                  </p>
                <?php endif; ?>
                
                <!-- Show informational message for cancelled bookings -->
                <?php if (strtolower($booking['booking_status']) === 'cancelled') : ?>
                  <p style="font-size: 13px; color: #dc3545; margin-top: 10px;">
                    <i class="fa fa-times-circle"></i> This booking has been cancelled. Contact hotel for inquiries.
                  </p>
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