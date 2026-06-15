<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$user = currentUser();
$stmt=$pdo->prepare('SELECT * FROM projects WHERE id=? AND user_id=?'); $stmt->execute([$id,$user['id']]);
$project=$stmt->fetch(); if(!$project) die('<h2>Project not found</h2>');
$report=json_decode($project['ai_report_json'],true) ?: [];
?>
<div class="page-head"><div><p class="eyebrow">Simulator</p><h1>Budget Simulator</h1><p class="muted">Compare micro, starter, standard and premium development options.</p></div><a class="btn" href="project_view.php?id=<?= e($project['id']) ?>">Back</a></div>
<section class="panel"><h2><?= e($project['land_name']) ?></h2><p>Current budget: <b><?= money($project['budget']) ?></b></p><div class="sim-grid">
<?php foreach(($report['budget_simulator'] ?? []) as $row): ?><div class="sim-card"><span><?= e($row['fit']) ?></span><h3><?= e($row['budget']) ?></h3><p><?= e($row['plan']) ?></p></div><?php endforeach; ?>
</div></section>
<section class="panel"><h2>How to use this</h2><p>Use this page to explain that the same land can be developed in different budget levels. The system recommends not spending all money at once; start with a revenue-generating version and improve after customer demand is proven.</p></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
