<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}

include 'Database.php';

if ($_POST && isset($_FILES['profile_picture'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $user_id = $_SESSION['user_id'];
        
        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = "uploads/profile_pictures/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Get old profile picture to delete
            $get_old_pic = "SELECT profile_picture FROM users WHERE id = :user_id";
            $stmt = $db->prepare($get_old_pic);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $old_pic = $stmt->fetch(PDO::FETCH_ASSOC)['profile_picture'];

            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_profile.' . $file_extension;
            $profile_picture_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture_path)) {
                // Update database
                $update_query = "UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id";
                $stmt = $db->prepare($update_query);
                $stmt->bindParam(':profile_picture', $profile_picture_path);
                $stmt->bindParam(':user_id', $user_id);
                
                if ($stmt->execute()) {
                    // Delete old profile picture
                    if ($old_pic && file_exists($old_pic)) {
                        unlink($old_pic);
                    }
                    $_SESSION['success_message'] = "Profile picture updated successfully!";
                } else {
                    unlink($profile_picture_path); // Clean up if DB update fails
                    throw new Exception("Failed to update profile picture in database.");
                }
            } else {
                throw new Exception("Failed to upload profile picture.");
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    header("Location: profile.php");
    exit();
}

// Get current user data
try {
    $database = new Database();
    $db = $database->getConnection();
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT first_name, middle_name, last_name, email, profile_picture FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $user = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - One Cainta College</title>
    <link rel="stylesheet" href="ProfileStyle.css">
</head>
<body>
    <header>
        <img class="logo" src="Image/CaintaLogo.jpg" alt="Cainta Logo">
        <nav>   
            <a href="Homepage.php">Home</a>
            <a href="Dashboard.php">Dashboard</a>
            <a href="logout.php" class="nav-logout-btn">Logout</a>
        </nav>
    </header>

    <div class="profile-container">
        <h1>Profile Settings</h1>
        
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">'.$_SESSION['error_message'].'</div>';
            unset($_SESSION['error_message']);
        }
        
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">'.$_SESSION['success_message'].'</div>';
            unset($_SESSION['success_message']);
        }
        ?>

        <div class="profile-content">
            <div class="profile-picture-section">
                <h2>Profile Picture</h2>
                <div class="current-picture">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-img">
                    <?php else: ?>
                        <div class="default-profile-img">No Image</div>
                    <?php endif; ?>
                </div>
                <form method="POST" action="profile.php" enctype="multipart/form-data">
                    <div class="input-group">
                        <label for="profile_picture">Update Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>
                    <button type="submit" class="update-btn">Update Picture</button>
                </form>
            </div>

            <div class="profile-info-section">
                <h2>Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>First Name:</label>
                        <span><?php echo htmlspecialchars($user['first_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Middle Name:</label>
                        <span><?php echo htmlspecialchars($user['middle_name'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Last Name:</label>
                        <span><?php echo htmlspecialchars($user['last_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>