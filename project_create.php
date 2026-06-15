<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/ai_engine.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = currentUser();
    $data = [
        'land_name' => trim($_POST['land_name'] ?? ''),
        'district' => trim($_POST['district'] ?? ''),
        'land_size_perches' => (float)($_POST['land_size_perches'] ?? 0),
        'land_type' => $_POST['land_type'] ?? 'general',
        'budget' => (float)($_POST['budget'] ?? 0),
        'has_road_access' => isset($_POST['has_road_access']) ? 1 : 0,
        'has_river' => isset($_POST['has_river']) ? 1 : 0,
        'has_old_house' => isset($_POST['has_old_house']) ? 1 : 0,
        'target_customer' => trim($_POST['target_customer'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    // Combine advanced feasibility fields into the notes column so the database stays simple.
    $advancedNotes = [];
    $fields = [
        'nearby_attractions' => 'Nearby Attractions',
        'road_condition' => 'Road / Vehicle Access',
        'utilities' => 'Water / Electricity',
        'existing_assets' => 'Existing Assets',
        'expected_business' => 'Expected Business Type',
        'main_concern' => 'Main Concern',
        'competitors' => 'Nearby Competitors',
        'google_map_location' => 'Google Map Location',
        'land_shape' => 'Land Shape',
        'preferred_business_style' => 'Preferred Business Style',
        'customer_budget_level' => 'Customer Budget Level',
        'unique_strength' => 'Unique Strength',
    ];
    foreach ($fields as $key => $label) {
        $value = trim($_POST[$key] ?? '');
        if ($value !== '') $advancedNotes[] = $label . ': ' . $value;
    }
    if (!empty($advancedNotes)) {
        $data['notes'] = trim($data['notes'] . "\n\n" . implode("\n", $advancedNotes));
    }

    if ($data['land_name'] === '' || $data['district'] === '' || $data['land_size_perches'] <= 0 || $data['budget'] <= 0) {
        flash('error', 'Please fill land name, district, land size and budget correctly.');
        redirect('project_create.php');
    }

    $imagePath = null;
    if (!empty($_FILES['land_image']['name'])) {
        if ($_FILES['land_image']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Image upload failed.');
            redirect('project_create.php');
        }
        if ($_FILES['land_image']['size'] > MAX_UPLOAD_SIZE) {
            flash('error', 'Image size must be less than 5MB.');
            redirect('project_create.php');
        }
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['land_image']['tmp_name']);
        if (!isset($allowed[$mime])) {
            flash('error', 'Only JPG, PNG or WEBP images are allowed.');
            redirect('project_create.php');
        }
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
        $fileName = 'land_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($_FILES['land_image']['tmp_name'], UPLOAD_DIR . $fileName)) {
            flash('error', 'Could not save uploaded image.');
            redirect('project_create.php');
        }
        $imagePath = UPLOAD_URL . $fileName;
    }

    $report = generateLandPlan($data);

    $stmt = $pdo->prepare('INSERT INTO projects (user_id, land_name, district, land_size_perches, land_type, budget, has_road_access, has_river, has_old_house, target_customer, notes, image_path, ai_report_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $user['id'], $data['land_name'], $data['district'], $data['land_size_perches'], $data['land_type'], $data['budget'],
        $data['has_road_access'], $data['has_river'], $data['has_old_house'], $data['target_customer'], $data['notes'], $imagePath,
        json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    ]);

    $id = $pdo->lastInsertId();
    flash('success', 'AI land business plan generated successfully.');
    redirect('project_view.php?id=' . $id);
}
?>
<div class="page-head">
    <div>
        <p class="eyebrow">New AI Plan</p>
        <h1>Create Land Business Plan</h1>
        <p class="muted">Enter clear land details. The system will generate a clean business decision report with scores, scenarios, finance, layout, risks and action plan.</p>
    </div>
</div>

<form method="post" enctype="multipart/form-data" class="form panel wide-form">
    <div class="grid-2">
        <div>
            <label>Land / Project Name *</label>
            <input name="land_name" required placeholder="Example: River Side Land - Negombo">
        </div>
        <div>
            <label>District / City *</label>
            <input name="district" required placeholder="Example: Gampaha, Kandy, Matara">
        </div>
        <div>
            <label>Land Size *</label>
            <div class="input-with-note">
                <input type="number" name="land_size_perches" min="1" step="0.1" required placeholder="Example: 40">
                <span>perches</span>
            </div>
        </div>
        <div>
            <label>Budget *</label>
            <div class="input-with-note">
                <input type="number" name="budget" min="1" step="1000" required placeholder="Example: 2500000">
                <span>LKR</span>
            </div>
        </div>
        <div>
            <label>Land Type</label>
            <select name="land_type">
                <option value="river_side">River Side Land</option>
                <option value="road_side">Road Side Land</option>
                <option value="town">Town / Urban Land</option>
                <option value="village">Village Land</option>
                <option value="agricultural">Agricultural Land</option>
                <option value="beach">Beach Side Land</option>
                <option value="mountain">Mountain / View Land</option>
                <option value="general">General Land</option>
            </select>
        </div>
        <div>
            <label>Target Customers</label>
            <input name="target_customer" placeholder="Example: families, couples, students, tourists">
        </div>
        <div>
            <label>Expected Business Type</label>
            <input name="expected_business" placeholder="Example: cabana resort, cafe, car wash, nursery">
        </div>
        <div>
            <label>Main Concern</label>
            <select name="main_concern">
                <option value="">Select main concern</option>
                <option>Low Budget</option>
                <option>High Construction Cost</option>
                <option>Flood Risk</option>
                <option>No Business Idea</option>
                <option>Parking Problem</option>
                <option>Customer Safety</option>
                <option>Marketing Problem</option>
            </select>
        </div>
        <div>
            <label>Road / Vehicle Access</label>
            <input name="road_condition" placeholder="Example: car/van can enter, 12ft road, main road nearby">
        </div>
        <div>
            <label>Water / Electricity Availability</label>
            <input name="utilities" placeholder="Example: electricity available, well water available">
        </div>
        <div>
            <label>Nearby Attractions / Demand Points</label>
            <input name="nearby_attractions" placeholder="Example: river bathing area, hotel, school, tourist place">
        </div>
        <div>
            <label>Nearby Competitors</label>
            <input name="competitors" placeholder="Example: small hotels, cafes, bathing places, no competitors">
        </div>
        <div>
            <label>Google Map Location Link</label>
            <input name="google_map_location" placeholder="Paste Google Map link or nearest landmark">
        </div>
        <div>
            <label>Land Shape / Slope</label>
            <input name="land_shape" placeholder="Example: rectangle, long narrow land, flat, sloped, corner land">
        </div>
        <div>
            <label>Preferred Business Style</label>
            <select name="preferred_business_style">
                <option value="">Select style</option>
                <option>Low-cost starter</option>
                <option>Eco friendly / nature style</option>
                <option>Premium luxury style</option>
                <option>Family friendly style</option>
                <option>Youth / social media style</option>
            </select>
        </div>
        <div>
            <label>Customer Budget Level</label>
            <select name="customer_budget_level">
                <option value="">Select customer level</option>
                <option>Low budget customers</option>
                <option>Middle income customers</option>
                <option>Premium customers</option>
                <option>Mixed customers</option>
            </select>
        </div>
        <div>
            <label>Unique Strength</label>
            <input name="unique_strength" placeholder="Example: river view, main road visibility, old house, privacy, large parking">
        </div>
        <div>
            <label>Existing Assets</label>
            <input name="existing_assets" placeholder="Example: old house, trees, river view, open grass area">
        </div>
    </div>

    <div class="checks">
        <label><input type="checkbox" name="has_road_access" checked> Road access available</label>
        <label><input type="checkbox" name="has_river"> River / water feature available</label>
        <label><input type="checkbox" name="has_old_house"> Old house / existing building available</label>
    </div>

    <label>Land Photo / Sketch</label>
    <input type="file" name="land_image" accept="image/jpeg,image/png,image/webp">
    <p class="muted">Optional. JPG, PNG or WEBP. Max 5MB.</p>

    <label>Extra Notes</label>
    <textarea name="notes" rows="5" placeholder="Extra details: land condition, shape, slope, privacy, parking space, customer flow, special idea, limitations etc."></textarea>

    <button class="btn" type="submit">Generate Advanced AI Feasibility Report</button>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
