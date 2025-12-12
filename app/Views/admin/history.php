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


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/dashboard.style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
    <title>Admin Dashboard - Booking History</title>
</head>


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
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=currentGuests" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-users"></i> Current Guests
                    </a>
                </li>
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
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
        <h1>Booking History</h1>


        <!-- Stats - 3 columns -->
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


            <!-- Results Info + Print Button -->
            <div class="results-info">
                <div class="results-count">
                    <i class="fa-solid fa-list"></i>
                    Showing <?= count($archivedBookings ?? []) ?> archived booking(s)
                </div>
                <button class="print-btn" onclick="printReports()">
                    <i class="fa-solid fa-print"></i> Print Reports
                </button>
            </div>


            <table id="historyTable">
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
                            $paymentMethod = $b['payment_method'] ?? 'N/A'; // NEW
                            ?>
                            <tr>
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
                                <td><?= date('M d, Y', strtotime($b['CheckIn'])) ?></td>
                                <td><?= date('M d, Y', strtotime($b['CheckOut'])) ?></td>
                                <td><span class="status <?= $bookingStatus ?>"><?= ucfirst($bookingStatus) ?></span></td>
                                <td><span class="payment <?= $paymentStatus ?>"><?= ucfirst($paymentStatus) ?></span></td>
                                <td><?= htmlspecialchars(ucfirst($paymentMethod)) ?></td> <!-- NEW -->
                                <td style="text-align: right;">₱<?= number_format($displayTotal, 2) ?></td>
                                <td class="actions">
                                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=restore&id=<?= $b['BookingID'] ?>"
                                        class="btn-confirm"
                                        onclick="return confirm('Restore this booking?')">
                                        Restore
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center; padding: 40px;">No archived bookings found.</td> <!-- UPDATED: 10 cols -->
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


    <!-- PRINT PREVIEW CONTAINER -->
    <div id="printPreview" class="print-preview" style="display: none;">
        <div style="text-align: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 3px solid #2c3e50;">
            <h1 style="font-size: 32px; margin: 0 0 10px 0; font-weight: 700; color: #2c3e50;">ARCHIVED BOOKINGS REPORT</h1>
            <p style="font-size: 16px; color: #666; margin: 0; font-weight: 500;">Lunera Hotel System</p>
        </div>


        <div style="margin-bottom: 30px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; border-left: 4px solid #007bff;">
                    <h3 style="font-size: 14px; color: #666; margin: 0 0 10px 0;">Total Archived</h3>
                    <p style="font-size: 28px; font-weight: 700; color: #333; margin: 0;"><?= $stats['total_archived'] ?? 0 ?></p>
                    <small style="color: #999;">All deleted bookings</small>
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; border-left: 4px solid #dc3545;">
                    <h3 style="font-size: 14px; color: #666; margin: 0 0 10px 0;">Cancelled Bookings</h3>
                    <p style="font-size: 28px; font-weight: 700; color: #333; margin: 0;"><?= $stats['cancelled_count'] ?? 0 ?></p>
                    <small style="color: #999;">User cancelled</small>
                </div>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; border-left: 4px solid #28a745;">
                    <h3 style="font-size: 14px; color: #666; margin: 0 0 10px 0;">Completed Bookings</h3>
                    <p style="font-size: 28px; font-weight: 700; color: #333; margin: 0;"><?= $stats['completed_count'] ?? 0 ?></p>
                    <small style="color: #999;">Checked out</small>
                </div>
            </div>
        </div>


        <div id="printTableContent"></div>


        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee; font-size: 14px; color: #666;">
            <p>Generated on <?= date('F d, Y \a\t g:i A') ?> | Lunera Hotel System</p>
            <p style="font-size: 12px; margin-top: 5px;">Page 1 of 1</p>
        </div>
    </div>


    <script>
        function printReports() {
            const table = document.getElementById('historyTable').cloneNode(true);


            // Remove Actions column from header
            const headerCells = table.querySelectorAll('thead th');
            if (headerCells.length > 0) {
                headerCells[headerCells.length - 1].remove();
            }


            // Remove Actions column from body
            const bodyRows = table.querySelectorAll('tbody tr');
            bodyRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    cells[cells.length - 1].remove();
                }
            });


            // Handle "no data" row colspan
            if (bodyRows.length === 1 && bodyRows[0].textContent.includes('No archived bookings')) {
                const onlyCell = bodyRows[0].querySelector('td');
                if (onlyCell) onlyCell.colSpan = headerCells.length;
            }


            const reportHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Archived Bookings Report</title>
            <style>
                body {
                    font-family: "Segoe UI", Arial, sans-serif;
                    margin: 30px;
                    color: #333;
                }
                .report-header {
                    text-align: center;
                    margin-bottom: 25px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #2c3e50;
                }
                .report-header h1 {
                    margin: 0 0 6px 0;
                    font-size: 26px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                }
                .report-header p {
                    margin: 0;
                    font-size: 13px;
                    color: #666;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 11px;
                    margin-top: 10px;
                }
                th, td {
                    padding: 7px 9px;
                    border: 1px solid #ccc;
                    text-align: left;
                    vertical-align: middle;
                }
                th {
                    background: #f4f5f7;
                    font-weight: 600;
                    text-transform: uppercase;
                    font-size: 10px;
                }
                td:nth-child(1),
                td:nth-child(3),
                td:nth-child(4),
                td:nth-child(5) {
                    white-space: nowrap;
                }
                td:nth-child(8) {
                    text-align: right;
                    white-space: nowrap;
                }
                .status, .payment {
                    padding: 3px 6px;
                    border-radius: 10px;
                    font-size: 10px;
                    font-weight: 500;
                    display: inline-block;
                }
                .report-footer {
                    text-align: center;
                    margin-top: 25px;
                    padding-top: 10px;
                    border-top: 1px solid #ddd;
                    font-size: 11px;
                    color: #777;
                }
                @media print {
                    body {
                        margin: 10mm 10mm 12mm 10mm;
                    }
                }
            </style>
        </head>
        <body>
            <div class="report-header">
                <h1>Archived Bookings Report</h1>
                <p>Lunera Hotel System</p>
            </div>
            ${table.outerHTML}
            <div class="report-footer">
                Generated on <?= date('F d, Y \\a\\t g:i A') ?>
            </div>
        </body>
        </html>
    `;


            const printWindow = window.open('', '_blank');
            printWindow.document.write(reportHtml);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
</body>

</html>