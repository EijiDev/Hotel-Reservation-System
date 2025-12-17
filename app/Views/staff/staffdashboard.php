<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Hotel_Reservation_System/app/public/css/dashboard.style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="/Hotel_Reservation_System/app/public/assets/Lunera-Logo.png" type="image/ico">
    <title>Staff Dashboard - Bookings</title>
    <style>
        /* Alert Styles */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

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

    <!-- Main Content -->
    <div class="main">
        <h1>Staff Dashboard</h1>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

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
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <form method="GET" action="/Hotel_Reservation_System/app/public/index.php" id="filterForm">
                <input type="hidden" name="controller" value="staff">
                <input type="hidden" name="action" value="index">

                <div class="filters-row">
                    <div class="filter-group search-box">
                        <label for="search">Search</label>
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="search" name="search" placeholder="Guest name or ID..."
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>

                    <div class="filter-group">
                        <label for="booking_status">Status</label>
                        <select id="booking_status" name="booking_status">
                            <option value="">All Status</option>
                            <option value="pending"
                                <?= ($_GET['booking_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed"
                                <?= ($_GET['booking_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed
                            </option>
                            <option value="checked-in"
                                <?= ($_GET['booking_status'] ?? '') === 'checked-in' ? 'selected' : '' ?>>Checked-in
                            </option>
                            <option value="checked-out"
                                <?= ($_GET['booking_status'] ?? '') === 'checked-out' ? 'selected' : '' ?>>Checked-out
                            </option>
                            <option value="cancelled"
                                <?= ($_GET['booking_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="payment_status">Payment</label>
                        <select id="payment_status" name="payment_status">
                            <option value="">All Payments</option>
                            <option value="pending"
                                <?= ($_GET['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="completed"
                                <?= ($_GET['payment_status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed
                            </option>
                            <option value="failed"
                                <?= ($_GET['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="sort_by">Sort By</label>
                        <select id="sort_by" name="sort_by">
                            <option value="checkin_desc"
                                <?= ($_GET['sort_by'] ?? 'checkin_desc') === 'checkin_desc' ? 'selected' : '' ?>>
                                Check-in (Newest)</option>
                            <option value="checkin_asc"
                                <?= ($_GET['sort_by'] ?? '') === 'checkin_asc' ? 'selected' : '' ?>>Check-in (Oldest)
                            </option>
                            <option value="total_desc"
                                <?= ($_GET['sort_by'] ?? '') === 'total_desc' ? 'selected' : '' ?>>Total (Highest)
                            </option>
                            <option value="total_asc"
                                <?= ($_GET['sort_by'] ?? '') === 'total_asc' ? 'selected' : '' ?>>Total (Lowest)
                            </option>
                            <option value="guest_name"
                                <?= ($_GET['sort_by'] ?? '') === 'guest_name' ? 'selected' : '' ?>>Guest Name (A-Z)
                            </option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=index"
                            class="btn-reset">
                            <i class="fa-solid fa-rotate-right"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="manage-bookings">
            <div class="results-info">
                <div class="results-count">
                    <i class="fa-solid fa-list"></i>
                    Showing <?= count($bookings ?? []) ?> booking(s)
                    <?php if (!empty($_GET['search']) || !empty($_GET['booking_status']) || !empty($_GET['payment_status'])): ?>
                        (Filtered)
                    <?php endif; ?>
                </div>
            </div>

            <table id="bookingsTable">
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
                                <td><?= date('M d, Y', strtotime($b['CheckIn'])) ?></td>
                                <td><?= date('M d, Y', strtotime($b['CheckOut'])) ?></td>
                                <td><span class="status <?= $bookingStatus ?>"><?= ucfirst($bookingStatus) ?></span></td>
                                <td><span class="payment <?= $paymentStatus ?>"><?= ucfirst($paymentStatus) ?></span></td>
                                <td><?= ucfirst($paymentMethod) ?></td>
                                <td>₱<?= number_format($displayTotal, 2) ?></td>
                                <td class="actions">
                                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=confirm&id=<?= $b['BookingID'] ?>"
                                        class="btn-confirm confirm-btn"
                                        data-booking-id="<?= $b['BookingID'] ?>"
                                        data-status="<?= $bookingStatus ?>"
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
                    <?php
                    // Preserve filter parameters in pagination
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?= $queryString ?>&page=<?= $i ?>" class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Alert function
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alertContainer');
            const icons = {
                error: 'fa-circle-exclamation',
                success: 'fa-circle-check',
                warning: 'fa-triangle-exclamation'
            };

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
            <i class="fa-solid ${icons[type]}"></i>
            <span>${message}</span>
        `;

            alertContainer.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-10px)';
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }

        // Confirm button validation
        document.querySelectorAll('.confirm-btn:not([style*="pointer-events"])').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const status = this.dataset.status;

                if (status === 'confirmed') {
                    e.preventDefault();
                    showAlert('This booking is already confirmed', 'warning');
                    return false;
                }

                if (status === 'cancelled') {
                    e.preventDefault();
                    showAlert('Cannot confirm a cancelled booking', 'error');
                    return false;
                }

                if (status === 'checked-out') {
                    e.preventDefault();
                    showAlert('This booking is already completed', 'warning');
                    return false;
                }
            });
        });

        // Check for URL parameters
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('success')) {
                const successType = urlParams.get('success');
                const messages = {
                    'confirmed': 'Booking confirmed successfully!'
                };
                showAlert(messages[successType] || 'Operation completed successfully!', 'success');

                // Remove success parameter from URL without refreshing
                urlParams.delete('success');
                const newUrl = urlParams.toString() ?
                    `${window.location.pathname}?${urlParams.toString()}` :
                    window.location.pathname;
                window.history.replaceState({}, '', newUrl);
            } else if (urlParams.has('error')) {
                const errorType = urlParams.get('error');
                const messages = {
                    'already_confirmed': 'This booking is already confirmed',
                    'confirm_failed': 'Failed to confirm booking. Please try again.'
                };
                showAlert(messages[errorType] || 'An error occurred', 'error');

                // Remove error parameter from URL without refreshing
                urlParams.delete('error');
                const newUrl = urlParams.toString() ?
                    `${window.location.pathname}?${urlParams.toString()}` :
                    window.location.pathname;
                window.history.replaceState({}, '', newUrl);
            }
        });
    </script>
    <script src="../public/js/dashboardFiltering.js"></script>
</body>

</html>