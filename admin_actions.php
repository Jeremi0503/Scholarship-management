<?php
session_start();
include 'security_headers.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

include 'Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'approve_user':
            $user_id = $_POST['user_id'];
            $query = "UPDATE users SET approved = TRUE WHERE id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User approved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve user']);
            }
            break;

        case 'reject_user':
            $user_id = $_POST['user_id'];
            // Delete user and their applications (cascade delete should handle applications)
            $query = "DELETE FROM users WHERE id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User rejected and deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject user']);
            }
            break;

        case 'delete_user':
            $user_id = $_POST['user_id'];
            $query = "DELETE FROM users WHERE id = :user_id AND is_admin = FALSE";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
            }
            break;

        case 'approve_application':
            $application_id = $_POST['application_id'];
            $query = "UPDATE scholarship_applications SET status = 'approved' WHERE id = :application_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':application_id', $application_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Application approved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve application']);
            }
            break;

        case 'reject_application':
            $application_id = $_POST['application_id'];
            $query = "UPDATE scholarship_applications SET status = 'rejected' WHERE id = :application_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':application_id', $application_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Application rejected successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject application']);
            }
            break;

        case 'send_update':
            $application_id = $_POST['application_id'];
            $admin_message = $_POST['admin_message'];
            
            $query = "INSERT INTO application_updates (application_id, admin_message) VALUES (:application_id, :admin_message)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':application_id', $application_id);
            $stmt->bindParam(':admin_message', $admin_message);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Update sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send update']);
            }
            break;

        case 'send_message':
            $user_id = $_POST['user_id'];
            $admin_id = $_SESSION['user_id'];
            $message = $_POST['message'];
            
            $query = "INSERT INTO admin_messages (user_id, admin_id, message) VALUES (:user_id, :admin_id, :message)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->bindParam(':message', $message);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send message']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>