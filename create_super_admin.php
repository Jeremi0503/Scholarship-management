<?php
// create_super_admin.php
// Secure script to create super admin accounts
// Run this once and then DELETE the file for security

session_start();

// Security headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Only allow access from localhost or with a secret key for security
$allowed = false;
$secret_key = "SUPER_ADMIN_CREATE_2025"; // Change this to a random string

// Check if accessing from localhost or with correct secret key
if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || 
    $_SERVER['REMOTE_ADDR'] === '::1' ||
    (isset($_GET['key']) && $_GET['key'] === $secret_key)) {
    $allowed = true;
}

if (!$allowed) {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>Access Denied</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                background: #0f172a; 
                color: #f1f5f9; 
                text-align: center; 
                padding: 50px; 
            }
            .error { 
                background: #dc2626; 
                color: white; 
                padding: 20px; 
                border-radius: 8px; 
                max-width: 500px; 
                margin: 0 auto; 
            }
        </style>
    </head>
    <body>
        <div class='error'>
            <h2>Access Denied</h2>
            <p>This script can only be accessed from localhost or with a valid secret key.</p>
        </div>
    </body>
    </html>
    ");
}

function createSuperAdminAccount($first_name, $last_name, $email, $password, $middle_name = '') {
    // Database configuration - update these with your actual database credentials
    $host = "localhost";
    $db_name = "scholarship_db";
    $username = "root";
    $password_db = "";
    
    try {
        // Create database connection
        $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = :email";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            return [
                'success' => false, 
                'error' => 'Email already exists. Please use a different email.'
            ];
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Set default file paths for required fields (even though they're not used for admin)
        $upload_dir = "uploads/";
        $school_id_file = $upload_dir . "default_admin_id.jpg";
        $grades_file = $upload_dir . "default_admin_grades.jpg";
        $profile_picture = null; // No profile picture by default
        
        // Insert super admin user
        $query = "INSERT INTO users 
                  SET first_name=:first_name, middle_name=:middle_name, 
                  last_name=:last_name, email=:email, password=:password, 
                  profile_picture=:profile_picture,
                  school_id_image=:school_id_image, grades_image=:grades_image,
                  is_admin=:is_admin, is_super_admin=:is_super_admin, 
                  approved=:approved, created_at=NOW()";
        
        $stmt = $conn->prepare($query);
        
        $is_admin = true;
        $is_super_admin = true;
        $approved = true;
        
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':profile_picture', $profile_picture);
        $stmt->bindParam(':school_id_image', $school_id_file);
        $stmt->bindParam(':grades_image', $grades_file);
        $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_BOOL);
        $stmt->bindParam(':is_super_admin', $is_super_admin, PDO::PARAM_BOOL);
        $stmt->bindParam(':approved', $approved, PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            $admin_id = $conn->lastInsertId();
            
            // Create admin management record
            $log_query = "INSERT INTO admin_management (created_by, admin_id) VALUES (:created_by, :admin_id)";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bindParam(':created_by', $admin_id); // Self-created
            $log_stmt->bindParam(':admin_id', $admin_id);
            $log_stmt->execute();
            
            return [
                'success' => true,
                'admin_id' => $admin_id,
                'email' => $email,
                'password' => $password
            ];
        } else {
            return ['success' => false, 'error' => 'Failed to execute query'];
        }
        
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
    }
}

// Handle form submission
$message = '';
$success = false;

if ($_POST) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $middle_name = trim($_POST['middle_name'] ?? '');
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $message = "All required fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        // Create the super admin account
        $result = createSuperAdminAccount($first_name, $last_name, $email, $password, $middle_name);
        
        if ($result['success']) {
            $success = true;
            $message = "Super Admin account created successfully!";
        } else {
            $message = "Error: " . $result['error'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Super Admin - One Cainta College</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-hover: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
            --border-color: #334155;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }

        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .header h1 {
            color: var(--text-primary);
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            color: var(--text-secondary);
            margin-bottom: 5px;
        }

        .warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: var(--warning-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--dark-bg);
            color: var(--text-primary);
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .credentials {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }

        .credentials h3 {
            color: #6ee7b7;
            margin-bottom: 15px;
        }

        .credential-item {
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create Super Admin Account</h1>
            <p>One Cainta College Scholarship System</p>
            <p><strong>⚠️ IMPORTANT: Delete this file after use for security!</strong></p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success && isset($result)): ?>
            <div class="credentials">
                <h3>Super Admin Account Created Successfully!</h3>
                <div class="credential-item">
                    <strong>Email:</strong> <?php echo htmlspecialchars($result['email']); ?>
                </div>
                <div class="credential-item">
                    <strong>Password:</strong> <?php echo htmlspecialchars($result['password']); ?>
                </div>
                <div class="credential-item">
                    <strong>Admin ID:</strong> <?php echo htmlspecialchars($result['admin_id']); ?>
                </div>
                <p style="margin-top: 15px; color: #fca5a5;">
                    <strong>⚠️ Save these credentials and DELETE this file immediately!</strong>
                </p>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name"
                               value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required
                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Create Super Admin Account</button>
            </form>
        </div>

        <div class="warning">
            <strong>Security Notice:</strong> This file should be deleted immediately after creating the super admin account. 
            Keep the credentials secure and change the password after first login.
        </div>
    </div>

    <script>
        // Clear password fields on page load for security
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('password').value = '';
            document.getElementById('confirm_password').value = '';
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>