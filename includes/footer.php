    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Hostel Booking System. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Booking Modal -->
    <div id="bookingModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeBookingModal()">&times;</span>
            <h3>Book Room</h3>
            
            <form method="POST" id="bookingForm" class="form">
                <input type="hidden" name="room_id" id="bookingRoomId">
                
                <div class="form-group">
                    <label for="check_in">Check-in Date</label>
                    <input type="date" id="check_in" name="check_in" required>
                </div>
                
                <div class="form-group">
                    <label for="check_out">Check-out Date</label>
                    <input type="date" id="check_out" name="check_out" required>
                </div>
                
                <div class="form-group">
                    <label>Total Price: <span id="totalPrice">$0.00</span></label>
                </div>
                
                <button type="submit" class="btn btn-primary">Confirm Booking</button>
                <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
            </form>
        </div>
    </div>
    
    <script src="/hostel_booking_system/assets/js/app.js"></script>
</body>
</html>