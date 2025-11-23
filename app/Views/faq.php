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
    <title>FAQ - Lunera Hotel and Grill</title>
    <link rel="icon" href="../public/assets/Lunera-Logo.png" type="image/ico">
    <link rel="stylesheet" href="../public/css/policy-pages.css">
    <link rel="stylesheet" href="../public/css/footer.style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php include "layouts/navigation.php"; ?>

    <div class="policy-container">
        <div class="policy-header">
            <i class="fas fa-question-circle"></i>
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about booking and staying at Lunera Hotel and Grill</p>
        </div>

        <div class="policy-content">
            <!-- Booking Questions -->
            <section class="faq-section">
                <h2><i class="fas fa-calendar-check"></i> Booking & Reservations</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>How do I make a reservation?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>To make a reservation, simply browse our available rooms, select your preferred room type, choose your check-in and check-out dates, and complete the booking form. You'll need to create an account or log in to proceed with your reservation.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>Can I modify or cancel my booking?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can modify pending bookings through your account dashboard. For cancellations, click the "Cancel" button on your booking. Please note that cancellation policies may apply depending on how close to your check-in date you cancel.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>What happens after I submit a booking?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>After submitting your booking, it will be in "pending" status. Our staff will review and confirm your reservation within 24 hours. You'll receive an email notification once your booking is confirmed.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>How far in advance can I book?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>You can make reservations up to 12 months in advance. We recommend booking early, especially during peak seasons and holidays, to ensure availability of your preferred room type.</p>
                    </div>
                </div>
            </section>

            <!-- Payment Questions -->
            <section class="faq-section">
                <h2><i class="fas fa-credit-card"></i> Payment & Pricing</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>What payment methods do you accept?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>We accept Cash, GCash, and Credit Card payments. You can select your preferred payment method during the booking process.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>When do I need to pay?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Payment is due upon check-in. For GCash payments, you may be required to show proof of payment. Credit card payments can be processed at the front desk during check-in.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>Are there additional fees?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Additional guests beyond the base occupancy incur a fee of ₱300 per person per night. All prices displayed include applicable taxes. Any additional services or amenities used during your stay will be charged separately.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>Do you offer refunds?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Refund policies depend on the cancellation timing. Cancellations made 48 hours or more before check-in are eligible for a full refund. Cancellations within 48 hours may incur a one-night charge.</p>
                    </div>
                </div>
            </section>

            <!-- Check-in/Check-out Questions -->
            <section class="faq-section">
                <h2><i class="fas fa-door-open"></i> Check-in & Check-out</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>What time is check-in and check-out?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Standard check-in time is 2:00 PM and check-out time is 12:00 PM (noon). Early check-in and late check-out may be available upon request, subject to availability and additional fees.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>What do I need to bring for check-in?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Please bring a valid government-issued ID (such as a driver's license, passport, or national ID) and your booking confirmation. Payment or proof of payment may also be required at check-in.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>Can I check in early or check out late?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Early check-in and late check-out are subject to availability. Please contact us in advance to make arrangements. Additional charges may apply for late check-out beyond 2:00 PM.</p>
                    </div>
                </div>
            </section>

            <!-- Room & Amenities Questions -->
            <section class="faq-section">
                <h2><i class="fas fa-bed"></i> Rooms & Amenities</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>What amenities are included in the rooms?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>All rooms include free WiFi, air conditioning, and TV. Amenities vary by room type. Deluxe rooms and suites include additional features such as mini bars, premium toiletries, and enhanced views. Check individual room descriptions for complete amenity lists.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-answer">
                        <p>Yes, we offer free parking for all guests. Our parking facility is secure and available 24/7.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>Do you have a restaurant?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, Lunera Grill is our on-site restaurant serving breakfast, lunch, and dinner. We offer a variety of local and international cuisine. Room service is also available.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-chevron-down"></i>
                        <h3>Are pets allowed?</h3>
                    </div>
                    <div class="faq-answer">
                        <p>We have a limited number of pet-friendly rooms available. Please contact us in advance to arrange pet accommodations. Additional cleaning fees may apply.</p>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section class="faq-section">
                <h2><i class="fas fa-phone"></i> Still Have Questions?</h2>
                <div class="contact-box">
                    <p>If you can't find the answer you're looking for, please don't hesitate to contact us:</p>
                    <div class="contact-methods">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <strong>Email</strong>
                                <p>hpl78910@gmail.com</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Phone</strong>
                                <p>+63 955 854 5146</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Hours</strong>
                                <p>24/7 Customer Support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php include "layouts/footer.php"; ?>
</body>
<script src="../public/js/faqtoggles.js"></script>
</html>