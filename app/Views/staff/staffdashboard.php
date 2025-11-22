<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<link rel="stylesheet" href="/Hotel_Reservation_System/app/public/css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Staff Panel</h2>
            <ul>
                <li class="dashboard-bar"><i class="fa-solid fa-chart-line"></i> Dashboard</li>
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
        <h1>Staff Dashboard</h1>

        <!-- Stats -->
        <div class="stats">
            <div class="card">
                <h3>Total Bookings</h3>
                <p><?= $stats['total_bookings'] ?? 0 ?></p>
                <small>All recorded bookings</small>
            </div>

            <div class="card">
                <h3>Upcoming Check-ins</h3>
                <p><?= $stats['upcoming_checkins'] ?? 0 ?></p>
                <small>Within 7 days</small>
            </div>

            <div class="card">
                <h3>Available Rooms</h3>
                <p><?= $stats['available_rooms'] ?? 0 ?></p>
                <small>Rooms ready for booking</small>
            </div>

            <div class="card">
                <h3>Pending Bookings</h3>
                <p><?= $stats['pending_bookings'] ?? 0 ?></p>
                <small>Awaiting confirmation</small>
            </div>
        </div>

        <div class="manage-bookings">
            <h2>Manage Bookings</h2>

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
                        <th>Payment Method</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            // booking_status comes from booking_status table via JOIN
                            $bookingStatus = strtolower($booking['booking_status'] ?? 'pending');
                            
                            // payment_method comes from payments table
                            $paymentMethod = $booking['payment_method'] ?? 'Cash';
                            
                            // payment_status comes from payments table
                            $paymentStatus = strtolower($booking['payment_status'] ?? 'pending');
                            
                            // Disable confirm button if already confirmed or cancelled
                            $confirmDisabled = in_array($bookingStatus, ['confirmed', 'cancelled', 'checked-in', 'checked-out']);
                            
                            // TotalAmount comes from payments.Amount
                            $totalAmount = $booking['TotalAmount'] ?? 0;
                            ?>
                            <tr>
                                <td><?= $booking['BookingID'] ?></td>
                                <!-- GuestName comes from useraccounts via JOIN -->
                                <td><?= htmlspecialchars($booking['GuestName'] ?? 'Unknown') ?></td>
                                <!-- RoomType comes from roomtypes via JOIN -->
                                <td><?= htmlspecialchars($booking['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= $booking['CheckIn'] ?? 'N/A' ?></td>
                                <td><?= $booking['CheckOut'] ?? 'N/A' ?></td>
                                <td><span class="status <?= $bookingStatus ?>"><?= ucfirst($bookingStatus) ?></span></td>
                                <td><span class="payment <?= $paymentStatus ?>"><?= ucfirst($paymentStatus) ?></span></td>
                                <td><?= ucfirst($paymentMethod) ?></td>
                                <td>₱<?= number_format($totalAmount, 2) ?></td>
                                <td class="actions">
                                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=confirm&booking_id=<?= $booking['BookingID'] ?>"
                                        style="background: <?= $confirmDisabled ? '#6cbb6c' : '#28a745' ?>;
                                               color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none;
                                               pointer-events: <?= $confirmDisabled ? 'none' : 'auto' ?>;
                                               opacity: <?= $confirmDisabled ? '0.6' : '1' ?>;">
                                        Confirm
                                    </a>
                                    
                                    <!-- Check-in button (only for confirmed bookings) -->
                                    <?php if ($bookingStatus === 'confirmed'): ?>
                                        <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=checkin&id=<?= $booking['BookingID'] ?>"
                                            style="background: #007bff; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; margin-left: 5px;">
                                            Check-in
                                        </a>
                                    <?php endif; ?>
                                    
                                    <!-- Check-out button (only for checked-in bookings) -->
                                    <?php if ($bookingStatus === 'checked-in'): ?>
                                        <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=checkout&id=<?= $booking['BookingID'] ?>"
                                            style="background: #6c757d; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; margin-left: 5px;">
                                            Check-out
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center;">No bookings assigned.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if (($totalPages ?? 1) > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= ($totalPages ?? 1); $i++): ?>
                        <a href="?controller=staff&action=index&page=<?= $i ?>"
                            class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>