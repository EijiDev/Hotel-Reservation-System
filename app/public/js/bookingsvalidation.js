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
    const contact = form.contact.value.trim();
    const email = form.email.value.trim();
    const paymentMethod = form.payment_method.value;
    const checkinTime = form.checkin_time.value;
    const idType = form.id_type ? form.id_type.value : "";
    const idFileInput = form.id_image;
    const idFile = idFileInput && idFileInput.files[0];

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

      // NEW: validate ID image extension and size (e.g., max 3 MB)
    const allowedExtensions = ["jpg", "jpeg", "png", "webp"];
    const maxSizeMB = 3;
    const fileName = idFile.name.toLowerCase();
    const fileSizeMB = idFile.size / (1024 * 1024);
    const ext = fileName.split(".").pop();

    if (!allowedExtensions.includes(ext)) {
      alert("Please upload a valid ID image (JPG, JPEG, PNG, or WEBP).");
      idFileInput.value = "";
      idFileInput.focus();
      return;
    }

    if (fileSizeMB > maxSizeMB) {
      alert("ID image must be smaller than 3 MB.");
      idFileInput.value = "";
      idFileInput.focus();
      return;
    }

    // OPTIONAL: show file name in the drag area
    const idDropTitle = document.querySelector(".id-drop-title");
    if (idDropTitle) {
      idDropTitle.textContent = idFile.name;
    }

    // OPTIONAL: “match” ID type rule (example only)
    // here you just ensure something is selected;
    // real matching of content to ID type must be done on the server or manually
    if (!["School ID", "National ID", "Postal ID"].includes(idType)) {
      alert("Please select a valid ID type.");
      form.id_type.focus();
      return;
    }


    // Guest limit validation (max 5 guests)
    if (guests < 1 || guests > 5) {
      alert("Number of guests must be between 1 and 5.");
      form.guests.focus();
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

    // Enhanced Contact validation (Philippine phone numbers + international)
    const cleanContact = contact.replace(/[\s\-\+\(\)]/g, "");
    let isValidContact = false;

    // Philippine mobile numbers (09xx-xxx-xxxx or +639xx-xxx-xxxx)
    const phMobileRegex = /^639\d{9}$|^09\d{9}$/;
    // Philippine landline (02-xxx-xxxx or +632-xxx-xxxx)
    const phLandlineRegex = /^632\d{7,8}$|^02\d{7,8}$/;
    // International numbers (starting with + followed by country code)
    const intlRegex = /^\+\d{1,4}\d{7,12}$/;

    if (phMobileRegex.test(cleanContact) || phLandlineRegex.test(cleanContact) || intlRegex.test(contact)) {
      isValidContact = true;
    }

    if (!isValidContact) {
      alert("Please enter a valid phone number.\n• Philippine mobile: 09xxxxxxxxx or +639xxxxxxxxx");
      form.contact.focus();
      return;
    }

    // Enhanced Email validation
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
      alert("Please enter a valid email address (e.g., example@gmail.com).");
      form.email.focus();
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

// Real-time checkout date validation AND guest limit
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

// Real-time guest limit validation
if (form.guests) {
  form.guests.addEventListener("input", function () {
    const guests = parseInt(this.value);
    if (guests > 5) {
      this.value = 5;
      alert("Maximum 5 guests only allowed");
    } else if (guests < 1) {
      this.value = 1;
    }
  });
}
