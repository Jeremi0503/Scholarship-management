<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - One Cainta College</title>
    <link rel="stylesheet" href="SuperAdminStyle.css">
</head>
<body>
    <header>
        <div class="header-content">
            <img class="logo" src="Image/CaintaLogo.jpg" alt="Cainta Logo">
            <div class="header-info">
                <h1>Change Password</h1>
                <p>Update your account password</p>
            </div>
        </div>
        <nav>   
    <a href="Homepage.php">Home</a>
    <?php if ($_SESSION['is_super_admin']): ?>
        <a href="SuperAdminDashboard.php">Super Admin</a>
    <?php endif; ?>
    <?php if ($_SESSION['is_admin']): ?>
        <a href="AdminDashboard.php">Admin Dashboard</a>
    <?php else: ?>
        <a href="Dashboard.php">Dashboard</a>
    <?php endif; ?>
    <a href="logout.php" class="nav-logout-btn">Logout</a>
</nav>
    </header>

    <div class="password-container">
        <div class="password-form">
            <h2>Change Your Password</h2>
            
            <?php
            if (isset($_SESSION['password_error'])) {
                echo '<div class="error-message">'.$_SESSION['password_error'].'</div>';
                unset($_SESSION['password_error']);
            }
            
            if (isset($_SESSION['password_success'])) {
                echo '<div class="success-message">'.$_SESSION['password_success'].'</div>';
                unset($_SESSION['password_success']);
            }
            ?>

            <form method="POST" action="change_password_process.php">
                <div class="input-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="input-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <small>Password must be at least 6 characters long</small>
                </div>
                
                <div class="input-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="change-password-btn">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>