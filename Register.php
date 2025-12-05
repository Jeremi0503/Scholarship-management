<?php
include 'security_headers.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - One Cainta College Scholarship Program</title>
    <link rel="stylesheet" href="RegisterStyle.css">
</head>
<body>
    <header>
        <img class="logo" src="Image/CaintaLogo.jpg" alt="Cainta Logo">
        <nav>   
            <a href="Homepage.php">Home</a>
            <a href="#">Services</a>
            <a href="LoginPage.php" class="nav-login-btn">Login</a>
            <a href="Register.php" class="nav-register-btn active">Create Account</a>
        </nav>
    </header>

    <div class="register-container">
        <h2>Create Your Account</h2>
        
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

        <form method="POST" action="register_process.php" autocomplete="off" enctype="multipart/form-data">
            <div class="form-row">
                <div class="input-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required autocomplete="given-name">
                </div>
                <div class="input-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" autocomplete="additional-name">
                </div>
            </div>
            
            <div class="input-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" required autocomplete="family-name">
            </div>
            
            <div class="input-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>

            <!-- Profile Picture Upload -->
            <div class="input-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                <small>Upload your profile picture (optional)</small>
            </div>
            
            <div class="form-row">
                <div class="input-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                </div>
                <div class="input-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                </div>
            </div>
            
            <button type="submit" class="register-btn">Create Account</button>
            
            <p class="login-text">Already have an account? <a href="LoginPage.php">Login here</a></p>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                field.value = '';
            });
        });
    </script>
</body>
</html>