<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/login.style.css">
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
    <title>Login</title>
</head>
<body>
    <div class="login-wrapper">
        <!-- LEFT PANEL -->
        <div class="left-panel">
            <div class="login-left">
                <img src="../public/assets/leftpanel-image.png" alt="Hotel view" class="login-image">
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="login-right">
            <div class="login-card">
                <h1>Welcome Back 👋</h1>
                <p class="subtitle">Enter your credentials to continue</p>

                <!-- Error Message -->
                <?php if (isset($error) && !empty($error)) : ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=login&action=login" class="login-form">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="example123@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="example123" required>
                    </div>

                    <button type="submit" class="login-btn">Log in</button>
                </form>

                <p class="signup-text">
                    Don’t have an account?
                    <a href="/Hotel_Reservation_System/app/public/index.php?controller=signup&action=index">Sign up</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>