<?php


function createAdminAccount($first_name, $last_name, $email, $password, $middle_name = '') {
    // Database configuration
    $host = "localhost";
    $db_name = "scholarship_db";
    $username = "root";
    $password_db = "";
    
    try {
        // Create database connection
        $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Set default file paths
        $upload_dir = "uploads/";
        $school_id_file = $upload_dir . "default_admin_id.jpg";
        $grades_file = $upload_dir . "default_admin_grades.jpg";
        
        // Insert admin user
        $query = "INSERT INTO users 
                  SET first_name=:first_name, middle_name=:middle_name, 
                  last_name=:last_name, email=:email, password=:password, 
                  school_id_image=:school_id_image, grades_image=:grades_image,
                  is_admin=:is_admin, approved=:approved, created_at=NOW()";
        
        $stmt = $conn->prepare($query);
        
        $is_admin = true;
        $approved = true;
        
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':school_id_image', $school_id_file);
        $stmt->bindParam(':grades_image', $grades_file);
        $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_BOOL);
        $stmt->bindParam(':approved', $approved, PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            $admin_id = $conn->lastInsertId();
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


$admins_to_create = [
    [
        'first_name' => 'System',
        'last_name' => 'Administrator',
        'email' => 'Ham@onecainta.edu.ph',
        'password' => 'Admin123!'
    ]
];


echo "Starting internal admin account creation...\n";
echo "==========================================\n\n";

$success_count = 0;
$total_count = count($admins_to_create);

foreach ($admins_to_create as $admin) {
    echo "Creating admin: {$admin['first_name']} {$admin['last_name']}... ";
    
    $result = createAdminAccount(
        $admin['first_name'],
        $admin['last_name'], 
        $admin['email'],
        $admin['password'],
        $admin['middle_name'] ?? ''
    );
    
    if ($result['success']) {
        $success_count++;
        echo "✅ SUCCESS\n";
        echo "   Admin ID: {$result['admin_id']}\n";
        echo "   Email: {$result['email']}\n";
        echo "   Password: {$result['password']}\n";
    } else {
        echo "❌ FAILED\n";
        echo "   Error: {$result['error']}\n";
    }
    echo "\n";
}

echo "==========================================\n";
echo "Creation Summary:\n";
echo "Total attempted: $total_count\n";
echo "Successful: $success_count\n";
echo "Failed: " . ($total_count - $success_count) . "\n";

if ($success_count > 0) {
    echo "\n⚠️  IMPORTANT SECURITY NOTES:\n";
    echo "1. Change the passwords after first login\n";
    echo "2. Remove or secure this script after use\n";
    echo "3. Store passwords securely\n";
}
?>