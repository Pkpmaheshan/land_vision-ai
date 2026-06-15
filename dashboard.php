<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
requireLogin();

$user = currentUser();
$stmt = $pdo->prepare('SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$projects = $stmt->fetchAll();
$totalProjects = count($projects);

$totalBudget = 0;
foreach ($projects as $p) $totalBudget += (float)$p['budget'];
?>
<div class="page-head">
    <div>
        <p class="eyebrow">Dashboard</p>
        <h1>Hello, <?= e($user['name']) ?></h1>
        <p class="muted">Manage your AI generated land business plans.</p>
    </div>
    <a class="btn" href="project_create.php">+ Create New Plan</a>
</div>

<section class="stats grid-3">
    <div class="stat-card">
        <span>Total Plans</span>
        <strong><?= e($totalProjects) ?></strong>
    </div>
    <div class="stat-card">
        <span>Account Status</span>
        <strong>Active</strong>
    </div>
    <div class="stat-card">
        <span>Visible Budget Total</span>
        <strong><?= money($totalBudget) ?></strong>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <h2>Recent Land Plans</h2>
        <p class="muted">Open a report to view AI recommendations, cost plan and risks.</p>
    </div>
    <?php if (!$projects): ?>
        <div class="empty">
            <h3>No plans yet</h3>
            <p>Create your first LandVision AI business plan.</p>
            <a class="btn" href="project_create.php">Create Plan</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Land Name</th>
                        <th>District</th>
                        <th>Type</th>
                        <th>Budget</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $p): ?>
                    <tr>
                        <td><?= e($p['land_name']) ?></td>
                        <td><?= e($p['district']) ?></td>
                        <td><?= e(ucwords(str_replace('_', ' ', $p['land_type']))) ?></td>
                        <td><?= money($p['budget']) ?></td>
                        <td><?= e(date('Y-m-d', strtotime($p['created_at']))) ?></td>
                        <td class="actions">
                            <a class="btn small" href="project_view.php?id=<?= e($p['id']) ?>">Open</a>
                            <a class="btn small danger" onclick="return confirm('Delete this plan?')" href="delete_project.php?id=<?= e($p['id']) ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
