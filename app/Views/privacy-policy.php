<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Lunera Hotel and Grill</title>
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
    <link rel="stylesheet" href="../public/css/footer.style.css">
    <link rel="stylesheet" href="../public/css/policy-pages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "layouts/navigation.php"; ?>

    <div class="policy-container">
        <div class="policy-header">
            <i class="fas fa-shield-alt"></i>
            <h1>Privacy Policy</h1>
            <p>Last Updated: <?= date('F d, Y') ?></p>
        </div>

        <div class="policy-content">
            <section class="policy-section">
                <h2>1. Information We Collect</h2>
                <p>When you use our hotel reservation system, we collect:</p>
                <ul>
                    <li>Name and email address</li>
                    <li>Contact number</li>
                    <li>Booking details (check-in/check-out dates, number of guests)</li>
                    <li>Payment information</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>2. How We Use Your Information</h2>
                <p>We use your information to:</p>
                <ul>
                    <li>Process your bookings</li>
                    <li>Send booking confirmations</li>
                    <li>Provide customer support</li>
                    <li>Improve our services</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>3. Information Sharing</h2>
                <p>We do not sell your personal information. We only share your information with payment processors and email service providers necessary to complete your booking.</p>
            </section>

            <section class="policy-section">
                <h2>4. Data Security</h2>
                <p>We protect your information using:</p>
                <ul>
                    <li>Encrypted passwords</li>
                    <li>Secure payment processing</li>
                    <li>Regular security updates</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>5. Cookies</h2>
                <p>We use cookies to remember your login session and improve your experience on our website.</p>
            </section>

            <section class="policy-section">
                <h2>7. Contact Us</h2>
                <p>If you have questions about this Privacy Policy, please contact us:</p>
                <div class="contact-box">
                    <p>Email: hpl78910@gmail.com</p>
                    <p>Phone: +63 955 854 5146</p>
                </div>
            </section>

            <div class="acknowledgment-box">
                <p>By using our service, you agree to this Privacy Policy.</p>
            </div>
        </div>
    </div>

    <?php include "layouts/footer.php"; ?>
</body>
</html>