<link rel="stylesheet" href="/Hotel_Reservation_System/app/public/css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="/Hotel_Reservation_System/app/public/assets/Lunera-Logo.png" type="image/ico">

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Staff Panel</h2>
            <ul>
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=index" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-book"></i> Bookings
                    </a>
                </li>
                <li class="dashboard-bar">
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

    <!-- Main Content -->
    <div class="main">
        <h1>Staff Dashboard</h1>

        <!-- Stats -->
        <div class="stats">
            <div class="card">
                <h3>Total Revenue</h3>
                <p>₱<?= number_format($stats['total_revenue'] ?? 0, 2) ?></p>
                <small>Based on completed payments</small>
            </div>

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
                            // Calculate total - SAME calculation as userbookings.php
                            $checkin = $b['CheckIn'];
                            $checkout = $b['CheckOut'];

                            // Use ceil() like JavaScript for nights calculation
                            $checkinTimestamp = strtotime($checkin);
                            $checkoutTimestamp = strtotime($checkout);
                            $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
                            $nights = max(1, $nights); // Minimum 1 night

                            $roomPrice = $b['room_price'] ?? 0;
                            $guests = $b['Guests'] ?? 1;
                            $checkinTime = $b['CheckIn_Time'] ?? '14:00';

                            // Room total
                            $roomTotal = $roomPrice * $nights;

                            // Guest fee: ₱300 per additional guest (first guest is free)
                            $guestFee = ($guests > 1) ? ($guests - 1) * 300 : 0;

                            // Extra night fee: ₱500 if check-in time is after 6 PM (18:00)
                            $extraNightFee = 0;
                            if ($checkinTime) {
                                list($hours, $minutes) = explode(':', $checkinTime);
                                $hours = (int)$hours;
                                if ($hours >= 18) {
                                    $extraNightFee = 500;
                                }
                            }

                            // Total = Room + Guest Fee + Extra Night Fee (EXACT same as userbookings.php)
                            $displayTotal = $roomTotal + $guestFee + $extraNightFee;

                            // booking_status comes from booking_status table via JOIN
                            $bookingStatus = strtolower($b['booking_status'] ?? 'pending');

                            // payment_method comes from payments table
                            $paymentMethod = $b['payment_method'] ?? 'Cash';

                            // payment_status comes from payments table
                            $paymentStatus = strtolower($b['payment_status'] ?? 'pending');

                            // Disable confirm button if already confirmed or cancelled
                            $confirmDisabled = in_array($bookingStatus, ['confirmed', 'cancelled', 'checked-in', 'checked-out']);
                            ?>
                            <tr>
                                <td><?= $b['BookingID'] ?></td>
                                <td>
                                    <?= htmlspecialchars($b['GuestName'] ?? 'Unknown') ?>
                                    <?php if ($bookingStatus === 'confirmed' || $bookingStatus === 'checked-in'): ?>
                                        <br><small style="color: #28a745;"><i class="fa fa-user-check"></i> Guest Coming</small>
                                    <?php elseif ($bookingStatus === 'cancelled'): ?>
                                        <br><small style="color: #dc3545;"><i class="fa fa-user-times"></i> Cancelled by User</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($b['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= $b['CheckIn'] ?? 'N/A' ?></td>
                                <td><?= $b['CheckOut'] ?? 'N/A' ?></td>
                                <td><span class="status <?= $bookingStatus ?>"><?= ucfirst($bookingStatus) ?></span></td>
                                <td><span class="payment <?= $paymentStatus ?>"><?= ucfirst($paymentStatus) ?></span></td>
                                <td><?= ucfirst($paymentMethod) ?></td>
                                <td>₱<?= number_format($displayTotal, 2) ?></td>
                                <td class="actions">
                                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=confirm&id=<?= $b['BookingID'] ?>"
                                        class="btn-confirm"
                                        <?php if ($confirmDisabled) echo 'style="pointer-events:none; opacity:0.5;"'; ?>>
                                        Confirm
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
            <?php if (($totalPages ?? 1) > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
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