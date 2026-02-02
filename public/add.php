<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
requireAdmin();

$error = '';
$success = '';
$action = sanitize($_GET['action'] ?? 'rooms');

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF token validation failed';
    } else {
        if ($action === 'rooms') {
            $room_no = sanitize($_POST['room_no'] ?? '');
            $type = sanitize($_POST['type'] ?? '');
            $capacity = sanitize($_POST['capacity'] ?? '');
            $price = sanitize($_POST['price'] ?? '');
            
            if (empty($room_no) || empty($type) || empty($capacity) || empty($price)) {
                $error = 'All fields are required';
            } else {
                $stmt = $pdo->prepare('SELECT id FROM rooms WHERE room_no = ?');
                $stmt->execute([$room_no]);
                
                if ($stmt->fetch()) {
                    $error = 'Room number already exists';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO rooms (room_no, type, capacity, price, status) VALUES (?, ?, ?, ?, ?)');
                    
                    try {
                        $stmt->execute([$room_no, $type, $capacity, $price, 'available']);
                        flash('success', 'Room added successfully!');
                        header('Location: add.php?action=rooms');
                        exit;
                    } catch (PDOException $e) {
                        $error = 'Failed to add room';
                    }
                }
            }
        } elseif ($action === 'occupants') {
            $occupant_name = sanitize($_POST['occupant_name'] ?? '');
            $occupant_email = sanitize($_POST['occupant_email'] ?? '');
            $room_id = sanitize($_POST['room_id'] ?? '');
            
            if (empty($occupant_name) || empty($occupant_email) || empty($room_id)) {
                $error = 'All fields are required';
            } else {
                $stmt = $pdo->prepare('INSERT INTO occupants (name, email, room_id) VALUES (?, ?, ?)');
                
                try {
                    $stmt->execute([$occupant_name, $occupant_email, $room_id]);
                    flash('success', 'Occupant added successfully!');
                    header('Location: add.php?action=occupants');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Failed to add occupant';
                }
            }
        }
    }
}

// Fetch all rooms and occupants
$rooms = $pdo->query('SELECT * FROM rooms ORDER BY room_no ASC')->fetchAll();
$occupants = $pdo->query('SELECT o.*, r.room_no FROM occupants o JOIN rooms r ON o.room_id = r.id ORDER BY r.room_no ASC')->fetchAll();

$csrf_token = generateCSRFToken();
?>
<?php require_once '../includes/header.php'; ?>

<div class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    
    <div class="admin-tabs">
        <a href="?action=rooms" class="tab-link <?php echo $action === 'rooms' ? 'active' : ''; ?>">Manage Rooms</a>
        <a href="?action=occupants" class="tab-link <?php echo $action === 'occupants' ? 'active' : ''; ?>">Manage Occupants</a>
        <a href="?action=bookings" class="tab-link <?php echo $action === 'bookings' ? 'active' : ''; ?>">View Bookings</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo escape($error); ?></div>
    <?php endif; ?>
    
    <!-- Manage Rooms Section -->
    <?php if ($action === 'rooms'): ?>
        <section class="admin-section">
            <h3>Add New Room</h3>
            <form method="POST" class="form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="room_no">Room Number</label>
                        <input type="text" id="room_no" name="room_no" required placeholder="e.g., 101">
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Room Type</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Single">Single</option>
                            <option value="Double">Double</option>
                            <option value="Dormitory">Dormitory</option>
                            <option value="Suite">Suite</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" id="capacity" name="capacity" required placeholder="Number of beds" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (per night)</label>
                        <input type="number" id="price" name="price" required placeholder="e.g., 50.00" step="0.01" min="0">
                    </div>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" class="btn btn-primary">Add Room</button>
            </form>
            
            <h3>Existing Rooms</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Room No</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo escape($room['room_no']); ?></td>
                            <td><?php echo escape($room['type']); ?></td>
                            <td><?php echo escape($room['capacity']); ?> beds</td>
                            <td>$<?php echo escape($room['price']); ?></td>
                            <td><span class="status status-<?php echo strtolower($room['status']); ?>"><?php echo escape($room['status']); ?></span></td>
                            <td>
                                <a href="edit.php?id=<?php echo $room['id']; ?>&type=room" class="btn btn-small btn-warning">Edit</a>
                                <a href="delete.php?id=<?php echo $room['id']; ?>&type=room" class="btn btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    
    <!-- Manage Occupants Section -->
    <?php elseif ($action === 'occupants'): ?>
        <section class="admin-section">
            <h3>Add New Occupant</h3>
            <form method="POST" class="form">
                <div class="form-group">
                    <label for="occupant_name">Name</label>
                    <input type="text" id="occupant_name" name="occupant_name" required placeholder="Occupant name">
                </div>
                
                <div class="form-group">
                    <label for="occupant_email">Email</label>
                    <input type="email" id="occupant_email" name="occupant_email" required placeholder="Occupant email">
                </div>
                
                <div class="form-group">
                    <label for="room_id">Room</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">Select Room</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>">Room <?php echo escape($room['room_no']); ?> (<?php echo escape($room['type']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" class="btn btn-primary">Add Occupant</button>
            </form>
            
            <h3>Existing Occupants</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Room No</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($occupants as $occupant): ?>
                        <tr>
                            <td><?php echo escape($occupant['name']); ?></td>
                            <td><?php echo escape($occupant['email']); ?></td>
                            <td><?php echo escape($occupant['room_no']); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $occupant['id']; ?>&type=occupant" class="btn btn-small btn-warning">Edit</a>
                                <a href="delete.php?id=<?php echo $occupant['id']; ?>&type=occupant" class="btn btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    
    <!-- View Bookings Section -->
    <?php elseif ($action === 'bookings'): ?>
        <section class="admin-section">
            <h3>All Bookings</h3>
            <?php
            $bookings = $pdo->query('
                SELECT b.*, u.name, u.email, r.room_no, r.type 
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                JOIN rooms r ON b.room_id = r.id 
                ORDER BY b.check_in DESC
            ')->fetchAll();
            ?>
            
            <?php if (empty($bookings)): ?>
                <p>No bookings found.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Room No</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo escape($booking['name']); ?></td>
                                <td><?php echo escape($booking['email']); ?></td>
                                <td><?php echo escape($booking['room_no']); ?> (<?php echo escape($booking['type']); ?>)</td>
                                <td><?php echo escape($booking['check_in']); ?></td>
                                <td><?php echo escape($booking['check_out']); ?></td>
                                <td>$<?php echo escape($booking['total_price']); ?></td>
                                <td><span class="status status-<?php echo strtolower($booking['status']); ?>"><?php echo escape($booking['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>