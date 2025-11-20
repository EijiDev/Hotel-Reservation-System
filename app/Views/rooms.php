<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/rooms.style.css">

<!-- Rooms Section -->
<section class="rooms">
    <h1 class="about-header">Discover Our Best Rooms</h1>
    <p class="subtitle">Explore our selection of top-rated rooms for an exceptional stay.</p>

    <div class="room-grid">
        <?php if (!empty($rooms)): ?>
            <?php
            // Limit to first 6 rooms
            $roomsToShow = array_slice($rooms, 0, 6);
            ?>
            <?php foreach ($roomsToShow as $room): ?>
                <div class="room-card">
                    <img src="./assets/<?= htmlspecialchars($room['image']) ?>"
                         alt="<?= htmlspecialchars($room['name']) ?>">
                    <div class="card-content">
                        <div class="card-header">
                            <h2><?= htmlspecialchars($room['name']) ?></h2>
                            <span class="rating"><?= htmlspecialchars($room['rating']) ?></span>
                        </div>
                        <p class="card-details">
                            Floor <?= htmlspecialchars($room['floor']) ?> |
                            Room ID: <?= htmlspecialchars($room['room_id']) ?>
                        </p>
                        <div class="availability">
                            <?php if ($room['availability'] === 'Available'): ?>
                                <i class="fa-solid fa-check available-icon" style="color: green;"></i>
                                <p style="color: green;">Available</p>
                            <?php else: ?>
                                <i class="fa-solid fa-times unavailable-icon" style="color: red;"></i>
                                <p style="color: red;">Not Available</p>
                            <?php endif; ?>
                        </div>
                        <p class="card-description"><?= htmlspecialchars($room['description']) ?></p>

                        <div class="features">
                            <?php if (!empty($room['amenities'])): ?>
                                <?php
                                $amenities = explode(',', $room['amenities']);
                                $icons = [
                                    'WiFi' => 'fa-wifi',
                                    'Parking' => 'fa-square-parking',
                                    'Gym' => 'fa-dumbbell',
                                    'Utensils' => 'fa-utensils'
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
                                ₱<?= number_format($room['price'], 2) ?><span class="card-day">/night</span>
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
                <p>No rooms available at the moment. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<script src="/Hotel_Reservation_System/app/public/js/modal.js"></script>
