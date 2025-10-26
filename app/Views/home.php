<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<meta charset="UTF-8">
<title>Lunera Hotel and Grill</title>
<head>
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include "layouts/navigation.php"; ?>
    <section class="hero-section">
        <div class="hero-container">
            <h1 class="main-header">Welcome to Lunera Hotel and Grill</h1>
            <p>Where comfort meets flavor, and every stay feels like home.</p>
            <button id="view-btn">View Rooms</button>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <button id="signup-btn" onclick="window.location.href='/Hotel_Reservation_System/app/views/signup.php'">
                    Sign up
                </button>
            <?php endif; ?>
        </div>
    </section>

    <main>
        <?php
        include "rooms.php";
        include "contact.php";
        ?>
    </main>

    <?php include "layouts/footer.php"; ?>
</body>
