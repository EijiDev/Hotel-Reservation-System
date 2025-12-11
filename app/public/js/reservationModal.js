function viewModal(data) {
  const body = document.getElementById("viewBody");
  body.innerHTML = `
                <div class="info-row">
                    <strong>Name:</strong>
                    <span>${data.GuestName || "Unknown"}</span>
                </div>
                <div class="info-row">
                    <strong>Email:</strong>
                    <span>${data.Email || "N/A"}</span>
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
                    <strong>Guests:</strong>
                    <span>${data.Guests || 1}</span>
                </div>
                <div class="info-row">
                    <strong>Status:</strong>
                    <span>${data.StatusName || "Pending"}</span>
                </div>
            `;
  document.getElementById("viewModal").style.display = "flex";
}

function editModal(data) {
  document.getElementById("editId").value = data.BookingID;
  document.getElementById("editName").value = data.GuestName || "";
  document.getElementById("editEmail").value = data.Email || "";
  document.getElementById("editRoomType").value = data.RoomType || "";
  document.getElementById("editRoomNo").value = data.RoomNumber || "";
  document.getElementById("editStreet").value = data.Street || "";
  document.getElementById("editBarangay").value = data.Barangay || "";
  document.getElementById("editCity").value = data.City || "";
  document.getElementById("editProvince").value = data.Province || "";
  document.getElementById("editPostalCode").value = data.PostalCode || "";
  document.getElementById("editCheckin").value = data.CheckIn || "";
  document.getElementById("editCheckout").value = data.CheckOut || "";
  document.getElementById("editGuests").value = data.Guests || 1;
  document.getElementById("editStatus").value = (
    data.StatusName || "pending"
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
