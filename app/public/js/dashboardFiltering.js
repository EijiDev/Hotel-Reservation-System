// Alert function
function showAlert(message, type = "error") {
  const alertContainer = document.getElementById("alertContainer");
  const icons = {
    error: "fa-circle-exclamation",
    success: "fa-circle-check",
    warning: "fa-triangle-exclamation",
  };

  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type}`;
  alertDiv.innerHTML = `
            <i class="fa-solid ${icons[type]}"></i>
            <span>${message}</span>
        `;

  alertContainer.appendChild(alertDiv);

  setTimeout(() => {
    alertDiv.style.opacity = "0";
    alertDiv.style.transform = "translateY(-10px)";
    setTimeout(() => alertDiv.remove(), 300);
  }, 5000);
}

// Archive button validation
document.querySelectorAll(".archive-btn").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    e.preventDefault();

    const bookingId = this.dataset.bookingId;
    const status = this.dataset.status;
    const payment = this.dataset.payment;

    // Validation: Cannot archive if checked-out with refunded payment
    if (status === "checked-out" && payment === "refunded") {
      showAlert(
        "Cannot archive checked-out booking with refunded payment. This booking needs to remain visible for financial records.",
        "error"
      );
      return;
    }

    // Validation: Cannot archive if checked-in
    if (status === "checked-in") {
      showAlert(
        "Cannot archive a checked-in booking. Please check out the guest first.",
        "error"
      );
      return;
    }

    // Show confirmation dialog
    const confirmMsg =
      status === "checked-out"
        ? "Move this completed booking to history?\n\nThis booking will be archived but still visible to the user."
        : "Move this booking to history?\n\nThis will archive the booking but keep it visible to the user.";

    if (confirm(confirmMsg)) {
      window.location.href = `/Hotel_Reservation_System/app/public/index.php?controller=admin&action=delete&id=${bookingId}`;
    }
  });
});

// Confirm button validation (prevent confirming already confirmed)
document
  .querySelectorAll('.confirm-btn:not([style*="pointer-events"])')
  .forEach((btn) => {
    btn.addEventListener("click", function (e) {
      const status = this.dataset.status;

      if (status === "confirmed") {
        e.preventDefault();
        showAlert("This booking is already confirmed", "warning");
        return false;
      }

      if (status === "cancelled") {
        e.preventDefault();
        showAlert("Cannot confirm a cancelled booking", "error");
        return false;
      }

      if (status === "checked-out") {
        e.preventDefault();
        showAlert("This booking is already completed", "warning");
        return false;
      }
    });
  });

// Check for URL parameters
window.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);

  if (urlParams.has("success")) {
    const successType = urlParams.get("success");
    const messages = {
      confirmed: "Booking confirmed successfully!",
      archived: "Booking moved to history successfully!",
    };
    showAlert(
      messages[successType] || "Operation completed successfully!",
      "success"
    );

    // Remove success parameter from URL without refreshing
    urlParams.delete("success");
    const newUrl = urlParams.toString()
      ? `${window.location.pathname}?${urlParams.toString()}`
      : window.location.pathname;
    window.history.replaceState({}, "", newUrl);
  } else if (urlParams.has("error")) {
    const errorType = urlParams.get("error");
    const messages = {
      already_confirmed: "This booking is already confirmed",
      confirm_failed: "Failed to confirm booking. Please try again.",
      cannot_archive: "Cannot archive this booking",
    };
    showAlert(messages[errorType] || "An error occurred", "error");

    // Remove error parameter from URL without refreshing
    urlParams.delete("error");
    const newUrl = urlParams.toString()
      ? `${window.location.pathname}?${urlParams.toString()}`
      : window.location.pathname;
    window.history.replaceState({}, "", newUrl);
  }
});
