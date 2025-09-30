<link rel="stylesheet" href="css/nav.css">

<nav class="navbar">
  <div class="navbar-left">
    <a href="/index.php?controller=home&action=index">
      <img alt="lunera" class="logo">
    </a>
    <span class="brand">Logo</span>
  </div>

  <ul class="navbar-menu">
    <li><a href="#" class="nav-link">Home</a></li>
    <li><a href="#" class="nav-link">Rooms</a></li>
    <li><a href="#" class="nav-link">Contact</a></li>
  </ul>

  <div class="navbar-actions">
    <a href="../views/signup.php" class="btn-outline">Sign up</a>
    <a href="../views/login.php" class="btn-gradient">Log in</a>
  </div>
</nav>











<script>
  window.addEventListener("scroll", function() {
    const navbar = document.querySelector(".navbar");
    if (window.scrollY > 50) {
      navbar.classList.add("transparent");
    } else {
      navbar.classList.remove("transparent");
    }
  });
</script>