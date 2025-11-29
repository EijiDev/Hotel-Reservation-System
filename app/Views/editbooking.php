<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Config\Database;
use App\Models\Room;
use App\Models\Booking;
if (session_status() === PHP_SESSION_NONE) session_start();

$db = (new Database())->connect();
$roomModel = new Room($db);
$bookingModel = new Booking($db);

if (!isset($editingBooking) || !$editingBooking) {
    die("No booking data found.");
}

$roomId = $editingBooking['RoomID'];
$room = $roomModel->getRoomById($roomId);
if (!$room) die("Room not found.");

$pricePerNight = $room['room_price'] ?? $room['Price'];

$existingBookings = $bookingModel->getBookingsByRoomId($room['RoomID']);
$unavailableDates = [];

foreach ($existingBookings as $b) {
    if ($b['BookingID'] == $editingBooking['BookingID']) continue;
    $unavailableDates[] = [
        'checkin' => $b['CheckIn'],
        'checkout' => $b['CheckOut']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Booking - Lunera Hotel</title>
    <link rel="stylesheet" href="../public/css/roombookings.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
</head>
<body data-price="<?= $pricePerNight ?>" data-unavailable='<?= json_encode($unavailableDates) ?>'>
    
    <?php include "layouts/navigation.php"; ?>

    <div class="container">
        <div class="room-card">
            <img src="../public/assets/<?= htmlspecialchars($room['image'] ?? 'default-room.jpg') ?>" class="room-image">
            <div class="room-details">
                <h2>
                    <?= htmlspecialchars($room['room_name']) ?>
                    <span class="rating"><?= htmlspecialchars($room['rating'] ?? '4.5') ?></span>
                </h2>
                <p class="room-info">
                    <i class="bx bx-hash"></i> Room #<?= htmlspecialchars($room['RoomNumber'] ?? $room['RoomID']) ?> &nbsp;&nbsp;
                    <i class="bx bx-building"></i> Floor: <?= htmlspecialchars($room['Floor'] ?? 'N/A') ?>
                </p>
                <p class="description"><?= htmlspecialchars($room['Description']) ?></p>
                <p class="price"><span class="amount">₱<?= number_format($pricePerNight, 2) ?></span> <span class="per-night">/night</span></p>

                <div class="info-notice">
                    <i class="bx bx-info-circle"></i> You are editing an existing booking
                </div>
            </div>
        </div>

        <div class="booking-form">
            <div class="booking-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="bx bx-edit"></i> Edit Your Booking</h2>
                <button type="button" class="back-btn" onclick="window.location.href='/Hotel_Reservation_System/app/public/index.php?controller=booking&action=userBookings'">
                    <i class="bx bx-arrow-back"></i> Back
                </button>
            </div>

            <form id="editBookingForm" method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=booking&action=store">
                <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['RoomID']) ?>">
                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($editingBooking['BookingID']) ?>">

                <div class="form-row">
                    <div>
                        <label>Check-in Date</label>
                        <div class="input-group">
                            <i class="bx bx-calendar"></i>
                            <input type="date" name="checkin" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($editingBooking['CheckIn']) ?>">
                        </div>
                    </div>

                    <div>
                        <label>Check-out Date</label>
                        <div class="input-group">
                            <i class="bx bx-calendar"></i>
                            <input type="date" name="checkout" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= htmlspecialchars($editingBooking['CheckOut']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label>Number of Guests</label>
                        <div class="input-group">
                            <i class="bx bx-user"></i>
                            <input type="number" name="guests" min="1" max="10" required value="<?= htmlspecialchars($editingBooking['Guests']) ?>">
                        </div>
                    </div>

                    <div>
                        <label>Check-in Time</label>
                        <div class="input-group">
                            <i class="bx bx-time"></i>
                            <input type="time" name="checkin_time" required value="<?= htmlspecialchars($editingBooking['CheckIn_Time']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label>Contact Number</label>
                        <div class="input-group">
                            <i class="bx bx-phone"></i>
                            <input type="text" name="contact" required value="<?= htmlspecialchars($editingBooking['Contact']) ?>" placeholder="Contact number">
                        </div>
                    </div>

                    <div>
                        <label>Email Address</label>
                        <div class="input-group">
                            <i class="bx bx-envelope"></i>
                            <input type="email" name="email" required value="<?= htmlspecialchars($editingBooking['Email']) ?>" placeholder="your-email@gmail.com">
                        </div>
                    </div>
                </div>

                <label>Payment Method</label>
                <div class="input-group">
                    <i class="bx bx-credit-card"></i>
                    <select name="payment_method" required>
                        <option value="">Select a payment method</option>
                        <option value="Cash" <?= ($editingBooking['payment_method'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="GCash" <?= ($editingBooking['payment_method'] ?? '') === 'GCash' ? 'selected' : '' ?>>GCash</option>
                        <option value="Credit Card" <?= ($editingBooking['payment_method'] ?? '') === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                    </select>
                </div>

                <div class="button-group" style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" id="updateBookingBtn" style="flex: 1;">
                        <i class="bx bx-save"></i> Update Booking
                    </button>
                    <button type="button"
                        onclick="if(confirm('Are you sure you want to cancel this booking?')) window.location.href='/Hotel_Reservation_System/app/public/index.php?controller=booking&action=cancel&id=<?= $editingBooking['BookingID'] ?>'"
                        style="flex: 1; background: #e74c3c;">
                        <i class="bx bx-x"></i> Cancel Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="updateModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2 class="modal-title"><i class="bx bx-edit"></i> Confirm Booking Update</h2>
            <p class="modal-sub">Please review your updated booking details.</p>
            <h3 class="room-title"><?= htmlspecialchars($room['room_name']) ?></h3>

            <div class="detail-row"><span>Check-in:</span><span id="m_checkin"></span></div>
            <div class="detail-row"><span>Check-out:</span><span id="m_checkout"></span></div>
            <div class="detail-row"><span>Guests:</span><span id="m_guests"></span></div>
            <div class="detail-row"><span>Time:</span><span id="m_time"></span></div>
            <div class="detail-row"><span>Contact:</span><span id="m_contact"></span></div>
            <div class="detail-row"><span>Email:</span><span id="m_email"></span></div>

            <hr>
            <h4 class="section-title">Updated Price Breakdown</h4>
            <div class="detail-row">
                <span id="m_nights_text"></span>
                <span id="m_roomtotal"></span>
            </div>
            <div class="detail-row">
                <span>+ Guest Fee</span>
                <span id="m_guestfee"></span>
            </div>
            <div class="total-row">
                <span>Total:</span>
                <span id="m_total"></span>
            </div>

            <div class="btn-group">
                <button class="confirm-btn" id="confirmUpdate">Confirm Update</button>
                <button class="cancel-btn" id="cancelUpdate" style="background: #95a5a6;">Cancel</button>
            </div>
        </div>
    </div>
<script src="../public/js/editbooking.js"></script>
</body>
</html>
