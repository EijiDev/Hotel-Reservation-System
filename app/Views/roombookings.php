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

  <div class="container">
    <!-- Room Card -->
    <div class="room-card">
      <img src="../public/assets/room1.png" alt="Comfort Twin Bed Room" class="room-image" />
      <div class="room-details">
        <h2>
          Comfort Twin Bed Room
          <span class="rating"><i class="bx bxs-star"></i> 4.8</span>
        </h2>
        <p class="category"><i class="bx bx-bed"></i> Deluxe</p>
        <p class="description">
          Experience unparalleled luxury and comfort in the heart of the city.
          The Grand Plaza offers breathtaking views and world-class service.
        </p>
        <p class="price">
          <span class="amount">$350</span><span class="per-night">/night</span>
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
        <!-- Reservation Details -->
        <div class="form-row">
          <div>
            <label>Check-in</label>
            <div class="input-group">
              <i class="bx bx-calendar"></i>
              <input type="date" value="2025-09-05" name="checkin" />
            </div>
          </div>
          <div>
            <label>Check-out</label>
            <div class="input-group">
              <i class="bx bx-calendar"></i>
              <input type="date" value="2025-09-08" name="checkout" />
            </div>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Guests</label>
            <div class="input-group">
              <i class="bx bx-user"></i>
              <input type="number" value="1" name="guests" />
            </div>
          </div>
          <div>
            <label>Check-in Time</label>
            <div class="input-group">
              <i class="bx bx-time"></i>
              <input type="time" value="14:00" name="checkin_time" />
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
              <input type="text" placeholder="Contact Number" name="contact" />
            </div>
          </div>
          <div>
            <label>Email Address</label>
            <div class="input-group">
              <i class="bx bx-envelope"></i>
              <input type="email" placeholder="example@gmail.com" name="email" />
            </div>
          </div>
        </div>

        <label>Payment Option</label>
        <div class="input-group">
          <i class="bx bx-credit-card"></i>
          <select name="payment_method">
            <option>Select a payment method</option>
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