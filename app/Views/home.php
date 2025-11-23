<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<meta charset="UTF-8">
<title>Lunera Hotel and Grill - Rooms</title>
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">

<!-- Main styles that set font -->
<link rel="stylesheet" href="/Hotel_Reservation_System/app/public/css/style.css">

<!-- Rooms specific styles -->
<link rel="stylesheet" href="/Hotel_Reservation_System/app/public/css/rooms.style.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
    <?php include "layouts/navigation.php"; ?>
    <section class="hero-section" id="hero">
        <div class="hero-container">
            <h1 class="main-header">Welcome to Lunera Hotel and Grill</h1>
            <p>Where comfort meets flavor, and every stay feels like home.</p>
            <a href="/Hotel_Reservation_System/app/public/index.php?controller=room&action=availableRooms">View Rooms</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/Hotel_Reservation_System/app/views/signup.php" id="signup-btn">Sign up</a>
            <?php endif; ?>
        </div>
    </section>

    <main>
        <?php
        include "rooms.php";
        include "location.php";
        include "contact.php";
        ?>
    </main>

    <?php include "layouts/footer.php"; ?>
</body>