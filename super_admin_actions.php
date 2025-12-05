<?php
session_start();
include 'security_headers.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_super_admin']) || !$_SESSION['is_super_admin']) {
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
        case 'create_admin':
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Check if email already exists
            $check_query = "SELECT id FROM users WHERE email = :email";
            $stmt = $db->prepare($check_query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Email already exists.']);
                break;
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert admin user (not super admin)
            $query = "INSERT INTO users 
                     SET first_name=:first_name, last_name=:last_name, email=:email, 
                     password=:password, is_admin=TRUE, is_super_admin=FALSE, approved=TRUE, created_at=NOW()";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                $admin_id = $db->lastInsertId();
                
                // Log the admin creation
                $log_query = "INSERT INTO admin_management (created_by, admin_id) VALUES (:created_by, :admin_id)";
                $log_stmt = $db->prepare($log_query);
                $log_stmt->bindParam(':created_by', $_SESSION['user_id']);
                $log_stmt->bindParam(':admin_id', $admin_id);
                $log_stmt->execute();
                
                header("Location: SuperAdminDashboard.php");
                    exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create admin account']);
            }
            break;

        case 'delete_user':
            $user_id = $_POST['user_id'];
            
            // Prevent super admin from deleting themselves
            if ($user_id == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
                break;
            }
            
            $query = "DELETE FROM users WHERE id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
            }
            break;

        case 'promote_to_admin':
            $user_id = $_POST['user_id'];
            $query = "UPDATE users SET is_admin = TRUE, approved = TRUE WHERE id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User promoted to admin successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to promote user']);
            }
            break;

        case 'demote_admin':
            $user_id = $_POST['user_id'];
            $query = "UPDATE users SET is_admin = FALSE WHERE id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Admin demoted to student successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to demote admin']);
            }
            break;

        case 'change_admin_password':
            $user_id = $_POST['user_id'];
            $new_password = $_POST['new_password'];
            
            // Validate password length
            if (strlen($new_password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
                break;
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = :password WHERE id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Admin password updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update admin password']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>