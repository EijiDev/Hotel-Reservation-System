<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: /Hotel_Reservation_System/app/public/index.php?controller=login&action=index&error=unauthorized");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="/Hotel_Reservation_System/app/public/css/dashboard.style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="/Hotel_Reservation_System/app/public/assets/Lunera-Logo.png" type="image/ico">
    <title>Guest History</title>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Staff Panel</h2>
            <ul>
                <li class="dashboard-bar">
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
                <li class="dashboard-bar" style="background: rgba(255,255,255,0.1);">
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

    <div class="main">
        <h1>Guest History</h1>

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

            <table>
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th>Room Type</th>
                        <th>Room No.</th>
                        <th>Location</th>
                        <th>Check-in Date</th>
                        <th>Check-out Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($guestHistory)): ?>
                        <?php foreach ($guestHistory as $guest): ?>
                            <tr>
                                <td><?= htmlspecialchars($guest['Name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($guest['Email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($guest['RoomType'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($guest['RoomNumber'] ?? 'N/A') ?></td>
                                <td>
                                    <?php 
                                    $location = [];
                                    if (!empty($guest['City'])) $location[] = $guest['City'];
                                    if (!empty($guest['Province'])) $location[] = $guest['Province'];
                                    echo htmlspecialchars(implode(', ', $location) ?: 'N/A');
                                    ?>
                                </td>
                                <td><?= $guest['CheckedInAt'] ? date('M d, Y h:i A', strtotime($guest['CheckedInAt'])) : 'N/A' ?></td>
                                <td><?= $guest['CheckedOutAt'] ? date('M d, Y h:i A', strtotime($guest['CheckedOutAt'])) : 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 40px; color: #999;">No guest history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?controller=staff&action=guestHistory&page=<?= $i ?>"
                            class="<?= ($i === ($page ?? 1)) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>