<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<link rel="stylesheet" href="/Hotel_Reservation_System/app/public/css/nav.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<nav class="navbar">
  <div class="navbar-left">
    <a href="/Hotel_Reservation_System/app/public/index.php?controller=home&action=index">
      <img src="../public/assets/Lunera-Logo.png" alt="Lunera Hotel Logo" class="logo">
    </a>
  </div>

  <ul class="navbar-menu">
    <li><a href="/Hotel_Reservation_System/app/public/index.php?controller=home&action=index" class="nav-link">Home</a></li>
    <li><a href="/Hotel_Reservation_System/app/views/availablerooms.php" class="nav-link">Rooms</a></li>
    <li><a href="#" class="nav-link">Contact</a></li>

    <?php if (isset($_SESSION['user_id'])): ?>
      <li><a href="/Hotel_Reservation_System/app/public/index.php?controller=user&action=userbookings" class="nav-link">My Bookings</a></li>
    <?php endif; ?>
  </ul>

  <div class="navbar-actions">
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="/Hotel_Reservation_System/app/public/index.php?controller=logout&action=index" class="btn-logout">
        <i class="fa-solid fa-right-from-bracket"></i> Log out
      </a>

    <?php else: ?>
      <a href="/Hotel_Reservation_System/app/views/signup.php" class="btn-outline">Sign up</a>
      <a href="/Hotel_Reservation_System/app/views/login.php" class="btn-gradient">Log in</a>
    <?php endif; ?>
  </div>
</nav>

<script src="/Hotel_Reservation_System/app/public/js/nav.js"></script>