<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
    exit;
}
?>

<link rel="stylesheet" href="./css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">

<style>
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.5);
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
.btn-confirm { background:#28a745;color:#fff;border:none;padding:8px 14px;cursor:pointer;border-radius:4px; }
.btn-cancel { background:#ccc;border:none;padding:8px 14px;cursor:pointer;border-radius:4px; }

/* Print Styles */
@media print {
    body * {
        visibility: hidden;
    }
    
    #receiptArea, #receiptArea * {
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

            <!-- Search Bar -->
            <input type="text" id="guestSearch" placeholder="Search guests..." style="width:300px;padding:8px;margin-bottom:15px;border:1px solid #ddd;border-radius:4px;">

            <table id="guestsTable">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Room Type</th>
                        <th>Room No.</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($currentGuests)): ?>
                        <?php foreach ($currentGuests as $guest): ?>
                            <tr>
                                <td class="g-booking-id"><?= htmlspecialchars($guest['BookingID'] ?? 'N/A') ?></td>
                                <td class="g-name"><?= htmlspecialchars($guest['GuestName'] ?? 'Unknown') ?></td>
                                <td class="g-email"><?= htmlspecialchars($guest['Email'] ?? 'N/A') ?></td>
                                <td class="g-contact"><?= htmlspecialchars($guest['Contact'] ?? 'N/A') ?></td>
                                <td class="g-room-type"><?= htmlspecialchars($guest['RoomType'] ?? 'Unknown') ?></td>
                                <td class="g-room-no"><?= htmlspecialchars($guest['RoomNumber'] ?? 'N/A') ?></td>
                                <td class="g-checkin"><?= $guest['CheckIn'] ?? 'N/A' ?></td>
                                <td class="g-checkout"><?= $guest['CheckOut'] ?? 'N/A' ?></td>
                                <td>
                                    <form method="POST"
                                        action="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=checkoutGuest"
                                        class="checkout-form">
                                        <input type="hidden" name="guest_id" value="<?= $guest['GuestID'] ?>">
                                        <input type="hidden" name="booking_id" value="<?= $guest['BookingID'] ?>">
                                        <input type="hidden" name="room_id" value="<?= $guest['RoomID'] ?>">
                                        <button type="button" class="btn-delete checkout-btn">Check-out</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center; padding: 40px; color: #999;">No guests currently checked-in.</td>
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

    <!-- Checkout Modal -->
    <div class="modal" id="checkoutModal">
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
                <p style="margin: 8px 0;"><strong>Contact:</strong> <span id="mContact"></span></p>
                <p style="margin: 8px 0;"><strong>Room:</strong> <span id="mRoom"></span></p>
                <p style="margin: 8px 0;"><strong>Check-in:</strong> <span id="mCheckin"></span></p>
                <p style="margin: 8px 0;"><strong>Check-out:</strong> <span id="mCheckout"></span></p>
            </div>

            <hr style="border: none; border-top: 2px solid #ddd; margin: 20px 0;">
            
            <p style="text-align:center; font-size:12px; color: #888; margin-top: 30px;">
                Thank you for staying with us!<br>
                We hope to see you again soon.
            </p>

            <div class="modal-actions no-print">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-confirm" onclick="printReceipt()">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </button>
                <button class="btn-confirm" id="confirmCheckout">Confirm Checkout</button>
            </div>
        </div>
    </div>

    <script>
    let activeForm = null;

    // Search functionality
    document.getElementById('guestSearch').addEventListener('keyup', function () {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#guestsTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });

    // Checkout button click - show modal
    document.querySelectorAll('.checkout-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            activeForm = this.closest('form');

            document.getElementById('mBookingId').textContent = row.querySelector('.g-booking-id').textContent;
            document.getElementById('mName').textContent = row.querySelector('.g-name').textContent;
            document.getElementById('mEmail').textContent = row.querySelector('.g-email').textContent;
            document.getElementById('mContact').textContent = row.querySelector('.g-contact').textContent;
            document.getElementById('mRoom').textContent =
                row.querySelector('.g-room-type').textContent + ' #' +
                row.querySelector('.g-room-no').textContent;
            document.getElementById('mCheckin').textContent = row.querySelector('.g-checkin').textContent;
            document.getElementById('mCheckout').textContent = row.querySelector('.g-checkout').textContent;

            document.getElementById('checkoutModal').style.display = 'flex';
        });
    });

    // Confirm checkout button
    document.getElementById('confirmCheckout').addEventListener('click', function () {
        if (activeForm) activeForm.submit();
    });

    // Close modal function
    function closeModal() {
        document.getElementById('checkoutModal').style.display = 'none';
    }

    // Print receipt function
    function printReceipt() {
        window.print();
    }

    // Close modal when clicking outside
    document.getElementById('checkoutModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Keyboard support - ESC to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('checkoutModal').style.display === 'flex') {
            closeModal();
        }
    });
    </script>
</body>