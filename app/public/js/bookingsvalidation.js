const modal = document.getElementById("modal");
const pendingModal = document.getElementById("pendingModal");
const form = document.getElementById("bookingForm");    
const confirmSubmit = document.getElementById("confirmSubmit");
const cancelModal = document.getElementById("cancelModal");

// Get PHP variables from data attributes
const pricePerNight = parseFloat(form.dataset.pricePerNight);
const roomNumber = form.dataset.roomNumber;
const unavailableDates = JSON.parse(form.dataset.unavailableDates);

const guestExtraFee = 300;

console.log("Room #" + roomNumber + " - Unavailable dates:", unavailableDates);

// Function to check if selected dates overlap with unavailable dates FOR THIS ROOM ONLY
function checkDateOverlap(checkin, checkout) {
    const selectedCheckin = new Date(checkin);
    const selectedCheckout = new Date(checkout);
    
    for (let range of unavailableDates) {
        const bookedCheckin = new Date(range.checkin);
        const bookedCheckout = new Date(range.checkout);
        
        if (selectedCheckin < bookedCheckout && selectedCheckout > bookedCheckin) {
            return true; 
        }
    }
    return false;
}

if (form) {
    form.addEventListener("submit", e => {
        e.preventDefault();

        const checkin = form.checkin.value;
        const checkout = form.checkout.value;
        const guests = parseInt(form.guests.value);
        const contact = form.contact.value;
        const email = form.email.value;
        const paymentMethod = form.payment_method.value;

        if (!checkin || !checkout || !guests || !contact || !email || !paymentMethod) {
            alert("Please fill in all required fields.");
            return;
        }

        const checkinDate = new Date(checkin);
        const checkoutDate = new Date(checkout);

        if (checkoutDate <= checkinDate) {
            alert("Checkout date must be after check-in date.");
            return;
        }

        if (checkDateOverlap(checkin, checkout)) {
            alert("The selected dates are not available for Room #" + roomNumber + ". Please choose different dates.");
            return;
        }

        const nights = Math.ceil((checkoutDate - checkinDate) / (1000*60*60*24));
        const roomTotal = pricePerNight * nights;
        const guestFee = guests > 1 ? (guests - 1) * guestExtraFee : 0;
        const total = roomTotal + guestFee;

        document.getElementById("m_checkin").textContent = checkin;
        document.getElementById("m_checkout").textContent = checkout;
        document.getElementById("m_guests").textContent = guests;
        document.getElementById("m_time").textContent = form.checkin_time.value;
        document.getElementById("m_nights_text").textContent = `₱${pricePerNight.toLocaleString()} × ${nights} night(s)`;
        document.getElementById("m_roomtotal").textContent = `₱${roomTotal.toLocaleString()}`;
        document.getElementById("m_guestfee").textContent = `₱${guestFee.toLocaleString()}`;
        document.getElementById("m_total").textContent = `₱${total.toLocaleString()}`;

        modal.style.display = "flex";
    });

    confirmSubmit.addEventListener("click", () => {
        modal.style.display = "none";
        pendingModal.style.display = "flex";

        setTimeout(() => form.submit(), 500);
    });
}

if (cancelModal) cancelModal.addEventListener("click", () => modal.style.display = "none");
if (document.getElementById("pendingCloseBtn")) {
    document.getElementById("pendingCloseBtn").addEventListener("click", () => pendingModal.style.display = "none");
}

window.onclick = e => {
    if (e.target === modal) modal.style.display = "none";
    if (e.target === pendingModal) pendingModal.style.display = "none";
};
