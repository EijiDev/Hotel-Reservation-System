<link rel="stylesheet" href="./css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Admin Panel</h2>
            <ul>
                <li class="dashboard-bar"><i class="fa-solid fa-chart-line"></i> Dashboard</li>
                <li class="dashboard-bar"><i class="fa-solid fa-receipt"></i> History</li>
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
        <h1>Admin Dashboard</h1>

        <!-- Stats -->
        <div class="stats">
            <div class="card">
                <h3>Total Revenue</h3>
                <p>₱<?= number_format($stats['total_revenue'], 2) ?></p>
                <small>Based on all bookings</small>
            </div>

            <div class="card">
                <h3>Total Bookings</h3>
                <p><?= $stats['total_bookings'] ?></p>
                <small>All recorded bookings</small>
            </div>

            <div class="card">
                <h3>Upcoming Check-ins</h3>
                <p><?= $stats['upcoming_checkins'] ?></p>
                <small>Within 7 days</small>
            </div>

            <div class="card">
                <h3>Available Rooms</h3>
                <p><?= $stats['available_rooms'] ?></p>
                <small>Rooms ready for booking</small>
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
                        <?php foreach ($bookings as $b): ?>
                            <?php
                            $bookingStatus = strtolower($b['booking_status'] ?? 'pending');
                            $paymentMethod = strtolower($b['PaymentMethod'] ?? 'cash');
                            $paymentStatus = ($paymentMethod === 'gcash') ? 'paid' : 'pending';
                            $confirmDisabled = ($bookingStatus === 'confirmed' || $bookingStatus === 'canceled');
                            ?>
                            <tr>
                                <td><?= $b['BookingID'] ?></td>
                                <td><?= htmlspecialchars($b['GuestName'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($b['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= $b['CheckIn'] ?? 'N/A' ?></td>
                                <td><?= $b['CheckOut'] ?? 'N/A' ?></td>
                                <td><span class="status <?= $bookingStatus ?>"><?= ucfirst($bookingStatus) ?></span></td>
                                <td><span class="payment <?= $paymentStatus ?>"><?= ucfirst($paymentStatus) ?></span></td>
                                <td><?= ucfirst($b['PaymentMethod'] ?? 'Cash') ?></td>
                                <td>₱<?= number_format($b['TotalAmount'] ?? 0, 2) ?></td>
                                <td class="actions">
                                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=confirm&id=<?= $b['BookingID'] ?>"
                                        class="btn-confirm"
                                        <?php if ($confirmDisabled) echo 'style="pointer-events:none; opacity:0.5;"'; ?>>
                                        Confirm
                                    </a>
                                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=delete&id=<?= $b['BookingID'] ?>"
                                        class="btn-delete"
                                        onclick="return confirm('Are you sure you want to delete this booking?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center;">No bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?controller=admin&action=index&page=<?= $i ?>"
                            class="<?= ($i === $page) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>