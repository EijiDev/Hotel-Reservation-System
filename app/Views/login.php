<link rel="stylesheet" href="../public/css/login.style.css">
<title>Login</title>

<body>
    <div class="login-container">
        <div class="context">
            <h1>Log in</h1>
            <p>Enter your credentials to access your account</p>
            <form class="form" method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=login&action=login">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="example123@example.com" required />

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="example123" required />

                <button type="submit" class="login-btn">Log in</button>
            </form>

            <p class="signup-text">Don't have an account?
                <a href="/Hotel_Reservation_System/app/public/index.php?controller=signup&action=index">Sign up</a>
            </p>
        </div>
    </div>
</body>

</html>