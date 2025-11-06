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
                <th>ID</th><th>Guest</th><th>Room Type</th><th>Check-in</th>
                <th>Check-out</th><th>Payment</th><th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><?= $b['BookingID'] ?></td>
                    <td><?= htmlspecialchars($b['GuestName']) ?></td>
                    <td><?= htmlspecialchars($b['RoomType']) ?></td>
                    <td><?= $b['CheckIn'] ?></td>
                    <td><?= $b['CheckOut'] ?></td>
                    <td>
                        <span class="payment <?= strtolower($b['PaymentStatus']) ?>">
                            <?= ucfirst($b['PaymentStatus']) ?>
                        </span>
                    </td>
                    <td>₱<?= number_format($b['TotalAmount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>