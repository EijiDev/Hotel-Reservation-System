<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lunera Hotel and Grill</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Hero Section -->
    <section class="hero-section"">
        <div class=" hero-container">
        <h1 class="main-header">Welcome to Lunera Hotel and Grill</h1>
        <p>Where comfort meets flavor, and every stay feels like home.</p>
        <button>View Rooms</button>
        <button>Sign up</button>
        </div>
    </section>

    <main>
        <?php
        include "rooms.php";
        include "contact.php";
        ?>
    </main>
</body>
</html>