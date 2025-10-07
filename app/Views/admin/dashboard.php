<title>Admin Dashboard</title>
<link rel="stylesheet" href="./css/dashboard.style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2><i class="fa-solid fa-hotel"></i> Admin Panel</h2>
            <ul>
                <li class="dashboard-bar"><i class="fa-solid fa-chart-line"></i> Dashboard</li>
                <li class="dashboard-bar"><i class="fa-solid fa-receipt"></i> Billing History</li>
                <li class="dashboard-bar"><i class="fa-solid fa-box"></i> Order History</li>
            </ul>
        </div>
        <div class="bottom">
            <a href="/Hotel_Reservation_System/app/public/index.php?controller=logout&action=index" class="logout">
                <i class="fa-solid fa-right-from-bracket"></i> Log out
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h1>Admin Dashboard</h1>

        <!-- Stats Section -->
        <div class="stats">
            <div class="card">
                <h3>Total Revenue</h3>
                <p>$3,500</p>
                <small>Based on all bookings</small>
            </div>
            <div class="card">
                <h3>Total Bookings</h3>
                <p>+2</p>
                <small>Across all hotels</small>
            </div>
            <div class="card">
                <h3>Upcoming Check-ins</h3>
                <p>2</p>
                <small>Guests arriving soon</small>
            </div>
            <div class="card">
                <h3>Available Rooms</h3>
                <p>6</p>
                <small>Rooms ready for booking</small>
            </div>
        </div>

        <!-- Manage Bookings -->
        <div class="manage-bookings">
            <h2>Manage Bookings</h2>
            <div class="filters">
                <input type="text" id="search" placeholder="Search bookings...">
                <select id="statusFilter">
                    <option value="all">Status</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Hotel</th>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody id="bookingTable">
                    <tr>
                        <td>1</td>
                        <td>John Doe</td>
                        <td>Comfort Twin Bed Room</td>
                        <td>Deluxe</td>
                        <td>Oct 06, 2025</td>
                        <td>Oct 10, 2025</td>
                        <td><span class="status confirmed">Confirmed</span></td>
                        <td><span class="payment paid">Paid</span></td>
                        <td>$1400.00</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Alice Smith</td>
                        <td>Cozy Single Bed Room</td>
                        <td>VIP</td>
                        <td>Oct 11, 2025</td>
                        <td>Oct 16, 2025</td>
                        <td><span class="status confirmed">Confirmed</span></td>
                        <td><span class="payment pending">Pending</span></td>
                        <td>$2100.00</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
