<?php
// Security & Helper Functions

// Session Management
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Generate CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize Input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Escape Output
function escape($output) {
    return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
}

// Check Authentication
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Get Current User
function getCurrentUser() {
    if (isAuthenticated()) {
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'name' => $_SESSION['name'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

// Check if User is Admin
function isAdmin() {
    return isAuthenticated() && $_SESSION['role'] === 'admin';
}

// Check if User is Customer
function isCustomer() {
    return isAuthenticated() && $_SESSION['role'] === 'customer';
}

// Redirect to Login if Not Authenticated
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: /hostel_booking_system/public/login.php');
        exit;
    }
}

// Redirect if Not Admin
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('Location: /hostel_booking_system/public/index.php');
        exit;
    }
}

// Redirect if Not Customer
function requireCustomer() {
    requireAuth();
    if (!isCustomer()) {
        header('Location: /hostel_booking_system/public/index.php');
        exit;
    }
}

// Hash Password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify Password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Logout User
function logout() {
    $_SESSION = [];
    session_destroy();
    header('Location: /hostel_booking_system/public/login.php');
    exit;
}

// JSON Response Helper
function jsonResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Flash Message Helper
function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Get Flash Message
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>