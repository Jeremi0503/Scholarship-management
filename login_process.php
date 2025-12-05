<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists('Database.php')) {
    die("Database configuration file not found.");
}

include 'Database.php';

if ($_POST) {
    try {
        $database = new Database();
        $db = $database->getConnection();

        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            throw new Exception("Please fill in all fields.");
        }

        // Check if user exists and get admin status and approval status
        $query = "SELECT id, first_name, middle_name, last_name, password, is_admin, is_super_admin, approved FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashed_password = $row['password'];
            $is_admin = $row['is_admin'];
            $is_super_admin = $row['is_super_admin'];
            $approved = $row['approved'];

            // DEBUG: Check what values we're getting
            error_log("Login attempt - Email: $email, Is Admin: $is_admin, Is Super Admin: $is_super_admin, Approved: $approved");

            // Check if account is approved
            if (!$approved && !$is_admin) {
                throw new Exception("Your account is pending admin approval. You will be notified via email once approved.");
            }

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $row['id'];
                
                $display_name = $row['first_name'];
                if (!empty($row['middle_name'])) {
                    $display_name .= ' ' . $row['middle_name'];
                }
                $display_name .= ' ' . $row['last_name'];
                
                $_SESSION['user_name'] = $display_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['is_admin'] = $is_admin;
                $_SESSION['is_super_admin'] = $is_super_admin;
                
                // DEBUG: Log session values
                error_log("Session set - User ID: " . $_SESSION['user_id'] . ", Is Super Admin: " . $_SESSION['is_super_admin']);
                
                // Redirect based on user type - check super admin first
                if ($_SESSION['is_super_admin']) {
                    error_log("Redirecting to SuperAdminDashboard.php");
                    header("Location: SuperAdminDashboard.php");
                } elseif ($is_admin) {
                    error_log("Redirecting to AdminDashboard.php");
                    header("Location: AdminDashboard.php");
                } else {
                    error_log("Redirecting to Dashboard.php");
                    header("Location: Dashboard.php");
                }
                exit();
            } else {
                throw new Exception("Invalid email or password.");
            }
        } else {
            throw new Exception("Invalid email or password.");
        }

    } catch (Exception $exception) {
        error_log("Login error: " . $exception->getMessage());
        $_SESSION['login_error'] = $exception->getMessage();
        header("Location: LoginPage.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = "Invalid request method.";
    header("Location: LoginPage.php");
    exit();
}
?>