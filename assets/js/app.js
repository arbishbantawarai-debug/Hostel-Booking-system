// Ajax Functions for Hostel Booking System

// Book Room - Open Modal
function openBookingModal(roomId) {
    const modal = document.getElementById('bookingModal');
    const roomIdInput = document.getElementById('bookingRoomId');
    const bookingForm = document.getElementById('bookingForm');
    
    roomIdInput.value = roomId;
    modal.style.display = 'block';
    
    // Set minimum check-in date to today
    const today = new Date().toISOString().split('T')[0];
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput) {
        checkInInput.setAttribute('min', today);
        checkInInput.value = today;
    }
    
    if (checkOutInput) {
        checkOutInput.value = '';
        checkOutInput.setAttribute('min', today);
    }
    
    // Reset total price
    document.getElementById('totalPrice').textContent = '$0.00';
    
    // Add CSRF token to form
    const csrfToken = document.getElementById('csrf_token')?.value;
    if (csrfToken && bookingForm) {
        let csrfInput = bookingForm.querySelector('input[name="csrf_token"]');
        if (!csrfInput) {
            csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            bookingForm.appendChild(csrfInput);
        }
        csrfInput.value = csrfToken;
        
        // Add action field
        let actionInput = bookingForm.querySelector('input[name="action"]');
        if (!actionInput) {
            actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'book';
            bookingForm.appendChild(actionInput);
        }
    }
}

function closeBookingModal() {
    const modal = document.getElementById('bookingModal');
    modal.style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('bookingModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Book Now Button Handler
document.addEventListener('DOMContentLoaded', function() {
    // Handle existing book buttons
    const bookButtons = document.querySelectorAll('.book-btn');
    bookButtons.forEach(button => {
        button.addEventListener('click', function() {
            const roomId = this.getAttribute('data-room-id');
            openBookingModal(roomId);
        });
    });
    
    // Calculate total price when dates change
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', calculatePrice);
        checkOutInput.addEventListener('change', calculatePrice);
    }
    
    // Real-time search with Ajax
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        const inputs = searchForm.querySelectorAll('select, input');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                performLiveSearch();
            });
        });
    }
    
    // Check availability on date change
    checkAvailabilityOnDateChange();
});

// Calculate total price
function calculatePrice() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const roomIdInput = document.getElementById('bookingRoomId');
    
    if (!checkInInput.value || !checkOutInput.value || !roomIdInput.value) {
        return;
    }
    
    const checkIn = new Date(checkInInput.value);
    const checkOut = new Date(checkOutInput.value);
    
    if (checkOut <= checkIn) {
        document.getElementById('totalPrice').textContent = '$0.00';
        return;
    }
    
    const days = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
    
    // Fetch room price via Ajax
    fetchRoomPrice(roomIdInput.value, days);
}

// Fetch room price via Fetch API
function fetchRoomPrice(roomId, days) {
    fetch(`/hostel_booking_system/public/room-price.php?room_id=${roomId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                const totalPrice = data.data.price * days;
                document.getElementById('totalPrice').textContent = '$' + totalPrice.toFixed(2);
            } else {
                console.error('Error fetching price:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('totalPrice').textContent = '$0.00';
        });
}

// Live Search with Ajax
function performLiveSearch() {
    const type = document.getElementById('type')?.value || '';
    const capacity = document.getElementById('capacity')?.value || '';
    const availability = document.getElementById('availability')?.value || '';
    const liveResultsDiv = document.getElementById('liveSearchResults');
    
    // Build query string
    let queryString = '';
    if (type) queryString += `type=${encodeURIComponent(type)}&`;
    if (capacity) queryString += `capacity=${encodeURIComponent(capacity)}&`;
    if (availability) queryString += `availability=${encodeURIComponent(availability)}&`;
    
    // Remove trailing &
    queryString = queryString.replace(/&$/, '');
    
    // If no filters, clear results
    if (!queryString) {
        if (liveResultsDiv) liveResultsDiv.innerHTML = '';
        return;
    }
    
    // Fetch results via Fetch API
    fetch(`/hostel_booking_system/public/search-rooms.php?${queryString}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!liveResultsDiv) return;
            
            if (data.status === 'success') {
                displayLiveSearchResults(data.data.rooms);
            } else {
                liveResultsDiv.innerHTML = '<p class="no-results">No rooms found.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (liveResultsDiv) {
                liveResultsDiv.innerHTML = '<p class="no-results">Error performing search.</p>';
            }
        });
}

// Display Live Search Results
function displayLiveSearchResults(rooms) {
    const liveResultsDiv = document.getElementById('liveSearchResults');
    
    if (!rooms || rooms.length === 0) {
        liveResultsDiv.innerHTML = '<p class="no-results">No rooms found matching your criteria.</p>';
        return;
    }
    
    let html = '<h4>Live Search Results</h4>';
    html += '<div class="rooms-grid">';
    
    rooms.forEach(room => {
        html += `
            <div class="room-card">
                <div class="room-header">
                    <h4>Room ${escapeHtml(room.room_no)}</h4>
                    <span class="room-status status-${room.status.toLowerCase()}">${escapeHtml(room.status)}</span>
                </div>
                <div class="room-details">
                    <p><strong>Type:</strong> ${escapeHtml(room.type)}</p>
                    <p><strong>Capacity:</strong> ${escapeHtml(room.capacity)} beds</p>
                    <p><strong>Price:</strong> $${escapeHtml(room.price)} per night</p>
                </div>
                ${room.status === 'available' ? `<button class="btn btn-primary book-btn" data-room-id="${room.id}">Book Now</button>` : ''}
            </div>
        `;
    });
    
    html += '</div>';
    liveResultsDiv.innerHTML = html;
    
    // Re-attach click handlers to new buttons
    const newBookButtons = liveResultsDiv.querySelectorAll('.book-btn');
    newBookButtons.forEach(button => {
        button.addEventListener('click', function() {
            const roomId = this.getAttribute('data-room-id');
            openBookingModal(roomId);
        });
    });
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

// Check Room Availability via Ajax
function checkAvailability(roomId, checkIn, checkOut) {
    return fetch(`/hostel_booking_system/public/check-availability.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            room_id: roomId,
            check_in: checkIn,
            check_out: checkOut
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => data)
    .catch(error => {
        console.error('Error:', error);
        return { status: 'error', available: false };
    });
}

// Fetch room availability when dates change
function checkAvailabilityOnDateChange() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const roomIdInput = document.getElementById('bookingRoomId');
    
    if (checkInInput && checkOutInput && roomIdInput) {
        checkInInput.addEventListener('change', performAvailabilityCheck);
        checkOutInput.addEventListener('change', performAvailabilityCheck);
    }
}

function performAvailabilityCheck() {
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const roomIdInput = document.getElementById('bookingRoomId');
    
    if (!checkInInput.value || !checkOutInput.value || !roomIdInput.value) {
        return;
    }
    
    checkAvailability(roomIdInput.value, checkInInput.value, checkOutInput.value)
        .then(data => {
            if (data.status === 'success' && !data.data.available) {
                alert('Room is not available for selected dates');
                checkOutInput.value = '';
                document.getElementById('totalPrice').textContent = '$0.00';
            }
        });
}