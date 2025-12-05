<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST) {
    try {
        if (!file_exists('Database.php')) {
            throw new Exception("Database configuration file not found.");
        }
        
        include 'Database.php';

        $database = new Database();
        $db = $database->getConnection();

        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email', 'password', 'confirm_password'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        // Get form data
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        // Validate passwords match
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long.");
        }

        // Check if email already exists
        $check_email_query = "SELECT id FROM users WHERE email = :email";
        $stmt = $db->prepare($check_email_query);
        $stmt->bindParam(':email', $email);
        
        if (!$stmt->execute()) {
            throw new Exception("Database error: Unable to check email.");
        }

        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists. Please use a different email.");
        }

        // Handle profile picture upload
        $profile_picture_path = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = "uploads/profile_pictures/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_profile.' . $file_extension;
            $profile_picture_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture_path)) {
                throw new Exception("Failed to upload profile picture.");
            }
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email is admin email
        $is_admin = (strpos($email, 'admin@onecainta.edu.ph') !== false);

        // Insert user - auto-approved
        $query = "INSERT INTO users 
                  SET first_name=:first_name, middle_name=:middle_name, 
                  last_name=:last_name, email=:email, password=:password, 
                  profile_picture=:profile_picture,
                  is_admin=:is_admin, approved=TRUE, created_at=NOW()";

        $stmt = $db->prepare($query);

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':profile_picture', $profile_picture_path);
        $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Account created successfully! You can now login.";
            header("Location: LoginPage.php");
            exit();
        } else {
            // Clean up uploaded file if database insert fails
            if ($profile_picture_path && file_exists($profile_picture_path)) {
                unlink($profile_picture_path);
            }
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Unable to register. Error: " . $errorInfo[2]);
        }

    } catch (Exception $exception) {
        $_SESSION['error_message'] = $exception->getMessage();
        header("Location: Register.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: Register.php");
    exit();
}
?>