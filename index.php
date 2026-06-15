<?php
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-text">
        <p class="eyebrow">AI Powered Land Business Planner</p>
        <h1>Convert unused land into a profitable business plan.</h1>
        <p>LandVision AI analyzes land details, budget, access, natural features and user goals to recommend the best business idea, layout, cost estimate, income prediction and risk report.</p>
        <div class="hero-actions">
            <?php if (isLoggedIn()): ?>
                <a class="btn" href="project_create.php">Create AI Plan</a>
                <a class="btn outline" href="dashboard.php">View Dashboard</a>
            <?php else: ?>
                <a class="btn" href="register.php">Start Free Demo</a>
                <a class="btn outline" href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-card glass">
        <h3>AI Report Includes</h3>
        <ul class="tick-list">
            <li>Best business recommendation</li>
            <li>Land layout zones</li>
            <li>Development cost estimate</li>
            <li>Monthly income prediction</li>
            <li>Risk analysis and solutions</li>
            <li>Printable PDF-style report</li>
        </ul>
    </div>
</section>

<section class="cards grid-3">
    <div class="card">
        <div class="icon">🏕️</div>
        <h3>Cabana / Resort Planning</h3>
        <p>River, beach or mountain lands can be converted into day outing, glamping or cabana plans.</p>
    </div>
    <div class="card">
        <div class="icon">🛣️</div>
        <h3>Road-side Business Ideas</h3>
        <p>Road-side lands can become cafes, car wash centers, mini stores or parking businesses.</p>
    </div>
    <div class="card">
        <div class="icon">📊</div>
        <h3>Investor-ready Report</h3>
        <p>Generate a professional plan that can be shown to clients, family, partners or investors.</p>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
