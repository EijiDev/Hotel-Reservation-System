let searchTimeout;

document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search");
  const statusSelect = document.getElementById("booking_status");
  const paymentSelect = document.getElementById("payment_status");
  const sortBySelect = document.getElementById("sort_by");
  const tableBody = document.querySelector("#bookingsTable tbody");

  // Collect all rows once (original data)
  const allRows = Array.from(tableBody.querySelectorAll("tr"));
  const hasData = allRows.length > 0 && allRows[0].cells.length > 1;

  if (hasData) {
    // Attach client-side listeners
    searchInput.addEventListener("keyup", delayedFilter);
    statusSelect.addEventListener("change", applyFiltersAndSort);
    paymentSelect.addEventListener("change", applyFiltersAndSort);
    sortBySelect.addEventListener("change", applyFiltersAndSort);

    // Initial apply for any pre-selected filters/sort
    applyFiltersAndSort();
  } else {
    updateResultsCount(0);
  }

  function delayedFilter() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFiltersAndSort, 500);
  }

  function updateResultsCount(count) {
    const resultsCountDiv = document.querySelector(".results-count");
    const searchVal = searchInput.value.trim();
    const statusVal = statusSelect.value;
    const paymentVal = paymentSelect.value;
    const filteredText = searchVal || statusVal || paymentVal ? "(Filtered)" : "";
    resultsCountDiv.innerHTML = `
      <i class="fa-solid fa-list"></i>
      Showing ${count} booking(s) ${filteredText}
    `;
  }

  function applyFiltersAndSort() {
    let filteredRows = allRows;
    const searchVal = searchInput.value.toLowerCase().trim();
    const statusVal = statusSelect.value;
    const paymentVal = paymentSelect.value;

    // --- Filtering ---
    filteredRows = allRows.filter((row) => {
      const id = row.cells[0].innerText.toLowerCase();
      const guestName = row.cells[1].innerText.toLowerCase();
      const rowStatus = row.cells[6].querySelector(".status").innerText.toLowerCase();
      const rowPaymentStatus = row.cells[7].querySelector(".payment").innerText.toLowerCase();

      const passesSearch = !searchVal || id.includes(searchVal) || guestName.includes(searchVal);
      const passesStatus = !statusVal || rowStatus === statusVal;
      const passesPayment = !paymentVal || rowPaymentStatus === paymentVal;

      return passesSearch && passesStatus && passesPayment;
    });

    // --- Sorting ---
    const sortVal = sortBySelect.value;
    filteredRows.sort((a, b) => {
      const aID = parseInt(a.cells[0].innerText);
      const bID = parseInt(b.cells[0].innerText);
      const aGuest = a.cells[1].innerText.trim().toLowerCase();
      const bGuest = b.cells[1].innerText.trim().toLowerCase();

      const aCheckInDate = a.cells[3].innerText;
      const aCheckInTime = a.cells[4].querySelector("strong").innerText.split(" ")[0];
      const bCheckInDate = b.cells[3].innerText;
      const bCheckInTime = b.cells[4].querySelector("strong").innerText.split(" ")[0];

      const aCheckIn = new Date(aCheckInDate + " " + aCheckInTime);
      const bCheckIn = new Date(bCheckInDate + " " + bCheckInTime);

      const aTotal = parseFloat(a.cells[9].innerText.replace("₱", "").replace(/,/g, ""));
      const bTotal = parseFloat(b.cells[9].innerText.replace("₱", "").replace(/,/g, ""));

      let comparison = 0;
      switch (sortVal) {
        case "checkin_asc":
          comparison = aCheckIn.getTime() - bCheckIn.getTime();
          break;
        case "checkin_desc":
          comparison = bCheckIn.getTime() - aCheckIn.getTime();
          break;
        case "total_asc":
          comparison = aTotal - bTotal;
          break;
        case "total_desc":
          comparison = bTotal - aTotal;
          break;
        case "guest_name":
          comparison = aGuest.localeCompare(bGuest);
          break;
        default:
          comparison = aID - bID; // fallback: by ID
      }
      return comparison;
    });

    // --- Update table ---
    tableBody.innerHTML = "";
    if (filteredRows.length > 0) {
      filteredRows.forEach((row) => tableBody.appendChild(row));
    } else {
      tableBody.innerHTML = `<tr><td colspan="11" style="text-align:center;">No bookings found.</td></tr>`;
    }

    updateResultsCount(filteredRows.length);
  }
});
