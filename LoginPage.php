<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - One Cainta College</title>
    <link rel="stylesheet" href="LoginStyle.css">
</head>
<body>

    <header>
         <img class="logo" src="images/School.jpg" alt="Logo">
        <nav>   
            <a href="Homepage.php">Home</a>
            <a href="#">Services</a>
            <a href="Register.php" class="nav-register-btn">Create Account</a>
            <a href="LoginPage.php" class="nav-login-btn">Login</a>
        </nav>
    </header>

    <div class="login-container">
        <h2>Login to Your Account</h2>

        <?php
        session_start();
        // Add cache control headers
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        if (isset($_SESSION['login_error'])) {
            echo '<div class="error-message">'.$_SESSION['login_error'].'</div>';
            unset($_SESSION['login_error']);
        }
        
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">'.$_SESSION['success_message'].'</div>';
            unset($_SESSION['success_message']);
        }
        ?>

        <!-- Check if login_process.php exists -->
        <?php
        $action_file = 'login_process.php';
        if (!file_exists($action_file)) {
            echo '<div class="error-message" style="background: #ffcccc; padding: 10px; border-radius: 5px; margin-bottom: 15px;">';
            echo 'Error: Login processing file (login_process.php) not found. Please contact administrator.';
            echo '</div>';
            $action_file = '#'; // Disable form if file doesn't exist
        }
        ?>

        <form method="POST" action="<?php echo $action_file; ?>" autocomplete="off" id="loginForm">
            <label>Email</label>
            <div class="input-box">
                <input type="email" name="email" required autocomplete="email" value="">
            </div>

            <label>Password</label>
            <div class="input-box">
                <input type="password" name="password" required autocomplete="current-password" value="">
            </div>

            <div class="options">
                <label><input type="checkbox" name="remember"> Remember me</label>
                <a href="#" class="forgot">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn" <?php echo (!file_exists('login_process.php')) ? 'disabled' : ''; ?>>Login</button>

            <p class="register-text">Don't have an account? <a href="Register.php">Create Account</a></p>
        </form>
    </div>

    <script>
        // Clear form fields on page load
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            if (form) {
                form.reset();
            }
            
            // Specifically clear password field
            const passwordField = document.querySelector('input[name="password"]');
            if (passwordField) {
                passwordField.value = '';
            }
            
            // Clear email field
            const emailField = document.querySelector('input[name="email"]');
            if (emailField) {
                emailField.value = '';
            }
        });
    </script>

</body>
</html>