<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Booking System</title>
    <link rel="stylesheet" href="/hostel_booking_system/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h1><a href="/hostel_booking_system/public/index.php">🏨 Hostel Booking</a></h1>
            </div>
            <div class="navbar-menu">
                <?php if (isAuthenticated()): ?>
                    <span class="welcome">Welcome, <?php echo escape(getCurrentUser()['name']); ?> (<?php echo escape(getCurrentUser()['role']); ?>)</span>
                    <?php if (isAdmin()): ?>
                       
                        <a href="/hostel_booking_system/public/add.php">Manage</a>
                    <?php elseif (isCustomer()): ?>
                        <a href="/hostel_booking_system/public/search.php">Book Room</a>
                    <?php endif; ?>
                    <a href="/hostel_booking_system/public/logout.php" class="btn-logout">Logout</a>
                <?php else: ?>
                    <a href="/hostel_booking_system/public/login.php">Login</a>
                    <a href="/hostel_booking_system/public/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="container">
        <?php 
        $flash = getFlash();
        if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo escape($flash['message']); ?>
            </div>
        <?php endif; ?>