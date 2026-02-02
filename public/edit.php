<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
requireAdmin();

$error = '';
$success = '';
$id = sanitize($_GET['id'] ?? '');
$type = sanitize($_GET['type'] ?? '');

if (empty($id) || empty($type)) {
    header('Location: add.php');
    exit;
}

// Fetch data based on type
if ($type === 'room') {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    $item_type = 'room';
} elseif ($type === 'occupant') {
    $stmt = $pdo->prepare('SELECT * FROM occupants WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    $item_type = 'occupant';
} else {
    header('Location: add.php');
    exit;
}

if (!$item) {
    header('Location: add.php');
    exit;
}

// Fetch all rooms for dropdown
$rooms = $pdo->query('SELECT * FROM rooms ORDER BY room_no ASC')->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF token validation failed';
    } else {
        if ($item_type === 'room') {
            $room_no = sanitize($_POST['room_no'] ?? '');
            $type_val = sanitize($_POST['type'] ?? '');
            $capacity = sanitize($_POST['capacity'] ?? '');
            $price = sanitize($_POST['price'] ?? '');
            $status = sanitize($_POST['status'] ?? '');
            
            if (empty($room_no) || empty($type_val) || empty($capacity) || empty($price) || empty($status)) {
                $error = 'All fields are required';
            } else {
                $stmt = $pdo->prepare('UPDATE rooms SET room_no = ?, type = ?, capacity = ?, price = ?, status = ? WHERE id = ?');
                
                try {
                    $stmt->execute([$room_no, $type_val, $capacity, $price, $status, $id]);
                    $success = 'Room updated successfully!';
                    // Refresh data
                    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ?');
                    $stmt->execute([$id]);
                    $item = $stmt->fetch();
                } catch (PDOException $e) {
                    $error = 'Failed to update room';
                }
            }
        } elseif ($item_type === 'occupant') {
            $occupant_name = sanitize($_POST['occupant_name'] ?? '');
            $occupant_email = sanitize($_POST['occupant_email'] ?? '');
            $room_id = sanitize($_POST['room_id'] ?? '');
            
            if (empty($occupant_name) || empty($occupant_email) || empty($room_id)) {
                $error = 'All fields are required';
            } else {
                $stmt = $pdo->prepare('UPDATE occupants SET name = ?, email = ?, room_id = ? WHERE id = ?');
                
                try {
                    $stmt->execute([$occupant_name, $occupant_email, $room_id, $id]);
                    $success = 'Occupant updated successfully!';
                    // Refresh data
                    $stmt = $pdo->prepare('SELECT * FROM occupants WHERE id = ?');
                    $stmt->execute([$id]);
                    $item = $stmt->fetch();
                } catch (PDOException $e) {
                    $error = 'Failed to update occupant';
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<?php require_once '../includes/header.php'; ?>

<div class="edit-container">
    <h2>Edit <?php echo $item_type === 'room' ? 'Room' : 'Occupant'; ?></h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo escape($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo escape($success); ?></div>
    <?php endif; ?>
    
    <?php if ($item_type === 'room'): ?>
        <form method="POST" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label for="room_no">Room Number</label>
                    <input type="text" id="room_no" name="room_no" required value="<?php echo escape($item['room_no']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="type">Room Type</label>
                    <select id="type" name="type" required>
                        <option value="Single" <?php echo $item['type'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                        <option value="Double" <?php echo $item['type'] === 'Double' ? 'selected' : ''; ?>>Double</option>
                        <option value="Dormitory" <?php echo $item['type'] === 'Dormitory' ? 'selected' : ''; ?>>Dormitory</option>
                        <option value="Suite" <?php echo $item['type'] === 'Suite' ? 'selected' : ''; ?>>Suite</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" required value="<?php echo escape($item['capacity']); ?>" min="1">
                </div>
                
                <div class="form-group">
                    <label for="price">Price (per night)</label>
                    <input type="number" id="price" name="price" required value="<?php echo escape($item['price']); ?>" step="0.01" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="available" <?php echo $item['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="occupied" <?php echo $item['status'] === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                    <option value="maintenance" <?php echo $item['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                </select>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Room</button>
                <a href="add.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    
    <?php else: ?>
        <form method="POST" class="form">
            <div class="form-group">
                <label for="occupant_name">Name</label>
                <input type="text" id="occupant_name" name="occupant_name" required value="<?php echo escape($item['name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="occupant_email">Email</label>
                <input type="email" id="occupant_email" name="occupant_email" required value="<?php echo escape($item['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="room_id">Room</label>
                <select id="room_id" name="room_id" required>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>" <?php echo $item['room_id'] == $room['id'] ? 'selected' : ''; ?>>
                            Room <?php echo escape($room['room_no']); ?> (<?php echo escape($room['type']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Occupant</button>
                <a href="add.php?action=occupants" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>