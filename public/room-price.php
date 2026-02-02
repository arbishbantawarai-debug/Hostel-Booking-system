<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
requireCustomer();

header('Content-Type: application/json');

$room_id = sanitize($_GET['room_id'] ?? '');

if (empty($room_id)) {
    jsonResponse('error', 'Room ID is required');
}

try {
    $stmt = $pdo->prepare('SELECT price FROM rooms WHERE id = ?');
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        jsonResponse('error', 'Room not found');
    }
    
    jsonResponse('success', 'Room price retrieved', ['price' => $room['price']]);
} catch (PDOException $e) {
    jsonResponse('error', 'Database error');
}
?>