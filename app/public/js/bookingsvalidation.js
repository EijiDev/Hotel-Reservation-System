const modal = document.getElementById("modal");
const form = document.getElementById("bookingForm");
const confirmSubmit = document.getElementById("confirmSubmit");
const cancelModal = document.getElementById("cancelModal");

// Get PHP variables from data attributes
const pricePerNight = parseFloat(form.dataset.pricePerNight);
const roomNumber = form.dataset.roomNumber;
const unavailableDates = JSON.parse(form.dataset.unavailableDates);

const guestExtraFee = 300;
const extraNightFee = 500; // Fee for staying beyond checkout time

console.log("Room #" + roomNumber + " - Unavailable dates:", unavailableDates);

// Function to check if selected dates overlap with unavailable dates FOR THIS ROOM ONLY
function checkDateOverlap(checkin, checkout) {
  const selectedCheckin = new Date(checkin);
  const selectedCheckout = new Date(checkout);

  for (let range of unavailableDates) {
    const bookedCheckin = new Date(range.checkin);
    const bookedCheckout = new Date(range.checkout);

    // Check if dates overlap
    if (selectedCheckin < bookedCheckout && selectedCheckout > bookedCheckin) {
      return true;
    }
  }
  return false;
}

if (form) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const checkin = form.checkin.value;
    const checkout = form.checkout.value;
    const guests = parseInt(form.guests.value);
    const contact = form.contact.value;
    const email = form.email.value;
    const paymentMethod = form.payment_method.value;
    const checkinTime = form.checkin_time.value;

    // Validation
    if (
      !checkin ||
      !checkout ||
      !guests ||
      !contact ||
      !email ||
      !paymentMethod
    ) {
      alert("Please fill in all required fields.");
      return;
    }

    const checkinDate = new Date(checkin);
    const checkoutDate = new Date(checkout);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Date validations
    if (checkinDate < today) {
      alert("Check-in date cannot be in the past.");
      return;
    }

    if (checkoutDate <= checkinDate) {
      alert("Check-out date must be after check-in date.");
      return;
    }

    // Check date overlap for this specific room
    if (checkDateOverlap(checkin, checkout)) {
      alert(
        "The selected dates are not available for Room #" +
          roomNumber +
          ". Please choose different dates."
      );
      return;
    }

    // Contact validation
    const phoneRegex = /^[0-9]{10,11}$/;
    if (!phoneRegex.test(contact.replace(/[\s\-]/g, ""))) {
      alert("Please enter a valid contact number (10-11 digits).");
      return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      alert("Please enter a valid email address.");
      return;
    }

    // Calculate costs
    const nights = Math.ceil(
      (checkoutDate - checkinDate) / (1000 * 60 * 60 * 24)
    );
    const roomTotal = pricePerNight * nights;

    // Guest fee: ₱300 per additional guest (first guest is free)
    const guestFee = guests > 1 ? (guests - 1) * guestExtraFee : 0;

    // Extra night fee: if check-in time is after 6 PM, add extra fee
    const [hours, minutes] = checkinTime.split(":").map(Number);
    const extraNight = hours >= 18 ? extraNightFee : 0;

    const total = roomTotal + guestFee + extraNight;

    // Update modal with booking details
    document.getElementById("m_checkin").textContent = new Date(
      checkin
    ).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
    document.getElementById("m_checkout").textContent = new Date(
      checkout
    ).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
    document.getElementById("m_guests").textContent =
      guests + (guests > 1 ? " guests" : " guest");
    document.getElementById("m_time").textContent = checkinTime;
    document.getElementById(
      "m_nights_text"
    ).textContent = `₱${pricePerNight.toLocaleString()} × ${nights} night${
      nights > 1 ? "s" : ""
    }`;
    document.getElementById(
      "m_roomtotal"
    ).textContent = `₱${roomTotal.toLocaleString()}`;
    document.getElementById(
      "m_guestfee"
    ).textContent = `₱${guestFee.toLocaleString()}`;
    document.getElementById(
      "m_extra_night"
    ).textContent = `₱${extraNight.toLocaleString()}`;
    document.getElementById(
      "m_total"
    ).textContent = `₱${total.toLocaleString()}`;

    // Show modal
    modal.style.display = "flex";
  });

  // Confirm booking submission
  if (confirmSubmit) {
    confirmSubmit.addEventListener("click", () => {
      modal.style.display = "none";

      // Success alert before submission
      alert("Booking successful! Please wait for the confirmation email.");

      // Show loading state
      confirmSubmit.disabled = true;
      confirmSubmit.innerHTML =
        '<i class="bx bx-loader-alt bx-spin"></i> Processing...';

      // Submit form
      form.submit();
    });
  }
}

// Cancel modal
if (cancelModal) {
  cancelModal.addEventListener("click", () => {
    modal.style.display = "none";
  });
}

// Close modal when clicking outside
window.onclick = (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
};

// Real-time checkout date validation
if (form.checkin) {
  form.checkin.addEventListener("change", function () {
    const checkinDate = new Date(this.value);
    const minCheckout = new Date(checkinDate);
    minCheckout.setDate(minCheckout.getDate() + 1);

    form.checkout.min = minCheckout.toISOString().split("T")[0];

    // Reset checkout if it's before new minimum
    if (form.checkout.value && new Date(form.checkout.value) <= checkinDate) {
      form.checkout.value = "";
    }
  });
}
