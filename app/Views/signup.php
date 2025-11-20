<link rel="stylesheet" href="../public/css/signup.css">
<link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
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

        <!-- Error Message -->
        <?php if (isset($_GET['error']) && isset($_SESSION['signup_error'])) : ?>
          <div class="error-message">
            <?= htmlspecialchars($_SESSION['signup_error']) ?>
          </div>
          <?php unset($_SESSION['signup_error']); ?>
        <?php endif; ?>

        <form class="signup-form" id="signupForm" method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=signup&action=signup" novalidate>
          <div class="form-group" id="nameGroup">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="e.g. Fernando Junio" required />
            <span class="field-error" id="nameError"></span>
          </div>

          <div class="form-group" id="emailGroup">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="fernando12@gmail.com" required />
            <span class="field-error" id="emailError"></span>
          </div>

          <div class="form-group" id="passwordGroup">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter a strong password" required />
            <span class="field-error" id="passwordError"></span>
            <div class="password-strength" id="strengthMeter" style="display: none;">
              <div class="strength-bar">
                <div class="strength-fill"></div>
              </div>
              <span id="strengthText"></span>
            </div>
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

  <script>
    const form = document.getElementById('signupForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Real-time validation
    nameInput.addEventListener('blur', validateName);
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('input', validatePassword);
    
    // Form submission
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const isNameValid = validateName();
      const isEmailValid = validateEmail();
      const isPasswordValid = validatePassword();
      
      if (isNameValid && isEmailValid && isPasswordValid) {
        form.submit();
      }
    });
    
    function validateName() {
      const name = nameInput.value.trim();
      const nameGroup = document.getElementById('nameGroup');
      const nameError = document.getElementById('nameError');
      
      if (!name) {
        showError(nameGroup, nameError, 'Name is required');
        return false;
      }
      
      if (name.length < 2) {
        showError(nameGroup, nameError, 'Name must be at least 2 characters');
        return false;
      }
      
      if (name.length > 100) {
        showError(nameGroup, nameError, 'Name is too long');
        return false;
      }
      
      if (!/^[a-zA-Z\s\-'\.]+$/.test(name)) {
        showError(nameGroup, nameError, 'Name can only contain letters, spaces, and hyphens');
        return false;
      }
      
      if (/\s{2,}/.test(name)) {
        showError(nameGroup, nameError, 'Name contains excessive spaces');
        return false;
      }
      
      clearError(nameGroup, nameError);
      return true;
    }
    
    function validateEmail() {
      const email = emailInput.value.trim();
      const emailGroup = document.getElementById('emailGroup');
      const emailError = document.getElementById('emailError');
      
      if (!email) {
        showError(emailGroup, emailError, 'Email is required');
        return false;
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        showError(emailGroup, emailError, 'Please enter a valid email address');
        return false;
      }
      
      if (email.length > 255) {
        showError(emailGroup, emailError, 'Email is too long');
        return false;
      }
      
      clearError(emailGroup, emailError);
      return true;
    }
    
    function validatePassword() {
      const password = passwordInput.value;
      const passwordGroup = document.getElementById('passwordGroup');
      const passwordError = document.getElementById('passwordError');
      const strengthMeter = document.getElementById('strengthMeter');
      const strengthText = document.getElementById('strengthText');
      
      if (!password) {
        showError(passwordGroup, passwordError, 'Password is required');
        strengthMeter.style.display = 'none';
        return false;
      }
      
      strengthMeter.style.display = 'block';
      
      if (password.length < 8) {
        showError(passwordGroup, passwordError, 'Password must be at least 8 characters');
        updateStrength('weak', 'Weak');
        return false;
      }
      
      let strength = 0;
      const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
      };
      
      for (let key in checks) {
        if (checks[key]) strength++;
      }
      
      if (!checks.uppercase) {
        showError(passwordGroup, passwordError, 'Password must contain at least one uppercase letter');
        updateStrength('weak', 'Weak');
        return false;
      }
      
      if (!checks.lowercase) {
        showError(passwordGroup, passwordError, 'Password must contain at least one lowercase letter');
        updateStrength('fair', 'Fair');
        return false;
      }
      
      if (!checks.number) {
        showError(passwordGroup, passwordError, 'Password must contain at least one number');
        updateStrength('fair', 'Fair');
        return false;
      }
      
      if (!checks.special) {
        showError(passwordGroup, passwordError, 'Password must contain at least one special character');
        updateStrength('good', 'Good');
        return false;
      }
      
      clearError(passwordGroup, passwordError);
      updateStrength('strong', 'Strong');
      return true;
    }
    
    function updateStrength(level, text) {
      const strengthMeter = document.getElementById('strengthMeter');
      const strengthText = document.getElementById('strengthText');
      
      strengthMeter.className = 'password-strength strength-' + level;
      strengthText.textContent = 'Password strength: ' + text;
    }
    
    function showError(group, errorElement, message) {
      group.classList.add('error');
      errorElement.textContent = message;
    }
    
    function clearError(group, errorElement) {
      group.classList.remove('error');
      errorElement.textContent = '';
    }
  </script>
</body>