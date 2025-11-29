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
    <title>Terms of Service - Lunera Hotel and Grill</title>
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
    <link rel="stylesheet" href="../public/css/policy-pages.css">
    <link rel="stylesheet" href="../public/css/footer.style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include "layouts/navigation.php"; ?>

    <div class="policy-container">
        <div class="policy-header">
            <i class="fas fa-file-contract"></i>
            <h1>Terms of Service</h1>
            <p>Last Updated: <?= date('F d, Y') ?></p>
        </div>

        <div class="policy-content">
            <section class="policy-section">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing and using the Lunera Hotel and Grill reservation system ("Service"), you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our Service.</p>
            </section>

            <section class="policy-section">
                <h2>2. Reservation and Booking</h2>
                <h3>2.1 Account Creation</h3>
                <p>To make a reservation, you must create an account with accurate and complete information. You are responsible for maintaining the confidentiality of your account credentials.</p>

                <h3>2.2 Booking Confirmation</h3>
                <p>All bookings are subject to confirmation by Lunera Hotel and Grill staff. A booking is not guaranteed until you receive confirmation via email.</p>

                <h3>2.3 Age Requirement</h3>
                <p>You must be at least 18 years old to make a reservation. Guests under 18 must be accompanied by a parent or legal guardian.</p>

                <h3>2.4 Accuracy of Information</h3>
                <p>You agree to provide accurate, current, and complete information during the reservation process. We reserve the right to refuse or cancel any reservation if the provided information is found to be inaccurate or fraudulent.</p>
            </section>

            <section class="policy-section">
                <h2>3. Pricing and Payment</h2>
                <h3>3.1 Room Rates</h3>
                <p>All room rates are displayed in Philippine Peso (PHP) and include applicable taxes unless otherwise stated. Rates are subject to change without notice.</p>

                <h3>3.2 Payment Methods</h3>
                <p>We accept Cash, GCash, and Credit Card payments. Payment is due at check-in unless otherwise arranged.</p>

                <h3>3.3 Additional Charges</h3>
                <p>Additional guests beyond the standard occupancy will incur an extra fee of ₱300 per person per night. Any damages, missing items, or additional services used during your stay will be charged accordingly.</p>

                <h3>3.4 Deposit and Guarantees</h3>
                <p>A valid payment method or deposit may be required to guarantee your reservation. This may be used to cover any incidental charges or damages.</p>
            </section>

            <section class="policy-section">
                <h2>4. Cancellation and Modification Policy</h2>
                <h3>4.1 Cancellations</h3>
                <ul>
                    <li><strong>48+ hours before check-in:</strong> Full refund</li>
                    <li><strong>24-48 hours before check-in:</strong> 50% refund</li>
                    <li><strong>Less than 24 hours:</strong> No refund (one-night charge applies)</li>
                    <li><strong>No-show:</strong> Full booking amount charged</li>
                </ul>

                <h3>4.2 Modifications</h3>
                <p>Booking modifications are subject to availability. Changes made within 24 hours of check-in may incur additional fees.</p>

                <h3>4.3 Force Majeure</h3>
                <p>In case of force majeure events (natural disasters, pandemics, government restrictions, etc.), special cancellation terms may apply.</p>
            </section>

            <section class="policy-section">
                <h2>5. Check-in and Check-out</h2>
                <h3>5.1 Standard Times</h3>
                <p>Check-in time: 2:00 PM | Check-out time: 12:00 PM (noon)</p>

                <h3>5.2 Early Check-in / Late Check-out</h3>
                <p>Early check-in and late check-out are subject to availability and may incur additional charges:</p>
                <ul>
                    <li>Early check-in (before 2:00 PM): Subject to availability</li>
                    <li>Late check-out (12:00 PM - 6:00 PM): 50% of nightly rate</li>
                    <li>Late check-out (after 6:00 PM): Full nightly rate</li>
                </ul>

                <h3>5.3 Identification</h3>
                <p>A valid government-issued photo ID is required at check-in. The name on the ID must match the name on the reservation.</p>
            </section>

            <section class="policy-section">
                <h2>6. Guest Conduct and Responsibilities</h2>
                <h3>6.1 Behavior</h3>
                <p>Guests are expected to conduct themselves in a respectful manner. We reserve the right to refuse service or terminate accommodation for guests who:</p>
                <ul>
                    <li>Engage in illegal activities</li>
                    <li>Cause disturbances to other guests</li>
                    <li>Damage hotel property</li>
                    <li>Violate hotel policies</li>
                </ul>

                <h3>6.2 Maximum Occupancy</h3>
                <p>Occupancy must not exceed the maximum number allowed for the booked room type. Additional guests require approval and will incur extra charges.</p>

                <h3>6.3 Smoking Policy</h3>
                <p>Lunera Hotel and Grill is a non-smoking facility. Smoking is only permitted in designated outdoor areas. Violations will result in cleaning fees.</p>

                <h3>6.4 Damages</h3>
                <p>Guests are responsible for any damage caused to the room or hotel property. Repair or replacement costs will be charged to the guest's account.</p>
            </section>

            <section class="policy-section">
                <h2>7. Liability and Disclaimers</h2>
                <h3>7.1 Personal Belongings</h3>
                <p>The hotel is not responsible for loss or damage to personal belongings. We recommend using the in-room safe for valuables.</p>

                <h3>7.2 Service Interruptions</h3>
                <p>We strive to provide uninterrupted services but are not liable for temporary disruptions due to maintenance, repairs, or circumstances beyond our control.</p>

                <h3>7.3 Third-Party Services</h3>
                <p>The hotel may provide access to third-party services or links. We are not responsible for the content, accuracy, or availability of these third-party services.</p>

                <h3>7.4 Medical Emergencies</h3>
                <p>In case of medical emergencies, we will assist in contacting appropriate emergency services, but we are not liable for medical treatment or outcomes.</p>
            </section>

            <section class="policy-section">
                <h2>8. Privacy and Data Protection</h2>
                <p>Your privacy is important to us. Please refer to our <a href="/Hotel_Reservation_System/app/views/privacy-policy.php">Privacy Policy</a> for detailed information about how we collect, use, and protect your personal information.</p>
            </section>

            <section class="policy-section">
                <h2>9. System Usage</h2>
                <h3>9.1 Prohibited Activities</h3>
                <p>You may not:</p>
                <ul>
                    <li>Use the Service for any unlawful purpose</li>
                    <li>Attempt to gain unauthorized access to the system</li>
                    <li>Interfere with or disrupt the Service</li>
                    <li>Use automated systems or software to extract data</li>
                    <li>Impersonate another person or entity</li>
                    <li>Post or transmit malicious code</li>
                </ul>

                <h3>9.2 Account Suspension</h3>
                <p>We reserve the right to suspend or terminate accounts that violate these terms without notice or refund.</p>
            </section>

            <section class="policy-section">
                <h2>10. Intellectual Property</h2>
                <p>All content on this Service, including text, graphics, logos, images, and software, is the property of Lunera Hotel and Grill and is protected by intellectual property laws. You may not reproduce, distribute, or create derivative works without our express written permission.</p>
            </section>

            <section class="policy-section">
                <h2>11. Modifications to Terms</h2>
                <p>We reserve the right to modify these Terms of Service at any time. Changes will be effective immediately upon posting to the Service. Your continued use of the Service after changes constitutes acceptance of the modified terms.</p>
            </section>


            <section class="policy-section">
                <h2>12. Severability</h2>
                <p>If any provision of these Terms of Service is found to be invalid or unenforceable, the remaining provisions shall remain in full force and effect.</p>
            </section>

            <section class="policy-section">
                <h2>13. Entire Agreement</h2>
                <p>These Terms of Service, together with our Privacy Policy, constitute the entire agreement between you and Lunera Hotel and Grill regarding the use of our Service.</p>
            </section>

            <section class="policy-section">
                <h2>14. Contact Information</h2>
                <p>If you have any questions about these Terms of Service, please contact us:</p>
                <div class="contact-box">
                    <p><strong>Lunera Hotel and Grill</strong></p>
                    <p>Email: legal@lunerahotel.com</p>
                    <p>Phone: +63 912 345 6789</p>
                    <p>Address: Baguio City, Philippines</p>
                </div>

                <div class="acknowledgment-box">
                    <p><strong>By using our Service, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</strong></p>
                </div>

            </section>
        </div>
    </div>

    <?php include "layouts/footer.php"; ?>
</body>

</html>