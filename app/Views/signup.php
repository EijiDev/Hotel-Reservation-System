<link rel="stylesheet" href="../public/css/signup.css">
<title>Signup</title>

<body>
  <div class="signup-wrapper">
    <!-- LEFT PANEL -->
    <div class="left-panel">
      <div class="signup-left">
        <img src="../public/assets/leftpanel-image.png" alt="Hotel view" class="signup-image">
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
      <div class="signup-card">
        <h1>Create Account ✨</h1>
        <p class="subtitle">Join us and start your hotel journey today</p>

        <!-- Error Message (optional) -->
        <?php if (isset($error) && !empty($error)) : ?>
          <div class="error-message">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form class="signup-form" id="signupForm" method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=signup&action=signup">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="e.g. Fernando Junio" required />
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="fernando12@gmail.com" required />
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="e.g. example123" required />
          </div>

          <button type="submit" class="signup-btn">Sign Up</button>

          <p class="login-text">
            Already have an account?
            <a href="/Hotel_Reservation_System/app/public/index.php?controller=login&action=index">Log in</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</body>
