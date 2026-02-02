<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();
?>
<?php require_once '../includes/header.php'; ?>

<div class="homepage">
    <div class="hero-section">
        <h2>Welcome to Hostel Booking System</h2>
        <p>Find and book your perfect room with ease</p>
        
        <?php if (!isAuthenticated()): ?>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-large">Login</a>
                <a href="register.php" class="btn btn-warning btn-large">Register as Customer</a>
            </div>
        <?php else: ?>
            <p class="customer-welcome">Ready to find your next room? Let's get started!</p>
            <a href="search.php" class="btn btn-primary btn-large">Search & Book Rooms</a>
        <?php endif; ?>
    </div>
    
    <section class="features-section">
        <h3>Why Choose Us?</h3>
        <div class="features-grid">
            <div class="feature">
                <h4>🔍 Easy Search</h4>
                <p>Find rooms by type, capacity, and availability in seconds</p>
            </div>
            <div class="feature">
                <h4>📅 Simple Booking</h4>
                <p>Book your room with just a few clicks using our calendar interface</p>
            </div>
            <div class="feature">
                <h4>🔒 Secure</h4>
                <p>Your data is safe with our advanced security features</p>
            </div>
            <div class="feature">
                <h4>⚡ Real-time</h4>
                <p>Get instant availability updates and booking confirmations</p>
            </div>
        </div>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>