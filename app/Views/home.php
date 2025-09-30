<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lunera Hotel and Grill</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include "layouts/navigation.php"; ?>

    <section class="hero-section">
        <div class="hero-container">
            <h1 class="main-header">Welcome to Lunera Hotel and Grill</h1>
            <p>Where comfort meets flavor, and every stay feels like home.</p>
            <button id="view-btn">View Rooms</button>
            <button id="signup-btn">Sign up</button>
        </div>
    </section>

    <main>
        <?php
        include "rooms.php";
        include "contact.php";
        ?>
    </main>

    <?php include "layouts/footer.php"?>

</body>
</html>
