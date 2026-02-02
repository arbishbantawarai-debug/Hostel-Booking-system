<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
requireCustomer();

header('Content-Type: application/json');

$type = sanitize($_GET['type'] ?? '');
$capacity = sanitize($_GET['capacity'] ?? '');
$availability = sanitize($_GET['availability'] ?? '');

try {
    $query = 'SELECT * FROM rooms WHERE 1=1';
    $params = [];
    
    if (!empty($type)) {
        $query .= ' AND type = ?';
        $params[] = $type;
    }
    if (!empty($capacity)) {
        $query .= ' AND capacity >= ?';
        $params[] = $capacity;
    }
    if (!empty($availability)) {
        $query .= ' AND status = ?';
        $params[] = $availability;
    }
    
    $query .= ' ORDER BY room_no ASC';
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
    
    if (empty($rooms)) {
        jsonResponse('error', 'No rooms found', []);
    }
    
    jsonResponse('success', 'Rooms retrieved', ['rooms' => $rooms]);
} catch (PDOException $e) {
    jsonResponse('error', 'Database error');
}
?>