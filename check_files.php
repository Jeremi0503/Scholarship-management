<?php
$files = [
    'login_process.php',
    'Database.php',
    'LoginPage.php',
    'Dashboard.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists<br>";
    } else {
        echo "✗ $file NOT FOUND<br>";
    }
}
?>