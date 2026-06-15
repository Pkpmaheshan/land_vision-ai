<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($name) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        flash('error', 'Please enter valid details. Password must be at least 6 characters.');
        redirect('register.php');
    }
    if ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
        redirect('register.php');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Account created. Please login.');
        redirect('login.php');
    } catch (PDOException $e) {
        flash('error', 'Email already registered or database error.');
        redirect('register.php');
    }
}
?>
<div class="auth-card">
    <h2>Create Account</h2>
    <p class="muted">Register and generate AI land business reports.</p>
    <form method="post" class="form">
        <label>Full Name</label>
        <input type="text" name="name" required placeholder="Your name">
        <label>Email</label>
        <input type="email" name="email" required placeholder="you@example.com">
        <label>Password</label>
        <input type="password" name="password" required minlength="6" placeholder="Minimum 6 characters">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required minlength="6" placeholder="Confirm password">
        <button class="btn full" type="submit">Register</button>
    </form>
    <p class="center">Already registered? <a href="login.php">Login</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
