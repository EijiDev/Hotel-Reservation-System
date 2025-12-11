<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authorization check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
    exit;
}
?>

<link rel="stylesheet" href="./css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Admin Panel</h2>
            <ul>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=index" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-book"></i> Bookings
                    </a>
                </li>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-calendar-check"></i> Reservations
                    </a>
                </li>
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=currentGuests" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-users"></i> Current Guests
                    </a>
                </li>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=guestHistory" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-user-clock"></i> Guest History
                    </a>
                </li>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=history" style="color: #fff; text-decoration: none; display: block;">
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

    <!-- Main Content -->
    <div class="main">
        <h1>Current Guests</h1>

        <!-- Stats -->
        <div class="stats" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 35px;">
            <div class="card">
                <h3>Total Current Guests</h3>
                <p><?= $stats['total_current_guests'] ?? 0 ?></p>
                <small>Currently checked-in</small>
            </div>

            <div class="card">
                <h3>Occupied Rooms</h3>
                <p><?= $stats['occupied_rooms'] ?? 0 ?></p>
                <small>Rooms in use</small>
            </div>

            <div class="card">
                <h3>Expected Checkouts Today</h3>
                <p><?= $stats['checkouts_today'] ?? 0 ?></p>
                <small>Guests leaving today</small>
            </div>
        </div>

        <div class="manage-bookings">
            <h2>Checked-in Guests</h2>

            <table>
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Room Type</th>
                        <th>Room No.</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($currentGuests)): ?>
                        <?php foreach ($currentGuests as $guest): ?>
                            <tr>
                                <td><?= htmlspecialchars($guest['GuestName'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($guest['Email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($guest['Contact'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($guest['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($guest['RoomNumber'] ?? 'N/A') ?></td>
                                <td><?= $guest['CheckIn'] ?? 'N/A' ?></td>
                                <td><?= $guest['CheckOut'] ?? 'N/A' ?></td>
                                <td class="actions">
                                    <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=checkoutGuest" style="display: inline-block; margin: 0;">
                                        <input type="hidden" name="guest_id" value="<?= $guest['GuestID'] ?>">
                                        <input type="hidden" name="booking_id" value="<?= $guest['BookingID'] ?>">
                                        <input type="hidden" name="room_id" value="<?= $guest['RoomID'] ?>">
                                        <button class="btn-delete" type="submit" onclick="return confirm('Check-out this guest?')">
                                            Check-out
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 40px; color: #999;">No guests currently checked-in.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?controller=admin&action=currentGuests&page=<?= $i ?>"
                            class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>