<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
requireLogin();
$projectId=(int)($_GET['project_id'] ?? $_POST['project_id'] ?? 0);
$user=currentUser();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $stmt=$pdo->prepare('INSERT INTO inquiries (project_id, customer_name, phone, package_name, preferred_date, advance_status, notes, status) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$projectId, trim($_POST['customer_name']??''), trim($_POST['phone']??''), trim($_POST['package_name']??''), trim($_POST['preferred_date']??''), trim($_POST['advance_status']??'Pending'), trim($_POST['notes']??''), 'New Inquiry']);
    flash('success','Inquiry added successfully.'); redirect('inquiries.php?project_id='.$projectId);
}
$stmt=$pdo->prepare('SELECT * FROM projects WHERE id=? AND user_id=?'); $stmt->execute([$projectId,$user['id']]);
$project=$stmt->fetch(); if(!$project) die('<h2>Project not found</h2>');
$inquiries=$pdo->prepare('SELECT * FROM inquiries WHERE project_id=? ORDER BY created_at DESC'); $inquiries->execute([$projectId]); $rows=$inquiries->fetchAll();
$report=json_decode($project['ai_report_json'],true) ?: [];
?>
<div class="page-head"><div><p class="eyebrow">Booking Module</p><h1>Booking / Inquiry Module</h1><p class="muted">Demo module to convert generated business plans into real customer inquiries.</p></div><a class="btn" href="project_view.php?id=<?= e($projectId) ?>">Back</a></div>
<section class="grid-2">
<form method="post" class="form panel"><input type="hidden" name="project_id" value="<?= e($projectId) ?>"><h2>Add Inquiry</h2><label>Customer Name</label><input name="customer_name" required><label>Phone Number</label><input name="phone" required><label>Package / Service</label><select name="package_name"><?php foreach(($report['packages']??[]) as $pkg): ?><option><?= e($pkg['name']) ?></option><?php endforeach; ?><option>Custom Inquiry</option></select><label>Preferred Date</label><input type="date" name="preferred_date"><label>Advance Payment Status</label><select name="advance_status"><option>Pending</option><option>Advance Paid</option><option>No Advance Required</option></select><label>Notes</label><textarea name="notes"></textarea><button class="btn">Save Inquiry</button></form>
<div class="panel"><h2>Inquiry Workflow</h2><ul class="tick-list"><?php foreach(($report['booking_module']['statuses']??[]) as $st): ?><li><?= e($st) ?></li><?php endforeach; ?></ul><p><?= e($report['booking_module']['suggestion'] ?? '') ?></p></div>
</section>
<section class="panel"><h2>Saved Inquiries</h2><div class="table-wrap"><table><thead><tr><th>Name</th><th>Phone</th><th>Package</th><th>Date</th><th>Advance</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r['customer_name']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['package_name']) ?></td><td><?= e($r['preferred_date']) ?></td><td><?= e($r['advance_status']) ?></td><td><?= e($r['status']) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
