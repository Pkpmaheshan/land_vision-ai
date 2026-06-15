<?php
// LandVision AI - Basic configuration
// For XAMPP default MySQL use: user=root and empty password.

define('DB_HOST', 'localhost');
define('DB_NAME', 'landvision_ai');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'LandVision AI');
define('APP_URL', 'http://localhost/landvision-ai');

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Currency label used throughout the app.
define('CURRENCY', 'Rs.');
?>
