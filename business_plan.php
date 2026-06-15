<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$user = currentUser();
$stmt = $pdo->prepare('SELECT p.*, u.name AS owner_name FROM projects p JOIN users u ON p.user_id=u.id WHERE p.id=? AND p.user_id=?');
$stmt->execute([$id,$user['id']]);
$project = $stmt->fetch();
if (!$project) die('<h2>Project not found</h2>');
$report = json_decode($project['ai_report_json'], true) ?: [];
$bp = $report['business_plan_export'] ?? [];
?>
<div class="page-head print-hide">
  <div><p class="eyebrow">Export</p><h1>Business Plan Export</h1><p class="muted">Printable investor/business proposal generated from the AI report.</p></div>
  <div class="head-actions"><button class="btn" onclick="window.print()">Download / Save as PDF</button><a class="btn outline" href="project_view.php?id=<?= e($project['id']) ?>">Back</a></div>
</div>
<section class="panel printable-report">
  <h1><?= e($bp['title'] ?? ($project['land_name'].' Business Plan')) ?></h1>
  <p><b>Prepared for:</b> <?= e($project['owner_name']) ?> | <b>Location:</b> <?= e($project['district']) ?> | <b>Budget:</b> <?= money($project['budget']) ?></p>
  <?php foreach (($bp['sections'] ?? []) as $title => $text): ?>
    <h2><?= e($title) ?></h2><p><?= e($text) ?></p>
  <?php endforeach; ?>
  <h2>Investor Pitch</h2><p><?= e($report['investor_pitch'] ?? '') ?></p>
  <h2>30 Day Action Plan</h2>
  <?php foreach (($report['monthly_action_plan'] ?? []) as $w): ?><h3><?= e($w['week']) ?></h3><ul><?php foreach($w['tasks'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul><?php endforeach; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
