<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
requireCustomer();

$user = getCurrentUser();
$error = '';
$success = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ---------- Book Room ----------
    if ($_POST['action'] === 'book') {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = 'CSRF token validation failed';
        } else {
            $room_id   = sanitize($_POST['room_id'] ?? '');
            $check_in  = sanitize($_POST['check_in'] ?? '');
            $check_out = sanitize($_POST['check_out'] ?? '');

            if (empty($room_id) || empty($check_in) || empty($check_out)) {
                $error = 'All fields are required';
            } else {
                $check_in_date  = strtotime($check_in);
                $check_out_date = strtotime($check_out);
                $today          = strtotime('today');

                if ($check_in_date === false || $check_out_date === false) {
                    $error = 'Invalid date format';
                } elseif ($check_in_date < $today) {
                    $error = 'Check-in date cannot be in the past';
                } elseif ($check_out_date <= $check_in_date) {
                    $error = 'Check-out date must be after check-in date';
                } else {
                    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ?');
                    $stmt->execute([$room_id]);
                    $room = $stmt->fetch();

                    if (!$room) {
                        $error = 'Room not found';
                    } else {
                        $stmt = $pdo->prepare('
                            SELECT COUNT(*) as count FROM bookings 
                            WHERE room_id = ? 
                            AND status = "confirmed"
                            AND (check_in < ? AND check_out > ?)
                        ');
                        $stmt->execute([$room_id, $check_out, $check_in]);
                        $conflict = $stmt->fetch();

                        if ($conflict['count'] > 0) {
                            $error = 'Room is not available for the selected dates';
                        } else {
                            $days        = ($check_out_date - $check_in_date) / (60 * 60 * 24);
                            $total_price = $days * $room['price'];

                            $stmt = $pdo->prepare('
                                INSERT INTO bookings (user_id, room_id, check_in, check_out, total_price, status) 
                                VALUES (?, ?, ?, ?, ?, ?)
                            ');

                            try {
                                $stmt->execute([$user['id'], $room_id, $check_in, $check_out, $total_price, 'confirmed']);

                                $stmt = $pdo->prepare('UPDATE rooms SET status = ? WHERE id = ?');
                                $stmt->execute(['occupied', $room_id]);

                                flash('success', 'Booking confirmed! Enjoy your stay.');
                                header('Location: search.php');
                                exit;
                            } catch (PDOException $e) {
                                $error = 'Failed to create booking';
                            }
                        }
                    }
                }
            }
        }
    }

    // ---------- Cancel Booking ----------
    if ($_POST['action'] === 'delete_booking') {
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'CSRF validation failed');
            header('Location: search.php');
            exit;
        }

        $booking_id = intval($_POST['booking_id'] ?? 0);
        $user_id    = $user['id'];

        if ($booking_id > 0) {
            try {
                $stmt = $pdo->prepare("SELECT room_id FROM bookings WHERE id = ? AND user_id = ?");
                $stmt->execute([$booking_id, $user_id]);
                $booking = $stmt->fetch();

                if ($booking) {
                    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
                    $stmt->execute([$booking_id, $user_id]);

                    $stmt = $pdo->prepare("UPDATE rooms SET status = 'available' WHERE id = ?");
                    $stmt->execute([$booking['room_id']]);

                    flash('success', 'Booking cancelled successfully');
                } else {
                    flash('error', 'Unauthorized delete attempt');
                }
            } catch (PDOException $e) {
                flash('error', 'Failed to cancel booking');
            }
        }

        header('Location: search.php');
        exit;
    }
}

// ==================== Fetch user bookings ====================
$stmt = $pdo->prepare('
    SELECT b.*, r.room_no, r.type, r.price 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? 
    ORDER BY b.check_in DESC
');
$stmt->execute([$user['id']]);
$user_bookings = $stmt->fetchAll();

// ==================== Room Search ====================
$search_type         = sanitize($_GET['type'] ?? '');
$search_capacity     = sanitize($_GET['capacity'] ?? '');
$search_availability = sanitize($_GET['availability'] ?? '');

$query  = 'SELECT * FROM rooms WHERE 1=1';
$params = [];

if (!empty($search_type)) {
    $query .= ' AND type = ?';
    $params[] = $search_type;
}
if (!empty($search_capacity)) {
    $query .= ' AND capacity >= ?';
    $params[] = $search_capacity;
}
if (!empty($search_availability)) {
    $query .= ' AND status = ?';
    $params[] = $search_availability;
}

$query .= ' ORDER BY room_no ASC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$search_results = $stmt->fetchAll();

// Room types for dropdown
$room_types = $pdo->query('SELECT DISTINCT type FROM rooms ORDER BY type ASC')->fetchAll();
$csrf_token = generateCSRFToken();
?>

<?php require_once '../includes/header.php'; ?>

<div class="customer-dashboard">
    <h2>Search & Book Rooms</h2>

    <!-- Search Section -->
    <section class="search-section">
        <h3>Find Your Room</h3>
        <form method="GET" id="searchForm" class="search-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Room Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <?php foreach ($room_types as $rt): ?>
                            <option value="<?php echo $rt['type']; ?>" <?php echo $search_type === $rt['type'] ? 'selected' : ''; ?>>
                                <?php echo escape($rt['type']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="capacity">Minimum Capacity</label>
                    <input type="number" id="capacity" name="capacity" value="<?php echo escape($search_capacity); ?>" min="1" placeholder="Number of beds">
                </div>

                <div class="form-group">
                    <label for="availability">Availability</label>
                    <select id="availability" name="availability">
                        <option value="">All</option>
                        <option value="available" <?php echo $search_availability === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="occupied" <?php echo $search_availability === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </section>

    <!-- Rooms Display -->
    <section class="rooms-section">
        <h3>Available Rooms</h3>
        <?php if (empty($search_results)): ?>
            <p class="no-results">No rooms found matching your criteria.</p>
        <?php else: ?>
            <div class="rooms-grid">
                <?php foreach ($search_results as $room): ?>
                    <div class="room-card">
                        <div class="room-header">
                            <h4>Room <?php echo escape($room['room_no']); ?></h4>
                            <span class="room-status status-<?php echo strtolower($room['status']); ?>"><?php echo escape($room['status']); ?></span>
                        </div>

                        <div class="room-details">
                            <p><strong>Type:</strong> <?php echo escape($room['type']); ?></p>
                            <p><strong>Capacity:</strong> <?php echo escape($room['capacity']); ?> beds</p>
                            <p><strong>Price:</strong> $<?php echo escape($room['price']); ?> per night</p>
                        </div>

                        <?php if ($room['status'] === 'available'): ?>
                            <button class="btn btn-primary toggle-book-form">Book Now</button>
                            <form method="POST" class="book-now-form" style="display:none; margin-top:10px;">
                                <input type="hidden" name="action" value="book">
                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <div class="form-group">
                                    <label>Check-in:</label>
                                    <input type="date" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Check-out:</label>
                                    <input type="date" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                                <button type="submit" class="btn btn-success">Confirm Booking</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- User Bookings -->
    <section class="bookings-section">
        <h3>My Bookings</h3>
        <?php if (empty($user_bookings)): ?>
            <p>You haven't made any bookings yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Room No</th>
                        <th>Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_bookings as $booking): ?>
                        <tr>
                            <td><?php echo escape($booking['room_no']); ?></td>
                            <td><?php echo escape($booking['type']); ?></td>
                            <td><?php echo escape($booking['check_in']); ?></td>
                            <td><?php echo escape($booking['check_out']); ?></td>
                            <td>$<?php echo escape($booking['total_price']); ?></td>
                            <td><span class="status status-<?php echo strtolower($booking['status']); ?>"><?php echo escape($booking['status']); ?></span></td>
                            <td>
                                <?php if ($booking['status'] === 'confirmed'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this booking?');">Cancel</button>
                                    </form>
                                <?php else: ?> — <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>

<script>
    document.querySelectorAll('.toggle-book-form').forEach(button => {
        button.addEventListener('click', () => {
            const form = button.nextElementSibling;
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
