<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$user = currentUser();

$stmt = $pdo->prepare('SELECT image_path FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$project = $stmt->fetch();
if (!$project) {
    flash('error', 'Project not found or permission denied.');
    redirect('dashboard.php');
}

$del = $pdo->prepare('DELETE FROM projects WHERE id = ? AND user_id = ?');
$del->execute([$id, $user['id']]);

if (!empty($project['image_path'])) {
    $file = __DIR__ . '/' . $project['image_path'];
    if (is_file($file)) @unlink($file);
}
flash('success', 'Project deleted successfully.');
redirect('dashboard.php');
?>
