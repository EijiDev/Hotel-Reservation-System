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
                            <!-- Status is now from rooms table (enum: 'available', 'booked') -->
                            <?php if (strtolower($room['Status']) === 'available'): ?>
                                <i class="fa-solid fa-check available-icon" style="color: green;"></i>
                                <p style="color: green;">Available</p>
                            <?php else: ?>
                                <i class="fa-solid fa-times unavailable-icon" style="color: red;"></i>
                                <p style="color: red;">Not Available</p>
                            <?php endif; ?>
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
                <p>No rooms available at the moment. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<script src="/Hotel_Reservation_System/app/public/js/modal.js"></script>