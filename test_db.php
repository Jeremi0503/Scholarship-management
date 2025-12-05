<?php
include 'security_headers.php';
?>

<?php
include 'Database.php';
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Database connection successful!";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>