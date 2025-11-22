document.addEventListener("DOMContentLoaded", () => {
    // Get PHP data from HTML data attributes
    const body = document.body;
    const pricePerNight = parseFloat(body.dataset.price);
    const unavailableDates = JSON.parse(body.dataset.unavailable);
    const guestExtraFee = 300;

    // Get form and modal elements
    const form = document.getElementById("editBookingForm");
    const updateModal = document.getElementById("updateModal");
    const confirmUpdate = document.getElementById("confirmUpdate");
    const cancelUpdate = document.getElementById("cancelUpdate");

    if (!form) return;

    // Function to check date overlap with other bookings
    function checkDateOverlap(checkin, checkout) {
        const selectedCheckin = new Date(checkin);
        const selectedCheckout = new Date(checkout);

        for (let range of unavailableDates) {
            const bookedCheckin = new Date(range.checkin);
            const bookedCheckout = new Date(range.checkout);

            // If dates overlap
            if (selectedCheckin < bookedCheckout && selectedCheckout > bookedCheckin) {
                return true;
            }
        }
        return false;
    }

    // Handle form submission
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        const checkin = form.checkin.value;
        const checkout = form.checkout.value;
        const guests = parseInt(form.guests.value);
        const contact = form.contact.value;
        const email = form.email.value;
        const checkinTime = form.checkin_time.value;

        const checkinDate = new Date(checkin);
        const checkoutDate = new Date(checkout);

        if (checkoutDate <= checkinDate) {
            alert("Checkout date must be after check-in date.");
            return;
        }

        if (checkDateOverlap(checkin, checkout)) {
            alert("The selected dates overlap with another booking. Please choose different dates.");
            return;
        }

        const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
        const roomTotal = pricePerNight * nights;
        const guestFee = guests > 1 ? (guests - 1) * guestExtraFee : 0;
        const total = roomTotal + guestFee;

        // Fill modal with updated booking details
        document.getElementById("m_checkin").textContent = checkin;
        document.getElementById("m_checkout").textContent = checkout;
        document.getElementById("m_guests").textContent = guests;
        document.getElementById("m_time").textContent = checkinTime;
        document.getElementById("m_contact").textContent = contact;
        document.getElementById("m_email").textContent = email;
        document.getElementById("m_nights_text").textContent = `₱${pricePerNight.toLocaleString()} × ${nights} night(s)`;
        document.getElementById("m_roomtotal").textContent = `₱${roomTotal.toLocaleString()}`;
        document.getElementById("m_guestfee").textContent = `₱${guestFee.toLocaleString()}`;
        document.getElementById("m_total").textContent = `₱${total.toLocaleString()}`;

        // Show modal
        updateModal.style.display = "flex";
    });

    // Confirm update
    confirmUpdate.addEventListener("click", () => {
        updateModal.style.display = "none";
        form.submit();
    });

    // Cancel modal
    cancelUpdate.addEventListener("click", () => {
        updateModal.style.display = "none";
    });

    // Close modal if clicked outside
    window.addEventListener("click", (e) => {
        if (e.target === updateModal) updateModal.style.display = "none";
    });
});
