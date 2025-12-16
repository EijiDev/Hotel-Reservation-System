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

    /* Form Validation Styles */
    .form-error {
        border: 2px solid #dc3545 !important;
    }

    .error-message {
        color: #dc3545;
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    /* ID Image Preview Styles */
    .id-preview-section {
        margin: 15px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .id-preview-section h4 {
        margin: 0 0 10px 0;
        color: #495057;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .id-image-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 10px auto;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .id-image-container img {
        width: 100%;
        height: auto;
        display: block;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .id-image-container img:hover {
        transform: scale(1.02);
    }

    .id-type-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #007bff;
        color: white;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 5px;
    }

    .id-not-available {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
    }

    .view-fullsize-btn {
        display: block;
        margin: 10px auto 0;
        padding: 8px 16px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.3s;
    }

    .view-fullsize-btn:hover {
        background: #0056b3;
    }

    /* Lightbox for full-size ID image */
    .id-lightbox {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        align-items: center;
        justify-content: center;
    }

    .id-lightbox-content {
        max-width: 90%;
        max-height: 90%;
        position: relative;
    }

    .id-lightbox-content img {
        max-width: 100%;
        max-height: 90vh;
        border-radius: 8px;
    }

    .id-lightbox-close {
        position: absolute;
        top: 20px;
        right: 40px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
    }

    .id-lightbox-close:hover {
        color: #ccc;
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
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=reservations" style="color: #fff; text-decoration: none; display: block;">
                        <i class="fa-solid fa-calendar-check"></i> Reservations
                    </a>
                </li>
                <li class="dashboard-bar">
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
        <h1>Reservations</h1>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <div class="stats" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 35px;">
            <div class="card">
                <h3>Total Reservations</h3>
                <p><?= $stats['total_reservations'] ?? 0 ?></p>
                <small>All active reservations</small>
            </div>

            <div class="card">
                <h3>Pending Bookings</h3>
                <p><?= $stats['pending_bookings'] ?? 0 ?></p>
                <small>Awaiting confirmation</small>
            </div>

            <div class="card">
                <h3>Confirmed Today</h3>
                <p><?= $stats['confirmed_today'] ?? 0 ?></p>
                <small>Bookings confirmed today</small>
            </div>
        </div>

        <div class="manage-bookings">
            <h2>All Reservations</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest Name</th>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-in Time</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Total</th>
                        <th width="14%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reservations)): ?>
                        <?php foreach ($reservations as $r): ?>
                            <?php
                            // Calculate total
                            $checkin = $r['CheckIn'];
                            $checkout = $r['CheckOut'];

                            $checkinTimestamp = strtotime($checkin);
                            $checkoutTimestamp = strtotime($checkout);
                            $nights = (int)ceil(($checkoutTimestamp - $checkinTimestamp) / (60 * 60 * 24));
                            $nights = max(1, $nights);

                            $roomPrice = $r['room_price'] ?? 0;
                            $guests = $r['Guests'] ?? 1;
                            $checkinTime = $r['CheckIn_Time'] ?? '14:00';

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
                            $formattedTime = date('g:i A', strtotime($checkinTime));
                            ?>
                            <tr>
                                <td><?= $r['BookingID'] ?></td>
                                <td><?= htmlspecialchars($r['GuestName'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($r['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= $r['CheckIn'] ?? 'N/A' ?></td>
                                <td><strong><?= $formattedTime ?></strong></td>
                                <td><?= $r['CheckOut'] ?? 'N/A' ?></td>
                                <td><span class="status <?= strtolower($r['StatusName'] ?? 'pending') ?>"><?= ucfirst($r['StatusName'] ?? 'Pending') ?></span></td>
                                <td>
                                    <span class="status <?= strtolower($r['PaymentStatus'] ?? 'pending') ?>">
                                        <?= ucfirst($r['PaymentStatus'] ?? 'Pending') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['PaymentMethod'] ?? 'N/A') ?></td>
                                <td>₱<?= number_format($displayTotal, 2) ?></td>
                                <td class="actions">
                                    <button class="btn-view" type="button" onclick='viewModal(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        View
                                    </button>
                                    <button class="btn-edit" type="button" onclick='editModal(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        Edit
                                    </button>
                                    <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=checkinReservation" style="display: inline-block; margin: 0;" class="checkin-form">
                                        <input type="hidden" name="booking_id" value="<?= $r['BookingID'] ?>">
                                        <button class="btn-confirm checkin-btn" type="button" data-booking-id="<?= $r['BookingID'] ?>" data-status="<?= strtolower($r['StatusName'] ?? 'pending') ?>" data-payment="<?= strtolower($r['PaymentStatus'] ?? 'pending') ?>">
                                            Check-in
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center; padding: 40px; color: #999;">No reservations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?controller=admin&action=reservations&page=<?= $i ?>"
                            class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reservation Details</h3>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div class="modal-body" id="viewBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer" id="viewModalFooter">
                <button type="button" onclick="closeModal('viewModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Reservation</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=admin&action=updateReservation" id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="editId">

                    <label>Name</label>
                    <input type="text" name="guest_name" id="editName" disabled>

                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" disabled>

                    <label>Contact</label>
                    <input type="text" name="contact" id="editContact" disabled>

                    <label>Street <span style="color: #dc3545;">*</span></label>
                    <input type="text" name="street" id="editStreet" required>
                    <span class="error-message" id="errorStreet"></span>

                    <label>Barangay <span style="color: #dc3545;">*</span></label>
                    <input type="text" name="barangay" id="editBarangay" required>
                    <span class="error-message" id="errorBarangay"></span>

                    <label>City <span style="color: #dc3545;">*</span></label>
                    <input type="text" name="city" id="editCity" required>
                    <span class="error-message" id="errorCity"></span>

                    <label>Province <span style="color: #dc3545;">*</span></label>
                    <input type="text" name="province" id="editProvince" required>
                    <span class="error-message" id="errorProvince"></span>

                    <label>Postal Code <span style="color: #dc3545;">*</span></label>
                    <input type="text" name="postal_code" id="editPostalCode" required>
                    <span class="error-message" id="errorPostalCode"></span>

                    <label>Check-in <span style="color: #dc3545;">*</span></label>
                    <input type="date" name="checkin" id="editCheckin" required>
                    <span class="error-message" id="errorCheckin"></span>

                    <label>Check-out <span style="color: #dc3545;">*</span></label>
                    <input type="date" name="checkout" id="editCheckout" required>
                    <span class="error-message" id="errorCheckout"></span>

                    <label>Check-in Time <span style="color: #dc3545;">*</span></label>
                    <input type="time" name="checkin_time" id="editCheckinTime" value="14:00" required>
                    <span class="error-message" id="errorCheckinTime"></span>

                    <label>Guests <span style="color: #dc3545;">*</span></label>
                    <input type="number" name="guests" id="editGuests" min="1" max="10" required>
                    <span class="error-message" id="errorGuests"></span>

                    <label>Booking Status</label>
                    <select name="status" id="editStatus">
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <label>Payment Status</label>
                    <select name="payment_status" id="editPaymentStatus">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" id="saveBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ID Image Lightbox -->
    <div id="idLightbox" class="id-lightbox">
        <span class="id-lightbox-close" onclick="closeLightbox()">&times;</span>
        <div class="id-lightbox-content">
            <img id="lightboxImage" src="" alt="ID Image">
        </div>
    </div>

    <script src="../public/js/reservationModal.js"></script>
    <script>
        function viewModal(data) {
            // Debug logging
            console.log("=== VIEW MODAL DEBUG ===");
            console.log("Full data:", data);
            console.log("IDType:", data.IDType);
            console.log("IDImage:", data.IDImage);

            const viewBody = document.getElementById('viewBody');

            // Build ID section HTML
            let idSectionHTML = '';
            if (data.IDType && data.IDImage && data.IDImage !== 'null' && data.IDImage !== '') {
                console.log("✅ ID data found - building display");
                idSectionHTML = `
                <div class="id-preview-section">
                    <h4>
                        <i class="fa-solid fa-id-card"></i>
                        Guest Identification
                    </h4>
                    <span class="id-type-badge">${data.IDType}</span>
                    <div class="id-image-container">
                        <img src="/Hotel_Reservation_System/uploads/ids/${data.IDImage}" 
                             alt="${data.IDType}" 
                             onclick="openLightbox('/Hotel_Reservation_System/uploads/ids/${data.IDImage}')"
                             onerror="console.error('Image failed to load:', this.src); this.parentElement.innerHTML='<div class=\\'id-not-available\\'>ID image not found</div>'">
                    </div>
                    <button type="button" class="view-fullsize-btn" onclick="openLightbox('/Hotel_Reservation_System/uploads/ids/${data.IDImage}')">
                        <i class="fa-solid fa-expand"></i> View Full Size
                    </button>
                </div>
            `;
            } else {
                console.log("❌ No ID data available");
                idSectionHTML = `
                <div class="id-preview-section">
                    <h4>
                        <i class="fa-solid fa-id-card"></i>
                        Guest Identification
                    </h4>
                    <div class="id-not-available">
                        <i class="fa-solid fa-exclamation-circle"></i> No ID information available
                    </div>
                </div>
            `;
            }

            viewBody.innerHTML = `
            <p><strong>Booking ID:</strong> ${data.BookingID}</p>
            <p><strong>Guest Name:</strong> ${data.GuestName}</p>
            <p><strong>Email:</strong> ${data.Email || 'N/A'}</p>
            <p><strong>Contact:</strong> ${data.Contact || 'N/A'}</p>
            <p><strong>Room Type:</strong> ${data.RoomType}</p>
            <p><strong>Room Number:</strong> ${data.RoomNumber || 'N/A'}</p>
            <p><strong>Check-in:</strong> ${data.CheckIn}</p>
            <p><strong>Check-out:</strong> ${data.CheckOut}</p>
            <p><strong>Guests:</strong> ${data.Guests}</p>
            <p><strong>Status:</strong> <span class="status ${data.StatusName.toLowerCase()}">${data.StatusName}</span></p>
            <p><strong>Payment Status:</strong> <span class="status ${data.PaymentStatus.toLowerCase()}">${data.PaymentStatus}</span></p>
            <p><strong>Payment Method:</strong> ${data.PaymentMethod || 'N/A'}</p>
            ${idSectionHTML}
        `;

            document.getElementById('viewModal').style.display = 'flex';
        }

        // Lightbox functions
        function openLightbox(imageSrc) {
            console.log("Opening lightbox for:", imageSrc);
            document.getElementById('lightboxImage').src = imageSrc;
            document.getElementById('idLightbox').style.display = 'flex';
        }

        function closeLightbox() {
            document.getElementById('idLightbox').style.display = 'none';
        }

        // Close lightbox when clicking outside the image
        document.getElementById('idLightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        // Close lightbox with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });

        // ✅ Check-in button handler with validation
        document.addEventListener('DOMContentLoaded', function() {
            const checkinButtons = document.querySelectorAll('.checkin-btn');

            checkinButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const bookingId = this.getAttribute('data-booking-id');
                    const status = this.getAttribute('data-status');
                    const payment = this.getAttribute('data-payment');

                    // Get the row data for validation
                    const row = this.closest('tr');
                    const guestName = row.cells[1].textContent.trim();
                    const roomType = row.cells[2].textContent.trim();
                    const checkin = row.cells[3].textContent.trim();
                    const checkout = row.cells[5].textContent.trim();

                    console.log('Check-in validation:', {
                        bookingId,
                        status,
                        payment,
                        guestName,
                        roomType,
                        checkin,
                        checkout
                    });
                    const missingFields = [];

                    if (!guestName || guestName === 'Unknown' || guestName === 'N/A') {
                        missingFields.push('Guest Name');
                    }

                    if (!roomType || roomType === 'Unknown' || roomType === 'N/A') {
                        missingFields.push('Room Type');
                    }

                    if (!checkin || checkin === 'N/A') {
                        missingFields.push('Check-in Date');
                    }

                    if (!checkout || checkout === 'N/A') {
                        missingFields.push('Check-out Date');
                    }

                    // Show error if fields are missing
                    if (missingFields.length > 0) {
                        alert('Cannot check-in: Missing Required Information\n\n' +
                            'The following fields are incomplete:\n' +
                            missingFields.map(field => `• ${field}`).join('\n') +
                            '\n\nPlease edit this reservation and fill in all required information before checking in.');
                        return;
                    }

                    // Validate status - only confirmed bookings can be checked in
                    if (status !== 'confirmed') {
                        alert('Only confirmed bookings can be checked in.\n\n' +
                            'This booking is currently: ' + status.toUpperCase() + '\n\n' +
                            'Please confirm this booking first from the Bookings page.');
                        return;
                    }

                    // Validate payment status - must be completed
                    if (payment !== 'completed') {
                        alert('Cannot check-in: Payment Not Completed\n\n' +
                            'Payment Status: ' + payment.toUpperCase() + '\n\n' +
                            'Please ensure the payment is marked as "Completed" before checking in the guest.');
                        return;
                    }

                    // Final confirmation
                    if (confirm(`Check-in Guest: ${guestName}\n` +
                            `Booking #${bookingId}\n\n` +
                            `Room: ${roomType}\n` +
                            `Check-in: ${checkin}\n` +
                            `Check-out: ${checkout}\n\n` +
                            `This will:\n` +
                            `• Move the guest to Current Guests\n` +
                            `• Mark the room as occupied\n` +
                            `• Update booking status to "checked-in"\n\n` +
                            `Proceed with check-in?`)) {
                        console.log('Submitting form for booking:', bookingId);
                        this.closest('form').submit();
                    }
                });
            });
        });
    </script>
</body>