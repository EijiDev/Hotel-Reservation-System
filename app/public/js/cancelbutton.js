document.querySelectorAll(".cancel-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const id = btn.getAttribute("data-id");
    if (confirm("Do you want to cancel this booking?")) {
      window.location.href = `/Hotel_Reservation_System/app/public/index.php?controller=booking&action=cancel&id=${id}`;
    }
  });
});
