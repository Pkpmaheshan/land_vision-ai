<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$user = currentUser();
$stmt = $pdo->prepare('SELECT p.*, u.name AS owner_name FROM projects p JOIN users u ON p.user_id = u.id WHERE p.id = ? AND p.user_id = ?');
$stmt->execute([$id, $user['id']]);
$project = $stmt->fetch();
if (!$project) { flash('error','Project not found.'); redirect('dashboard.php'); }
$report = json_decode($project['ai_report_json'], true) ?: [];
$best = $report['best_idea'] ?? [];
$costs = $report['costs'] ?? [];
$income = $report['income'] ?? [];
$roi = $report['roi'] ?? [];
function heatLevel($level){ return strtolower(str_replace([' / ',' '], ['-','-'], (string)$level)); }
?>
<div class="premium-head">
    <div>
        <p class="eyebrow">Premium AI Feasibility Consultant</p>
        <h1><?= e($project['land_name']) ?></h1>
        <p class="muted">Owner: <?= e($project['owner_name']) ?> • <?= e($project['district']) ?> • <?= e($project['land_size_perches']) ?> perches</p>
    </div>
    <div class="head-actions">
        <a class="btn outline" href="report.php?id=<?= e($project['id']) ?>" target="_blank">Download / Print PDF</a>
        <a class="btn" href="dashboard.php">Back</a>
    </div>
</div>

<section class="decision-hero">
    <div class="hero-image">
        <?php if ($project['image_path']): ?><img src="<?= e($project['image_path']) ?>" alt="Land image"><?php else: ?><div class="image-placeholder">No Image</div><?php endif; ?>
    </div>
    <div class="decision-card">
        <span class="badge">Recommended Business</span>
        <h2><?= e($best['name'] ?? 'Business Model') ?></h2>
        <p><?= e($report['summary'] ?? '') ?></p>
        <div class="decision-stats">
            <div><span>Budget</span><strong><?= money($project['budget']) ?></strong></div>
            <div><span>AI Score</span><strong><?= e($best['score'] ?? '-') ?></strong></div>
            <div><span>Net Profit</span><strong><?= money($roi['estimated_net_profit'] ?? 0) ?></strong></div>
            <div><span>Payback</span><strong><?= e($roi['payback_period_months'] ?? '-') ?> mo</strong></div>
        </div>
    </div>
</section>

<?php if (!empty($report['potential_scores'])): ?>
<section class="score-strip">
    <?php foreach ($report['potential_scores'] as $s): ?>
        <div class="score-pill"><span><?= e($s['label']) ?></span><strong><?= e($s['score']) ?>/100</strong><em><?= e($s['level']) ?></em></div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<div class="tabs">
    <button class="tab-btn active" data-tab="overview">Overview</button>
    <button class="tab-btn" data-tab="finance">Financial Plan</button>
    <button class="tab-btn" data-tab="layout">Layout Plan</button>
    <button class="tab-btn" data-tab="risk">Risk Plan</button>
    <button class="tab-btn" data-tab="actions">Action Plan</button>
</div>

<section id="overview" class="tab-panel active">
    <div class="grid-2">
        <div class="panel premium-panel">
            <h2>Why this business?</h2>
            <ul class="tick-list"><?php foreach (($best['reasons'] ?? []) as $reason): ?><li><?= e($reason) ?></li><?php endforeach; ?></ul>
        </div>
        <div class="panel premium-panel">
            <h2>Scenario Comparison</h2>
            <?php foreach (($report['scenario_comparison'] ?? []) as $sc): ?>
                <div class="scenario-card"><div><strong><?= e($sc['plan']) ?></strong><p><?= e($sc['best_for']) ?></p></div><div><span><?= money($sc['investment']) ?></span><em><?= money($sc['monthly_profit']) ?>/mo profit</em></div></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel premium-panel">
        <h2>Business Comparison</h2>
        <div class="table-wrap"><table><thead><tr><th>Business Idea</th><th>Cost</th><th>Income</th><th>Risk</th><th>Decision</th></tr></thead><tbody>
        <?php foreach (($report['business_comparison'] ?? []) as $row): ?><tr><td><?= e($row['idea']) ?></td><td><?= e($row['cost_level']) ?></td><td><?= e($row['income_potential']) ?></td><td><?= e($row['risk_level']) ?></td><td><span class="badge small-badge"><?= e($row['recommendation']) ?></span></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </div>
</section>

<section id="finance" class="tab-panel">
    <div class="grid-2">
        <div class="panel premium-panel">
            <h2>Financial Feasibility</h2>
            <div class="finance-grid">
                <div><span>Investment</span><strong><?= money($roi['estimated_investment'] ?? $project['budget']) ?></strong></div>
                <div><span>Monthly Revenue</span><strong><?= money($income['monthly_revenue_low'] ?? 0) ?> - <?= money($income['monthly_revenue_high'] ?? 0) ?></strong></div>
                <div><span>Monthly Expenses</span><strong><?= money($roi['estimated_monthly_expenses'] ?? 0) ?></strong></div>
                <div><span>Net Profit</span><strong><?= money($roi['estimated_net_profit'] ?? 0) ?></strong></div>
                <div><span>Payback Period</span><strong><?= e($roi['payback_period_months'] ?? '-') ?> months</strong></div>
                <div><span>Break-even</span><strong><?= e($report['break_even']['summary'] ?? '-') ?></strong></div>
            </div>
        </div>
        <div class="panel premium-panel">
            <h2>Income Streams</h2>
            <ul class="tick-list"><?php foreach (($income['income_streams'] ?? []) as $stream): ?><li><?= e($stream) ?></li><?php endforeach; ?></ul>
            <p class="muted"><?= e($income['assumption'] ?? '') ?></p>
        </div>
    </div>
    <div class="panel premium-panel">
        <h2>Cost Estimate</h2>
        <div class="table-wrap"><table><thead><tr><th>Item</th><th>Estimated Cost</th></tr></thead><tbody>
        <?php foreach (($costs['items'] ?? []) as $item): ?><tr><td><?= e($item['item']) ?></td><td><?= money($item['cost']) ?></td></tr><?php endforeach; ?>
        </tbody><tfoot><tr><th>Total</th><th><?= money($costs['estimated_total'] ?? 0) ?></th></tr><tr><th>Budget Gap</th><th><?= money($costs['budget_gap'] ?? 0) ?></th></tr></tfoot></table></div>
        <p class="note"><b><?= e($costs['budget_status'] ?? '') ?>:</b> <?= e($costs['note'] ?? '') ?></p>
    </div>
</section>

<section id="layout" class="tab-panel">
    <div class="panel premium-panel">
        <h2>Visual Master Plan</h2>
        <div class="master-plan">
            <?php foreach (($report['layout_diagram'] ?? []) as $step): ?><div><?= e($step) ?></div><span>↓</span><?php endforeach; ?>
        </div>
    </div>
    <div class="panel premium-panel">
        <h2>Suggested Zones</h2>
        <div class="layout-grid"><?php foreach (($report['layout'] ?? []) as $i => $zone): ?><div class="layout-zone"><span><?= $i + 1 ?></span><h3><?= e($zone['zone']) ?></h3><p><?= e($zone['purpose']) ?></p></div><?php endforeach; ?></div>
    </div>
</section>

<section id="risk" class="tab-panel">
    <div class="grid-2">
        <div class="panel premium-panel">
            <h2>Risk Heatmap</h2>
            <?php foreach (($report['risk_heatmap'] ?? []) as $risk): ?><div class="heat-row"><span><?= e($risk['risk']) ?></span><strong class="heat <?= e(heatLevel($risk['level'])) ?>"><?= e($risk['level']) ?></strong></div><?php endforeach; ?>
        </div>
        <div class="panel premium-panel">
            <h2>SWOT Analysis</h2>
            <div class="swot-grid"><?php foreach (['strengths'=>'Strengths','weaknesses'=>'Weaknesses','opportunities'=>'Opportunities','threats'=>'Threats'] as $key=>$label): ?><div><h3><?= e($label) ?></h3><ul><?php foreach (($report['swot'][$key] ?? []) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?></ul></div><?php endforeach; ?></div>
        </div>
    </div>
    <div class="panel premium-panel"><h2>Risk + Solution</h2><?php foreach (($report['risks'] ?? []) as $risk): ?><div class="risk-card"><strong><?= e($risk['risk']) ?></strong><p><?= e($risk['solution']) ?></p></div><?php endforeach; ?></div>
</section>

<section id="actions" class="tab-panel">
    <div class="grid-2">
        <div class="panel premium-panel">
            <h2>Development Phases</h2>
            <?php foreach (($report['phases'] ?? []) as $phase): ?><div class="phase-card"><span><?= e($phase['phase']) ?></span><p><?= e($phase['work']) ?></p></div><?php endforeach; ?>
        </div>
        <div class="panel premium-panel">
            <h2>Marketing Plan</h2>
            <ul class="tick-list"><?php foreach (($report['marketing'] ?? []) as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
        </div>
    </div>
    <div class="panel final-advice"><h2>Final AI Advice</h2><p><?= e($report['final_advice'] ?? '') ?></p></div>
</section>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => btn.addEventListener('click', () => {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(btn.dataset.tab).classList.add('active');
}));
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
