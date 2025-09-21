<link rel="stylesheet" href="../public/css/signup.css">

<body>
  <div class="signup-container">
    <h1>Sign Up</h1>
    <form class="form" id="signupForm" method="POST" action="/Hotel_Reservation_System/app/public/index.php?action=signup">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" placeholder="e.g. Fernando Junio" required />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="fernado12@gmail.com" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="e.g. example123" required />

      <button type="submit" class="signup-btn">Sign Up</button>

      <p class="signup-text">
        Already have an account? <a href="../views/login.php">Log in</a>
      </p>
    </form>
  </div>
</body>
