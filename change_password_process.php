<?php
session_start();
include 'security_headers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}

if ($_POST) {
    try {
        include 'Database.php';
        $database = new Database();
        $db = $database->getConnection();

        $user_id = $_SESSION['user_id'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception("All fields are required.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("New passwords do not match.");
        }

        if (strlen($new_password) < 6) {
            throw new Exception("New password must be at least 6 characters long.");
        }

        // Verify current password
        $query = "SELECT password FROM users WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($current_password, $row['password'])) {
                throw new Exception("Current password is incorrect.");
            }

            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = :password WHERE id = :user_id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':password', $hashed_password);
            $update_stmt->bindParam(':user_id', $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['password_success'] = "Password changed successfully!";
            } else {
                throw new Exception("Failed to update password. Please try again.");
            }
        } else {
            throw new Exception("User not found.");
        }

    } catch (Exception $e) {
        $_SESSION['password_error'] = $e->getMessage();
    }
}

header("Location: change_password.php");
exit();
?>