<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Hotel Reservation System</title>
    <link rel="stylesheet" href="../public/css/signup.css">
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
    
    <style>
        .error-message {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .field-error {
            color: #c33;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }
        
        .form-group.error input {
            border-color: #c33;
        }
        
        .password-strength {
            margin-top: 8px;
            font-size: 13px;
        }
        
        .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }
        
        .strength-weak .strength-fill { width: 25%; background: #f44336; }
        .strength-fair .strength-fill { width: 50%; background: #ff9800; }
        .strength-good .strength-fill { width: 75%; background: #2196f3; }
        .strength-strong .strength-fill { width: 100%; background: #4caf50; }
    </style>
</head>
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
<script src="../public/js/signupvalidation.js"></script>
</body>
</html>