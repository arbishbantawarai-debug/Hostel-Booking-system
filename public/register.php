<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();

// If already logged in, redirect
if (isAuthenticated()) {
    if (isAdmin()) {
        header('Location: admin.php');
    } else {
        header('Location: search.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF token validation failed';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = sanitize($_POST['password'] ?? '');
        $phone = sanitize($_POST['phone']);

        $confirm_password = sanitize($_POST['confirm_password'] ?? '');
        
        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'All fields are required';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email already registered';
            } else {
                $hashed_password = hashPassword($password);
                $stmt = $pdo->prepare('INSERT INTO users (name, phone, email, password, role) VALUES (?, ?, ?, ?, ?)');
                
                try {
                    $stmt->execute([$name, $phone, $email, $hashed_password, 'customer']);
                    flash('success', 'Registration successful! You can now login.');
                    header('Location: login.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<?php require_once '../includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Register as Customer</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" class="form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Minimum 6 characters">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
       
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>