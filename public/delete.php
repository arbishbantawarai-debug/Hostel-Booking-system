<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
requireAdmin();

$id = sanitize($_GET['id'] ?? '');
$type = sanitize($_GET['type'] ?? '');

if (empty($id) || empty($type)) {
    header('Location: add.php');
    exit;
}

// Delete based on type
if ($type === 'room') {
    try {
        $stmt = $pdo->prepare('DELETE FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Room deleted successfully!');
        header('Location: add.php?action=rooms');
    } catch (PDOException $e) {
        flash('error', 'Failed to delete room');
        header('Location: add.php?action=rooms');
    }
} elseif ($type === 'occupant') {
    try {
        $stmt = $pdo->prepare('DELETE FROM occupants WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Occupant deleted successfully!');
        header('Location: add.php?action=occupants');
    } catch (PDOException $e) {
        flash('error', 'Failed to delete occupant');
        header('Location: add.php?action=occupants');
    }
} else {
    header('Location: add.php');
}

exit;
?>