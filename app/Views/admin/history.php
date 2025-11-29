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

<link rel="stylesheet" href="./css/history.style.css">
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
                        <i class="fa-solid fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=history" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-receipt"></i> History
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
        <h1>Booking History</h1>
        <!-- Stats -->
        <div class="stats" style="grid-template-columns: repeat(3, 1fr);">
            <div class="card">
                <h3>Total Archived</h3>
                <p><?= $stats['total_archived'] ?? 0 ?></p>
                <small>All deleted bookings</small>
            </div>

            <div class="card">
                <h3>Cancelled Bookings</h3>
                <p><?= $stats['cancelled_count'] ?? 0 ?></p>
                <small>User cancelled</small>
            </div>

            <div class="card">
                <h3>Completed Bookings</h3>
                <p><?= $stats['completed_count'] ?? 0 ?></p>
                <small>Checked out</small>
            </div>
        </div>

        <div class="manage-bookings">
            <h2>Archived Bookings</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest</th>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($archivedBookings)): ?>
                        <?php foreach ($archivedBookings as $b): ?>
                            <?php
                            // Calculate total 
                            $checkin = $b['CheckIn'];
                            $checkout = $b['CheckOut'];
                            
                            $checkinTimestamp = strtotime($checkin);
                            $checkoutTimestamp = strtotime($checkout);
                            $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
                            $nights = max(1, $nights);
                            
                            $roomPrice = $b['room_price'] ?? 0;
                            $guests = $b['Guests'] ?? 1;
                            $checkinTime = $b['CheckIn_Time'] ?? '14:00';
                            
                            $roomTotal = $roomPrice * $nights;
                            $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;
                            
                            $extraNightFee = 0;
                            if ($checkinTime) {
                                list($hours, $minutes) = explode(':', $checkinTime);
                                $hours = (int)$hours;
                                if ($hours >= 18) {
                                    $extraNightFee = 500;
                                }
                            }
                            
                            $displayTotal = $roomTotal + $guestFee + $extraNightFee;
                            
                            $bookingStatus = strtolower($b['booking_status'] ?? 'deleted');
                            $paymentStatus = strtolower($b['payment_status'] ?? 'pending');
                            ?>
                            <tr style="opacity: 0.8;">
                                <td><?= $b['BookingID'] ?></td>
                                <td>
                                    <?= htmlspecialchars($b['GuestName'] ?? 'Unknown') ?>
                                    <?php if ($bookingStatus === 'cancelled'): ?>
                                        <br><small style="color: #dc3545;"><i class="fa fa-user-times"></i> Cancelled</small>
                                    <?php elseif ($bookingStatus === 'checked-out'): ?>
                                        <br><small style="color: #28a745;"><i class="fa fa-check"></i> Completed</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($b['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= $b['CheckIn'] ?? 'N/A' ?></td>
                                <td><?= $b['CheckOut'] ?? 'N/A' ?></td>
                                <td><span class="status <?= $bookingStatus ?>"><?= ucfirst($bookingStatus) ?></span></td>
                                <td><span class="payment <?= $paymentStatus ?>"><?= ucfirst($paymentStatus) ?></span></td>
                                <td>₱<?= number_format($displayTotal, 2) ?></td>
                                <td class="actions">
                                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=restore&id=<?= $b['BookingID'] ?>"
                                        class="btn-confirm"
                                        onclick="return confirm('Restore this booking?')">
                                        <i class="fa fa-undo"></i> Restore
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center;">No archived bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?controller=admin&action=history&page=<?= $i ?>"
                            class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>