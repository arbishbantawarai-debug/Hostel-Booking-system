<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

initSession();

// Default variables
$email = '';
$password = '';
$error = '';

// Redirect if already logged in
if (isAuthenticated()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: search.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF token validation failed';
    } else {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required';
        } else {
            $stmt = $pdo->prepare(
                'SELECT id, name, email, password, role FROM users WHERE email = ?'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // ✅ SAME LOGIN → DIFFERENT DASHBOARD
                if ($user['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: search.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<?php require_once '../includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo escape($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p class="auth-link">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
