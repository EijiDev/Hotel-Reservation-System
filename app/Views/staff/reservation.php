<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authorization check - staff and admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
    exit;
}
?>

<link rel="stylesheet" href="./css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
<title>Reservation</title>

<body>
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Staff Panel</h2>
            <ul>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=index" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-book"></i> Bookings
                    </a>
                </li>
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=reservations" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-calendar-check"></i> Reservations
                    </a>
                </li>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=currentGuests" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-users"></i> Current Guests
                    </a>
                </li>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=guestHistory" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-user-clock"></i> Guest History
                    </a>
                </li>
            </ul>
        </div>
        <div class="bottom">
            <a href="/Hotel_Reservation_System/app/public/index.php?controller=logout&action=index" class="logout">
                <i class="fa-solid fa-right-from-bracket"></i> Log out
            </a>
        </div>
    </div>

    <div class="main">
        <h1>Reservations</h1>

        <div class="stats" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 35px;">
            <div class="card">
                <h3>Total Reservations</h3>
                <p><?= $stats['total_reservations'] ?? 0 ?></p>
                <small>All active reservations</small>
            </div>

            <div class="card">
                <h3>Pending Bookings</h3>
                <p><?= $stats['pending_bookings'] ?? 0 ?></p>
                <small>Awaiting confirmation</small>
            </div>

            <div class="card">
                <h3>Confirmed Today</h3>
                <p><?= $stats['confirmed_today'] ?? 0 ?></p>
                <small>Bookings confirmed today</small>
            </div>
        </div>

        <div class="manage-bookings">
            <h2>All Reservations</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest Name</th>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reservations)): ?>
                        <?php foreach ($reservations as $r): ?>
                            <?php
                            // ✅ Calculate total - SAME as dashboard.php
                            $checkin = $r['CheckIn'];
                            $checkout = $r['CheckOut'];
                            
                            $checkinTimestamp = strtotime($checkin);
                            $checkoutTimestamp = strtotime($checkout);
                            $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
                            $nights = max(1, $nights);
                            
                            $roomPrice = $r['room_price'] ?? 0;
                            $guests = $r['Guests'] ?? 1;
                            $checkinTime = $r['CheckIn_Time'] ?? '14:00';
                            
                            // Room total
                            $roomTotal = $roomPrice * $nights;
                            
                            // Guest fee: ₱300 per additional guest
                            $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;
                            
                            // Extra night fee: ₱500 if check-in after 6 PM
                            $extraNightFee = 0;
                            if ($checkinTime) {
                                list($hours, $minutes) = explode(':', $checkinTime);
                                $hours = (int)$hours;
                                if ($hours >= 18) {
                                    $extraNightFee = 500;
                                }
                            }
                            
                            // Total
                            $displayTotal = $roomTotal + $guestFee + $extraNightFee;
                            ?>
                            <tr>
                                <td><?= $r['BookingID'] ?></td>
                                <td><?= htmlspecialchars($r['GuestName'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($r['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= $r['CheckIn'] ?? 'N/A' ?></td>
                                <td><?= $r['CheckOut'] ?? 'N/A' ?></td>
                                <td><span class="status <?= strtolower($r['StatusName'] ?? 'pending') ?>"><?= ucfirst($r['StatusName'] ?? 'Pending') ?></span></td>
                                <td>
                                    <span class="status <?= strtolower($r['PaymentStatus'] ?? 'pending') ?>">
                                        <?= ucfirst($r['PaymentStatus'] ?? 'Pending') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['PaymentMethod'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($displayTotal, 2) ?></td>
                                <td class="actions">
                                    <button class="btn-view" type="button" onclick='viewModal(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        View
                                    </button>
                                    <button class="btn-edit" type="button" onclick='editModal(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        Edit
                                    </button>
                                    <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=checkinReservation" style="display: inline-block; margin: 0;">
                                        <input type="hidden" name="booking_id" value="<?= $r['BookingID'] ?>">
                                        <button class="btn-confirm" type="submit" onclick="return confirm('Check-in this reservation?')">
                                            Check-in
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center; padding: 40px; color: #999;">No reservations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?controller=staff&action=reservations&page=<?= $i ?>"
                            class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reservation Details</h3>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer" id="viewModalFooter">
                <button type="button" onclick="closeModal('viewModal')">Close</button>
                <!-- Mark as Paid button will be added here dynamically -->
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Reservation</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=updateReservation">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="editId">

                    <label>Name</label>
                    <input type="text" name="guest_name" id="editName" disabled>

                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" disabled>

                    <label>Contact</label>
                    <input type="text" name="contact" id="editContact" disabled>

                    <label>Street</label>
                    <input type="text" name="street" id="editStreet">

                    <label>Barangay</label>
                    <input type="text" name="barangay" id="editBarangay">

                    <label>City</label>
                    <input type="text" name="city" id="editCity">

                    <label>Province</label>
                    <input type="text" name="province" id="editProvince">

                    <label>Postal Code</label>
                    <input type="text" name="postal_code" id="editPostalCode">

                    <label>Check-in</label>
                    <input type="date" name="checkin" id="editCheckin" required>

                    <label>Check-out</label>
                    <input type="date" name="checkout" id="editCheckout" required>

                    <label>Check-in Time</label>
                    <input type="time" name="checkin_time" id="editCheckinTime" value="14:00" required>

                    <label>Guests</label>
                    <input type="number" name="guests" id="editGuests" min="1" required>

                    <label>Booking Status</label>
                    <select name="status" id="editStatus">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <label>Payment Status</label>
                    <select name="payment_status" id="editPaymentStatus">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../public/js/reservationModal.js"></script>
</body>