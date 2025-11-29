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
                                // Calculate total
                                $checkin = $booking['CheckIn'];
                                $checkout = $booking['CheckOut'];
                                
                                $checkinTimestamp = strtotime($checkin);
                                $checkoutTimestamp = strtotime($checkout);
                                $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
                                $nights = max(1, $nights); // Minimum 1 night
                                
                                $roomPrice = $booking['room_price'] ?? 0;
                                $guests = $booking['Guests'] ?? 1;
                                $checkinTime = $booking['CheckIn_Time'] ?? '14:00';
                                
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
                                
                                $totalAmount = $roomTotal + $guestFee + $extraNightFee;
                                
                                $bookingStatus = strtolower($booking['booking_status'] ?? 'pending');
                                
                                $paymentMethod = $booking['payment_method'] ?? 'Cash';
                                
                                $paymentStatus = strtolower($booking['payment_status'] ?? 'pending');
                                
                                $confirmDisabled = in_array($bookingStatus, ['confirmed', 'cancelled', 'checked-in', 'checked-out']);
                                ?>
                                <tr>
                                    <td><?= $booking['BookingID'] ?></td>
                                    <td><?= htmlspecialchars($booking['GuestName'] ?? 'Unknown') ?></td>
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