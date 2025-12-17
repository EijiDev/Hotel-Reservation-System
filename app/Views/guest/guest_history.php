<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authorization check - Allow both admin and guest_staff
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'guest_staff'])) {
    header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
    exit;
}
?>

<link rel="stylesheet" href="./css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">

<style>
    /* Loyalty Badge Styles */
    .loyalty-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        display: inline-block;
        min-width: 70px;
        text-align: center;
    }

    .loyalty-badge.gold {
        background: #ffd700;
        color: #5a4500;
    }

    .loyalty-badge.silver {
        background: #c0c0c0;
        color: #333;
    }

    .loyalty-badge.bronze {
        background: #cd7f32;
        color: #fff;
    }

    .loyalty-badge.none {
        background: #eee;
        color: #777;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        justify-content: center;
        align-items: center;
        z-index: 999;
    }

    .modal-content {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        width: 420px;
        max-width: 90%;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-confirm {
        background: #28a745;
        color: #fff;
        border: none;
        padding: 8px 14px;
        cursor: pointer;
        border-radius: 4px;
    }

    .btn-cancel {
        background: #ccc;
        border: none;
        padding: 8px 14px;
        cursor: pointer;
        border-radius: 4px;
    }

    .btn-view {
        background: #007bff;
        color: #fff;
        border: none;
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
    }

    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }

        #receiptArea,
        #receiptArea * {
            visibility: visible;
        }

        #receiptArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
            padding: 40px;
            box-sizing: border-box;
        }

        .modal {
            background: none !important;
            position: static !important;
        }

        .no-print {
            display: none !important;
        }

        #receiptArea h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #000;
        }

        #receiptArea p {
            font-size: 14px;
            line-height: 1.8;
            margin: 10px 0;
            color: #000;
        }

        #receiptArea hr {
            border: none;
            border-top: 2px solid #333;
            margin: 20px 0;
        }

        #receiptArea strong {
            font-weight: 600;
            display: inline-block;
            width: 120px;
        }

        @page {
            margin: 1cm;
        }
    }
</style>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Guest Staff Panel</h2>
            <ul>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=guest&action=reservations" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-calendar-check"></i> Reservations
                    </a>
                </li>
                <li class="dashboard-bar">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=guest&action=currentGuests" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-users"></i> Current Guests
                    </a>
                </li>
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=guest&action=history" style="color: #fff; text-decoration: none; display: block;">
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
        <h1>Guest History</h1>

        <!-- Stats -->
        <div class="stats" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 35px;">
            <div class="card">
                <h3>Total Checked-out Guests</h3>
                <p><?= $stats['total_history'] ?? 0 ?></p>
                <small>All time</small>
            </div>

            <div class="card">
                <h3>This Month</h3>
                <p><?= $stats['this_month'] ?? 0 ?></p>
                <small>Checked-out this month</small>
            </div>

            <div class="card">
                <h3>This Week</h3>
                <p><?= $stats['this_week'] ?? 0 ?></p>
                <small>Checked-out this week</small>
            </div>
        </div>

        <div class="manage-bookings">
            <h2>Guest Check-out History</h2>

            <!-- Search Bar -->
            <input
                type="text"
                id="guestHistorySearch"
                placeholder="Search by Name, Email, Room, Location, Date..."
                style="width:300px; padding:8px; margin-bottom:15px; border:1px solid #ddd; border-radius:4px;">

            <table id="guestHistoryTable">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Room Type</th>
                        <th>Room No.</th>
                        <th>Location</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                        <th>Past Visits</th>
                        <th>Loyalty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($guestHistory)): ?>
                        <?php foreach ($guestHistory as $guest): ?>
                            <tr>
                                <td class="g-booking-id"><?= htmlspecialchars($guest['BookingID'] ?? 'N/A') ?></td>
                                <td class="g-name"><?= htmlspecialchars($guest['Name'] ?? 'Unknown') ?></td>
                                <td class="g-email"><?= htmlspecialchars($guest['Email'] ?? 'N/A') ?></td>
                                <td class="g-room-type"><?= htmlspecialchars($guest['RoomType'] ?? 'Unknown') ?></td>
                                <td class="g-room-no"><?= htmlspecialchars($guest['RoomNumber'] ?? 'N/A') ?></td>
                                <td class="g-location">
                                    <?php
                                    $location = [];
                                    if (!empty($guest['City'])) $location[] = $guest['City'];
                                    if (!empty($guest['Province'])) $location[] = $guest['Province'];
                                    echo htmlspecialchars(implode(', ', $location) ?: 'N/A');
                                    ?>
                                </td>
                                <td class="g-checkin"><?= $guest['CheckedInAt'] ? date('M d, Y h:i A', strtotime($guest['CheckedInAt'])) : 'N/A' ?></td>
                                <td class="g-checkout"><?= $guest['CheckedOutAt'] ? date('M d, Y h:i A', strtotime($guest['CheckedOutAt'])) : 'N/A' ?></td>
                                <td class="g-visits"><?= htmlspecialchars($guest['PastVisits'] ?? 0) ?></td>
                                <td>
                                    <?php
                                    $visits = (int)($guest['PastVisits'] ?? 0);

                                    if ($visits >= 9) {
                                        $loyalty = 'Gold';
                                        $class = 'gold';
                                    } elseif ($visits >= 4) {
                                        $loyalty = 'Silver';
                                        $class = 'silver';
                                    } elseif ($visits >= 3) {
                                        $loyalty = 'Bronze';
                                        $class = 'bronze';
                                    } else {
                                        $loyalty = 'None';
                                        $class = 'none';
                                    }
                                    ?>
                                    <span class="loyalty-badge <?= $class ?>">
                                        <?= $loyalty ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn-view view-receipt-btn">
                                        <i class="fa-solid fa-receipt"></i> View Receipt
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center; padding:40px; color:#999;">
                                No guest history found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?controller=guest&action=history&page=<?= $i ?>"
                            class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal" id="receiptModal">
        <div class="modal-content" id="receiptArea">
            <div style="text-align: center; margin-bottom: 30px;">
                <h3 style="margin: 0; font-size: 22px; color: #333;">Hotel Lunera</h3>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Checkout Receipt</p>
            </div>

            <hr style="border: none; border-top: 2px solid #ddd; margin: 20px 0;">

            <div style="margin: 15px 0;">
                <p style="margin: 8px 0;"><strong>Booking ID:</strong> <span id="mBookingId"></span></p>
                <p style="margin: 8px 0;"><strong>Guest Name:</strong> <span id="mName"></span></p>
                <p style="margin: 8px 0;"><strong>Email:</strong> <span id="mEmail"></span></p>
                <p style="margin: 8px 0;"><strong>Room:</strong> <span id="mRoom"></span></p>
                <p style="margin: 8px 0;"><strong>Location:</strong> <span id="mLocation"></span></p>
                <p style="margin: 8px 0;"><strong>Check-in:</strong> <span id="mCheckin"></span></p>
                <p style="margin: 8px 0;"><strong>Check-out:</strong> <span id="mCheckout"></span></p>
                <p style="margin: 8px 0;"><strong>Past Visits:</strong> <span id="mVisits"></span></p>
            </div>

            <hr style="border: none; border-top: 2px solid #ddd; margin: 20px 0;">

            <p style="text-align:center; font-size:12px; color: #888; margin-top: 30px;">
                Thank you for staying with us!<br>
                We hope to see you again soon.
            </p>

            <div class="modal-actions no-print">
                <button class="btn-cancel" onclick="closeModal()">Close</button>
                <button class="btn-confirm" onclick="printReceipt()">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('guestHistorySearch').addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            const rows = document.querySelectorAll('#guestHistoryTable tbody tr');

            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
            });
        });

        // View Receipt functionality
        document.querySelectorAll('.view-receipt-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');

                document.getElementById('mBookingId').textContent = row.querySelector('.g-booking-id').textContent;
                document.getElementById('mName').textContent = row.querySelector('.g-name').textContent;
                document.getElementById('mEmail').textContent = row.querySelector('.g-email').textContent;
                document.getElementById('mRoom').textContent =
                    row.querySelector('.g-room-type').textContent + ' #' +
                    row.querySelector('.g-room-no').textContent;
                document.getElementById('mLocation').textContent = row.querySelector('.g-location').textContent;
                document.getElementById('mCheckin').textContent = row.querySelector('.g-checkin').textContent;
                document.getElementById('mCheckout').textContent = row.querySelector('.g-checkout').textContent;
                document.getElementById('mVisits').textContent = row.querySelector('.g-visits').textContent;

                document.getElementById('receiptModal').style.display = 'flex';
            });
        });

        function closeModal() {
            document.getElementById('receiptModal').style.display = 'none';
        }

        function printReceipt() {
            window.print();
        }

        // Close modal when clicking outside
        document.getElementById('receiptModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Keyboard support - ESC to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('receiptModal').style.display === 'flex') {
                closeModal();
            }
        });
    </script>
</body>