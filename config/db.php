<?php
// Database Configuration - PDO Connection
// Update these values to match your environment

define('DB_HOST', 'localhost');
define('DB_NAME', 'np02cs4a240111');
define('DB_USER', 'np02cs4a240111');
define('DB_PASS', 'sEClXUPQVB');

// define('DB_HOST', 'localhost');
// define('DB_NAME', 'host_booking_db');
// define('DB_USER', 'root');
// define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Log error for debugging
    error_log('Database Connection Error: ' . $e->getMessage());
    // Show user-friendly message
    die('Database Connection Failed: ' . $e->getMessage());
}

// Optional: provide a mysqli connection if other parts of the app expect it
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die('MySQLi Connection failed: ' . $mysqli->connect_error);
}
?>