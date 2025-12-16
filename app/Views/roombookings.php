<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Models\Room;
use App\Models\Booking;

if (session_status() === PHP_SESSION_NONE) session_start();

$db = (new Database())->connect();
$roomModel = new Room($db);
$bookingModel = new Booking($db);

$editingBooking = $editingBooking ?? null;
$roomId = $_GET['room_id'] ?? ($editingBooking['RoomID'] ?? null);

if ($roomId) {
    $room = $roomModel->getRoomById($roomId);
    if (!$room) die("Room not found.");
} else {
    die("No room selected.");
}

$pricePerNight = $room['room_price'] ?? $room['Price'];
$isAvailable = (strtolower($room['Status']) === 'available');

$existingBookings = $bookingModel->getBookingsByRoomId($room['RoomID']);
$unavailableDates = [];

foreach ($existingBookings as $b) {
    if (isset($b['booking_status']) && strtolower($b['booking_status']) === 'cancelled') continue;
    if ($editingBooking && $b['BookingID'] == $editingBooking['BookingID']) continue;

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

                <?php if (count($unavailableDates) > 0): ?>
                    <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                        <p style="margin: 0; font-size: 14px; color: #856404;">
                            <i class="bx bx-info-circle"></i> <strong>Note:</strong> This room has existing bookings. Please select available dates.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="booking-form <?= !$isAvailable && !isset($editingBooking) ? 'disabled-form' : '' ?>">
            <div class="booking-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><?= isset($editingBooking) ? "Edit Your Booking" : "Complete Your Booking" ?></h2>
                <button type="button" class="back-btn" onclick="history.back()">
                    <i class="bx bx-arrow-back"></i> Back
                </button>
            </div>

            <?php if (!$isAvailable && !isset($editingBooking)): ?>
                <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 20px; color: #721c24;">
                    <i class="bx bx-error-circle"></i> <strong>This room is currently unavailable for new bookings.</strong>
                </div>
            <?php endif; ?>

            <form id="bookingForm"
                method="POST"
                action="/Hotel_Reservation_System/app/public/index.php?controller=booking&action=store"
                enctype="multipart/form-data"
                data-price-per-night="<?= $pricePerNight ?>"
                data-room-number="<?= htmlspecialchars($room['RoomNumber'] ?? $room['RoomID']) ?>"
                data-unavailable-dates='<?= json_encode($unavailableDates) ?>'>

                <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['RoomID']) ?>">
                <input type="hidden" name="booking_id" value="<?= $editingBooking['BookingID'] ?? '' ?>">

                <div class="form-row">
                    <div>
                        <label>Check-in</label>
                        <div class="input-group">
                            <i class="bx bx-calendar"></i>
                            <input type="date" name="checkin" required min="<?= date('Y-m-d') ?>" value="<?= $editingBooking['CheckIn'] ?? '' ?>">
                        </div>
                    </div>

                    <div>
                        <label>Check-out</label>
                        <div class="input-group">
                            <i class="bx bx-calendar"></i>
                            <input type="date" name="checkout" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= $editingBooking['CheckOut'] ?? '' ?>">
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
                        <option value="Cash" <?= isset($editingBooking) && $editingBooking['payment_method'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="GCash" <?= isset($editingBooking) && $editingBooking['payment_method'] === 'GCash' ? 'selected' : '' ?>>GCash</option>
                    </select>
                </div>

                <div class="form-row">
                    <div style="flex: 1; min-width: 100%;">
                        <label>ID Type</label>
                        <div class="input-group">
                            <i class="bx bx-id-card"></i>
                            <select name="id_type" required>
                                <option value="">Select ID type</option>
                                <option value="School ID">School ID</option>
                                <option value="National ID">National ID</option>
                                <option value="Postal ID">Postal ID</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div style="flex: 1; min-width: 100%;">
                        <label>Upload ID Image</label>
                        <label class="id-dropzone">
                            <i class="bx bx-cloud-upload"></i>
                            <div class="id-drop-text">
                                <span class="id-drop-title">Drag & drop your ID here</span>
                                <span class="id-drop-sub">or click to browse (JPG / PNG)</span>
                            </div>
                            <input type="file" name="id_image" accept="image/*" required>
                        </label>
                    </div>
                </div>

                <button type="submit" id="openModalBtn" <?= !$isAvailable && !isset($editingBooking) ? 'disabled style="background:#ccc; cursor:not-allowed;"' : '' ?>>
                    <?= isset($editingBooking) ? 'Update Booking' : 'Submit' ?>
                </button>
            </form>
        </div>
    </div>

    <div id="modal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2 class="modal-title">Confirm Your Booking</h2>
            <p class="modal-sub">Please review your booking details before confirming.</p>
            <h3 class="room-title"><?= htmlspecialchars($room['room_name']) ?> - Room #<?= htmlspecialchars($room['RoomNumber'] ?? $room['RoomID']) ?></h3>

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
    <script src="../public/js/bookingsvalidation.js"></script>
</body>

</html>