<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/rooms.style.css">

    <!-- Rooms Section -->
    <section class="rooms">
        <h1 class="about-header">Discover Our Best Rooms</h1>
        <p class="subtitle">Explore our selection of top-rated rooms for an exceptional stay.</p>

        <div class="room-grid">
            <?php if (!empty($rooms)): ?>
                <?php
                // Filter rooms to only show available ones without existing bookings
                $availableRooms = [];

                foreach ($rooms as $room) {
                    // Check if room status is available
                    if (strtolower($room['Status']) !== 'available') {
                        continue;
                    }

                    // Check if room has any active bookings
                    $hasActiveBookings = false;

                    // Query to check for active bookings on this room
                    $bookingCheckQuery = "
                    SELECT COUNT(*) as booking_count
                    FROM bookings b
                    JOIN booking_status bs ON b.StatusID = bs.StatusID
                    WHERE b.RoomID = ?
                    AND bs.StatusName IN ('pending', 'confirmed', 'checked-in')
                    AND b.IsDeleted = 0
                    AND b.CheckOut >= CURDATE()
                ";

                    // You'll need to inject the DB connection here
                    // This assumes you have $db or $pdo available
                    if (isset($db) || isset($pdo)) {
                        $dbConn = $db ?? $pdo;
                        $stmt = $dbConn->prepare($bookingCheckQuery);
                        $stmt->execute([$room['RoomID']]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($result && $result['booking_count'] > 0) {
                            $hasActiveBookings = true;
                        }
                    }

                    // Only add room if it has no active bookings
                    if (!$hasActiveBookings) {
                        $availableRooms[] = $room;
                    }
                }

                // Limit to first 6 available rooms
                $roomsToShow = array_slice($availableRooms, 0, 6);
                ?>

                <?php if (!empty($roomsToShow)): ?>
                    <?php foreach ($roomsToShow as $room): ?>
                        <div class="room-card">
                            <!-- image is from rooms table -->
                            <img src="./assets/<?= htmlspecialchars($room['image'] ?? 'default-room.jpg') ?>"
                                alt="<?= htmlspecialchars($room['room_name']) ?>">
                            <div class="card-content">
                                <div class="card-header">
                                    <!-- room_name comes from roomtypes table via JOIN -->
                                    <h2><?= htmlspecialchars($room['room_name']) ?></h2>
                                    <span class="rating"><?= htmlspecialchars($room['rating'] ?? '4.5') ?></span>
                                </div>
                                <p class="card-details">
                                    Floor <?= htmlspecialchars($room['Floor']) ?> |
                                    Room #<?= htmlspecialchars($room['RoomNumber'] ?? $room['RoomID']) ?>
                                </p>
                                <div class="availability">
                                    <i class="fa-solid fa-check available-icon" style="color: green;"></i>
                                    <p style="color: green;">Available Now</p>
                                </div>
                                <!-- Description comes from roomtypes table -->
                                <p class="card-description"><?= htmlspecialchars($room['Description']) ?></p>

                                <div class="features">
                                    <!-- Amenities come from roomtypes table -->
                                    <?php if (!empty($room['Amenities'])): ?>
                                        <?php
                                        $amenities = explode(',', $room['Amenities']);
                                        $icons = [
                                            'WiFi' => 'fa-wifi',
                                            'Parking' => 'fa-square-parking',
                                            'Gym' => 'fa-dumbbell',
                                            'Utensils' => 'fa-utensils',
                                            'TV' => 'fa-tv',
                                            'Air Conditioning' => 'fa-snowflake',
                                            'Mini Bar' => 'fa-wine-glass'
                                        ];
                                        ?>
                                        <?php foreach ($amenities as $amenity): ?>
                                            <span class="feature-item">
                                                <i class="fa-solid <?= $icons[trim($amenity)] ?? 'fa-circle' ?>"></i>
                                                <?= htmlspecialchars(trim($amenity)) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <div class="card-price">
                                        <!-- Price comes from roomtypes table -->
                                        ₱<?= number_format($room['Price'], 2) ?><span class="card-day">/night</span>
                                    </div>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="/Hotel_Reservation_System/app/views/roombookings.php?room_id=<?= $room['RoomID'] ?>" class="book-now-btn">Book Now</a>
                                    <?php else: ?>
                                        <a href="../views/login.php" class="book-now-btn">Book Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-rooms">
                        <i class="fa-solid fa-bed" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                        <p>All rooms are currently booked. Please check back later or contact us for availability.</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-rooms">
                    <i class="fa-solid fa-exclamation-circle" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p>No rooms available at the moment. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <script src="/Hotel_Reservation_System/app/public/js/modal.js"></script>
</body>

</html>