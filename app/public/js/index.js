const today = new Date();
const yyyy = today.getFullYear;
const mm = String(today.getMonth() + 1).padStart(2, "0"); // Months start at 0
const dd = String(today.getDate()).padStart(2, "0");

const checkinDate = `${yyyy}-${mm}-${dd}`;
document.getElementById("checkin").value = checkinDate;

const tomorrow = new Date();
tomorrow.setDate(today.getDate() + 1);
const t_yyyy = tomorrow.getFullYear();
const t_mm = String(tomorrow.getMonth() + 1).padStart(2, "0");
const t_dd = String(tomorrow.getDate()).padStart(2, "0");

const checkoutDate = `${t_yyyy}-${t_mm}-${t_dd}`;
document.getElementById("checkout").value = checkoutDate;

document.getElementById("checkin").addEventListener("change", (e) => {
  const selectedDate = new Date(e.target.value);
  const nextDay = new Date(selectedDate);
  nextDay.setDate(selectedDate.getDate() + 1);
  const newCheckout = `${nextDay.getFullYear()}-${String(
    nextDay.getMonth() + 1
  ).padStart(2, "0")}-${String(nextDay.getDate()).padStart(2, "0")}`;
  document.getElementById("checkout").min = newCheckout;
});

//alert message when successfull booking
document.addEventListener("DOMContentLoaded", () => {
  const bookNowButton = document.getElementById("book-now-btn");
  if (bookNowButton) {
    bookNowButton.addEventListener("click", () => {
      alert("You successfully booked!");
    });
  }
});
