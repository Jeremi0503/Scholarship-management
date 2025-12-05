<?php
session_start();
include 'security_headers.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

include 'Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT am.message, am.created_at, am.is_read, u.first_name, u.last_name 
              FROM admin_messages am 
              JOIN users u ON am.admin_id = u.id 
              WHERE am.user_id = :user_id 
              ORDER BY am.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages);
} catch (Exception $e) {
    echo json_encode([]);
}
?>