<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ FIX 1: Authorization check changed to allow both 'admin' and 'staff'
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
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=history" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-receipt"></i> Booking History
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
        <h1>Reservations Management</h1>

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
                        <th>Email</th>
                        <th>Room Type</th>
                        <th>Room No.</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reservations)): ?>
                        <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td><?= $r['BookingID'] ?></td>
                                <td><?= htmlspecialchars($r['GuestName'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($r['Email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($r['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($r['RoomNumber'] ?? 'N/A') ?></td>
                                <td><?= $r['CheckIn'] ?? 'N/A' ?></td>
                                <td><?= $r['CheckOut'] ?? 'N/A' ?></td>
                                <td><span class="status <?= strtolower($r['StatusName'] ?? 'pending') ?>"><?= ucfirst($r['StatusName'] ?? 'Pending') ?></span></td>
                                <td class="actions">
                                    <button class="btn-view" type="button" onclick='viewModal(<?= json_encode($r, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                        View
                                    </button>
                                    <button class="btn-edit" type="button" onclick='editModal(<?= json_encode($r, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
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
                            <td colspan="9" style="text-align:center; padding: 40px; color: #999;">No reservations found.</td>
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

    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reservation Details</h3>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewBody">
                </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

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
                    <input type="text" name="guest_name" id="editName" required>
                    
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" required>
                    
                    <label>Room Type</label>
                    <input type="text" name="room_type" id="editRoomType" required>
                    
                    <label>Room No.</label>
                    <input type="text" name="room_no" id="editRoomNo" required>
                    
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
                    
                    <label>Guests</label>
                    <input type="number" name="guests" id="editGuests" min="1" required>
                    
                    <label>Status</label>
                    <select name="status" id="editStatus">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
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