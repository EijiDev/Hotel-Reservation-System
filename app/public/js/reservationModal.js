function viewModal(data) {
  const body = document.getElementById("viewBody");
  const footer = document.getElementById("viewModalFooter");

  // Calculate total amount
  const checkinTimestamp = new Date(data.CheckIn).getTime();
  const checkoutTimestamp = new Date(data.CheckOut).getTime();
  let nights = Math.ceil(
    (checkoutTimestamp - checkinTimestamp) / (1000 * 60 * 60 * 24)
  );
  nights = Math.max(1, nights);

  const roomPrice = parseFloat(data.room_price || 0);
  const guests = parseInt(data.Guests || 1);
  const checkinTime = data.CheckIn_Time || "14:00";

  const roomTotal = roomPrice * nights;
  const guestFee = guests > 1 ? (guests - 1) * 300 : 0;

  let extraNightFee = 0;
  if (checkinTime) {
    const [hours] = checkinTime.split(":");
    if (parseInt(hours) >= 18) {
      extraNightFee = 500;
    }
  }

  const totalAmount = roomTotal + guestFee + extraNightFee;

  // Payment status styling
  const paymentStatusClass = data.PaymentStatus
    ? data.PaymentStatus.toLowerCase()
    : "pending";
  const paymentStatusText = data.PaymentStatus
    ? data.PaymentStatus.charAt(0).toUpperCase() + data.PaymentStatus.slice(1)
    : "Pending";

  body.innerHTML = `
        <div class="info-row">
            <strong>Booking ID:</strong>
            <span>${data.BookingID || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Name:</strong>
            <span>${data.GuestName || "Unknown"}</span>
        </div>
        <div class="info-row">
            <strong>Email:</strong>
            <span>${data.Email || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Contact:</strong>
            <span>${data.Contact || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Room Type:</strong>
            <span>${data.RoomType || "Unknown"}</span>
        </div>
        <div class="info-row">
            <strong>Room No.:</strong>
            <span>${data.RoomNumber || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Street:</strong>
            <span>${data.Street || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Barangay:</strong>
            <span>${data.Barangay || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>City:</strong>
            <span>${data.City || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Province:</strong>
            <span>${data.Province || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Postal Code:</strong>
            <span>${data.PostalCode || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Check-in:</strong>
            <span>${data.CheckIn || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Check-out:</strong>
            <span>${data.CheckOut || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Check-in Time:</strong>
            <span>${data.CheckIn_Time || "14:00"}</span>
        </div>
        <div class="info-row">
            <strong>Guests:</strong>
            <span>${data.Guests || 1}</span>
        </div>
        <div class="info-row">
            <strong>Booking Status:</strong>
            <span class="status ${
              data.StatusName ? data.StatusName.toLowerCase() : "pending"
            }">${data.StatusName || "Pending"}</span>
        </div>
        <div class="info-row">
            <strong>Payment Method:</strong>
            <span>${data.PaymentMethod || "N/A"}</span>
        </div>
        <div class="info-row">
            <strong>Payment Status:</strong>
            <span class="status ${paymentStatusClass}">${paymentStatusText}</span>
        </div>
        <div class="info-row">
            <strong>Total Amount:</strong>
            <span style="font-size: 1.2em; color: #2c5f2d; font-weight: bold;">₱${totalAmount.toLocaleString(
              "en-PH",
              { minimumFractionDigits: 2, maximumFractionDigits: 2 }
            )}</span>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #2c5f2d;">
            <strong style="display: block; margin-bottom: 10px; color: #2c5f2d;">Price Breakdown:</strong>
            <div style="font-size: 0.95em; color: #555;">
                <p style="margin: 5px 0;">Room (${nights} night${
    nights > 1 ? "s" : ""
  } × ₱${roomPrice.toLocaleString("en-PH", {
    minimumFractionDigits: 2,
  })}): <strong>₱${roomTotal.toLocaleString("en-PH", {
    minimumFractionDigits: 2,
  })}</strong></p>
                ${
                  guestFee > 0
                    ? `<p style="margin: 5px 0;">Additional Guests (${
                        guests - 1
                      } × ₱300): <strong>₱${guestFee.toLocaleString("en-PH", {
                        minimumFractionDigits: 2,
                      })}</strong></p>`
                    : ""
                }
                ${
                  extraNightFee > 0
                    ? `<p style="margin: 5px 0;">Late Check-in Fee (after 6 PM): <strong>₱${extraNightFee.toLocaleString(
                        "en-PH",
                        { minimumFractionDigits: 2 }
                      )}</strong></p>`
                    : ""
                }
                <hr style="margin: 10px 0; border: none; border-top: 1px solid #ddd;">
                <p style="margin: 5px 0; font-size: 1.1em;"><strong>Total: ₱${totalAmount.toLocaleString(
                  "en-PH",
                  { minimumFractionDigits: 2, maximumFractionDigits: 2 }
                )}</strong></p>
            </div>
        </div>
    `;

  // Check if payment is Cash and Pending - show "Mark as Paid" button
  const isCashPending =
    data.PaymentMethod &&
    data.PaymentMethod.toLowerCase() === "cash" &&
    data.PaymentStatus &&
    data.PaymentStatus.toLowerCase() === "pending";

  if (isCashPending) {
    footer.innerHTML = `
            <button type="button" onclick="closeModal('viewModal')">Close</button>
            <form method="POST" action="/Hotel_Reservation_System/app/public/index.php?controller=staff&action=markPaymentPaid" style="display: inline-block; margin: 0;">
                <input type="hidden" name="booking_id" value="${
                  data.BookingID
                }">
                <button type="submit" onclick="return confirm('Mark this cash payment as completed? Guest has paid ₱${totalAmount.toLocaleString(
                  "en-PH",
                  { minimumFractionDigits: 2 }
                )}?')" 
                        style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
                    <i class="fa-solid fa-check-circle"></i> Mark as Paid
                </button>
            </form>
        `;
  } else {
    footer.innerHTML = `
            <button type="button" onclick="closeModal('viewModal')">Close</button>
        `;
  }

  document.getElementById("viewModal").style.display = "flex";
}

function editModal(data) {
  document.getElementById("editId").value = data.BookingID || "";
  document.getElementById("editName").value = data.GuestName || "";
  document.getElementById("editEmail").value = data.Email || "";
  document.getElementById("editContact").value = data.Contact || "";
  document.getElementById("editStreet").value = data.Street || "";
  document.getElementById("editBarangay").value = data.Barangay || "";
  document.getElementById("editCity").value = data.City || "";
  document.getElementById("editProvince").value = data.Province || "";
  document.getElementById("editPostalCode").value = data.PostalCode || "";
  document.getElementById("editCheckin").value = data.CheckIn || "";
  document.getElementById("editCheckout").value = data.CheckOut || "";
  document.getElementById("editCheckinTime").value =
    data.CheckIn_Time || "14:00";
  document.getElementById("editGuests").value = data.Guests || 1;
  document.getElementById("editStatus").value = (
    data.StatusName || "pending"
  ).toLowerCase();
  
  document.getElementById("editPaymentStatus").value = (
    data.PaymentStatus || "pending"
  ).toLowerCase();
  
  document.getElementById("editModal").style.display = "flex";
}

function closeModal(id) {
  document.getElementById(id).style.display = "none";
}

window.onclick = function (e) {
  if (e.target.classList.contains("modal")) {
    e.target.style.display = "none";
  }
};