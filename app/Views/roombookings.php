<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Models\Room;

if (session_status() === PHP_SESSION_NONE) session_start();

$db = (new Database())->connect();
$roomModel = new Room($db);

// Editing booking (from controller)
$editingBooking = $editingBooking ?? null;

// Get room ID from URL or from editingBooking
$roomId = $_GET['room_id'] ?? ($editingBooking['room_id'] ?? null);

if ($roomId) {
    $room = $roomModel->getRoomById($roomId);
    if (!$room) die("Room not found.");
} else {
    die("No room selected.");
}

// Price per night
$pricePerNight = $room['price'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($editingBooking) ? "Edit Booking" : "Hotel Booking" ?></title>

    <link rel="stylesheet" href="../public/css/roombookings.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
</head>

<body>
    <?php include "layouts/navigation.php"; ?>

    <div class="container">

        <div class="room-card">
            <img src="../public/assets/<?= htmlspecialchars($room['image']) ?>" class="room-image">
            <div class="room-details">
                <h2>
                    <?= htmlspecialchars($room['name']) ?>
                    <span class="rating"><?= htmlspecialchars($room['rating']) ?></span>
                </h2>

                <p class="room-info">
                    <i class="bx bx-hash"></i> Room ID: <?= htmlspecialchars($room['RoomID']) ?> &nbsp;&nbsp;
                    <i class="bx bx-building"></i> Floor: <?= htmlspecialchars($room['floor'] ?? 'N/A') ?>
                </p>

                <p class="description"><?= htmlspecialchars($room['description']) ?></p>
                <p class="price"><span class="amount">₱<?= number_format($pricePerNight, 2) ?></span> <span class="per-night">/night</span></p>
            </div>
        </div>

        <!-- BOOKING FORM -->
        <div class="booking-form">
            <div class="booking-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><?= isset($editingBooking) ? "Edit Your Booking" : "Complete Your Booking" ?></h2>
                <button type="button" class="back-btn" onclick="history.back()">
                    <i class="bx bx-arrow-back"></i> Back
                </button>
            </div>

            <form id="bookingForm" method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=booking&action=store">
                <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['RoomID']) ?>">
                <input type="hidden" name="booking_id" value="<?= $editingBooking['BookingID'] ?? '' ?>">

                <div class="form-row">
                    <div>
                        <label>Check-in</label>
                        <div class="input-group">
                            <i class="bx bx-calendar"></i>
                            <input type="date" name="checkin" required value="<?= $editingBooking['CheckIn'] ?? '' ?>">
                        </div>
                    </div>

                    <div>
                        <label>Check-out</label>
                        <div class="input-group">
                            <i class="bx bx-calendar"></i>
                            <input type="date" name="checkout" required value="<?= $editingBooking['CheckOut'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label>Guests</label>
                        <div class="input-group">
                            <i class="bx bx-user"></i>
                            <input type="number" name="guests" min="1" required value="<?= $editingBooking['Guests'] ?? 1 ?>">
                        </div>
                    </div>

                    <div>
                        <label>Check-in Time</label>
                        <div class="input-group">
                            <i class="bx bx-time"></i>
                            <input type="time" name="checkin_time" required value="<?= $editingBooking['CheckIn_Time'] ?? '14:00' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label>Contact</label>
                        <div class="input-group">
                            <i class="bx bx-phone"></i>
                            <input type="text" name="contact" required value="<?= $editingBooking['Contact'] ?? '' ?>" placeholder="Contact number">
                        </div>
                    </div>

                    <div>
                        <label>Email Address</label>
                        <div class="input-group">
                            <i class="bx bx-envelope"></i>
                            <input type="email" name="email" required value="<?= $editingBooking['Email'] ?? '' ?>" placeholder="Gmail">
                        </div>
                    </div>
                </div>

                <label>Payment Option</label>
                <div class="input-group">
                    <i class="bx bx-credit-card"></i>
                    <select name="payment_method" required>
                        <option value="">Select a payment method</option>
                        <option value="Cash" <?= isset($editingBooking) && $editingBooking['Payment_Method'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="GCash" <?= isset($editingBooking) && $editingBooking['Payment_Method'] === 'GCash' ? 'selected' : '' ?>>GCash</option>
                    </select>
                </div>

                <button type="submit" id="openModalBtn"><?= isset($editingBooking) ? 'Update Booking' : 'Submit' ?></button>
            </form>
        </div>
    </div>

    <!-- CONFIRM MODAL -->
    <div id="modal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2 class="modal-title">Confirm Your Booking</h2>
            <p class="modal-sub">Please review your booking details before confirming.</p>
            <h3 class="room-title"><?= htmlspecialchars($room['name']) ?></h3>

            <div class="detail-row"><span>Check-in:</span><span id="m_checkin"></span></div>
            <div class="detail-row"><span>Check-out:</span><span id="m_checkout"></span></div>
            <div class="detail-row"><span>Guests:</span><span id="m_guests"></span></div>
            <div class="detail-row"><span>Time:</span><span id="m_time"></span></div>

            <hr>
            <h4 class="section-title">Price Breakdown</h4>
            <div class="detail-row">
                <span id="m_nights_text"></span>
                <span id="m_roomtotal"></span>
            </div>
            <div class="detail-row">
                <span>+ Guest Fee</span>
                <span id="m_guestfee"></span>
            </div>
            <div class="detail-row">
                <span>+ Extra Night Fee</span>
                <span id="m_extra_night">₱0</span>
            </div>
            <div class="total-row">
                <span>Total:</span>
                <span id="m_total"></span>
            </div>

            <div class="btn-group">
                <button class="confirm-btn" id="confirmSubmit">Confirm Booking</button>
            </div>
        </div>
    </div>

    <!-- PENDING MODAL -->
    <div id="pendingModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Booking Submitted!</h2>
            <p>Thank you for booking. Your booking is now <strong>pending</strong>. You will receive an email once it is confirmed by our staff.</p>
            <button id="pendingCloseBtn" style="text-align: center;">OK</button>
        </div>
    </div>

    <script>
        const modal = document.getElementById("modal");
        const pendingModal = document.getElementById("pendingModal");
        const form = document.getElementById("bookingForm");
        const confirmSubmit = document.getElementById("confirmSubmit");

        const pricePerNight = <?= $pricePerNight ?>;
        const guestExtraFee = 300;

        // Open confirm modal on submit
        form.addEventListener("submit", e => {
            e.preventDefault();

            const checkin = new Date(form.checkin.value);
            const checkout = new Date(form.checkout.value);
            const guests = parseInt(form.guests.value);

            if (checkout <= checkin) {
                alert("Checkout must be after check-in.");
                return;
            }

            const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
            const roomTotal = pricePerNight * nights;
            const guestFee = guests > 1 ? (guests - 1) * guestExtraFee : 0;
            const total = roomTotal + guestFee;

            // Fill confirm modal values
            document.getElementById("m_checkin").textContent = form.checkin.value;
            document.getElementById("m_checkout").textContent = form.checkout.value;
            document.getElementById("m_guests").textContent = guests;
            document.getElementById("m_time").textContent = form.checkin_time.value;
            document.getElementById("m_nights_text").textContent = `₱${pricePerNight.toLocaleString()} × ${nights} night(s)`;
            document.getElementById("m_roomtotal").textContent = `₱${roomTotal.toLocaleString()}`;
            document.getElementById("m_guestfee").textContent = `₱${guestFee.toLocaleString()}`;
            document.getElementById("m_total").textContent = `₱${total.toLocaleString()}`;

            modal.style.display = "flex";
        });

        // Confirm booking → show pending modal, then submit form
        confirmSubmit.addEventListener("click", () => {
            modal.style.display = "none";
            pendingModal.style.display = "flex";

            setTimeout(() => {
                form.submit();
            }, 500);
        });

        // Close pending modal manually
        document.getElementById("pendingCloseBtn").addEventListener("click", () => {
            pendingModal.style.display = "none";
        });

        // Close modals if clicking outside
        window.onclick = e => {
            if (e.target === modal) modal.style.display = "none";
            if (e.target === pendingModal) pendingModal.style.display = "none";
        }
    </script>
</body>
</html>
