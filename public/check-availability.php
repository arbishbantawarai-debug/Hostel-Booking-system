<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
requireCustomer();

header('Content-Type: application/json');

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);

$room_id = sanitize($input['room_id'] ?? '');
$check_in = sanitize($input['check_in'] ?? '');
$check_out = sanitize($input['check_out'] ?? '');

if (empty($room_id) || empty($check_in) || empty($check_out)) {
    jsonResponse('error', 'Missing required fields');
}

try {
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as count FROM bookings 
        WHERE room_id = ? 
        AND status = "confirmed"
        AND (check_in < ? AND check_out > ?)
    ');
    $stmt->execute([$room_id, $check_out, $check_in]);
    $result = $stmt->fetch();
    
    $available = $result['count'] == 0;
    
    jsonResponse('success', $available ? 'Room is available' : 'Room is not available', ['available' => $available]);
} catch (PDOException $e) {
    jsonResponse('error', 'Database error');
}
?>