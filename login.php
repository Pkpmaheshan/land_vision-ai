<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ];
        flash('success', 'Welcome back, ' . $user['name'] . '!');
        redirect('dashboard.php');
    } else {
        flash('error', 'Invalid email or password.');
        redirect('login.php');
    }
}
?>
<div class="auth-card">
    <h2>Login</h2>
    <p class="muted">Use your account to create and manage land plans.</p>
    <form method="post" class="form">
        <label>Email</label>
        <input type="email" name="email" required placeholder="you@example.com">
        <label>Password</label>
        <input type="password" name="password" required placeholder="••••••••">
        <button class="btn full" type="submit">Login</button>
    </form>
    <p class="center">No account? <a href="register.php">Register here</a></p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
