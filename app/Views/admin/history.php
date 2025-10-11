
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