<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_super_admin']) || !$_SESSION['is_super_admin']) {
    header("Location: LoginPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - One Cainta College</title>
    <link rel="stylesheet" href="SuperAdminStyle.css">
    
</head>
<body>
    <header>
        <div class="header-content">
            <img class="logo" src="images/School.jpg" alt="sLogo">
            <div class="header-info">
                <h1>Super Admin Panel</h1>
                <p>Welcome, <?php echo $_SESSION['user_name']; ?>!</p>
            </div>
        </div>
        <nav>   
            <a href="Homepage.php">Home</a>
            <a href="change_password.php" class="nav-profile-btn">Change Password</a>
            <a href="logout.php" class="nav-logout-btn">Logout</a>
        </nav>
    </header>

    <div class="super-admin-container">
        <div class="welcome-section">
            <h2>Super Admin Dashboard</h2>
            <p>Manage all users, admin accounts, and system settings.</p>
        </div>

        <div class="admin-content">
            <!-- Create Admin Account Section -->
            <div class="admin-section">
                <h3>Create New Admin Account</h3>
                <form method="POST" action="super_admin_actions.php" class="admin-form">
                    <input type="hidden" name="action" value="create_admin">
                    <div class="form-row">
                        <div class="input-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="input-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-row">
                        <div class="input-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="input-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" class="create-admin-btn">Create Admin Account</button>
                </form>
            </div>

            <!-- All Users Management Section -->
            <div class="admin-section">
                <h3>User Management</h3>
                <div class="users-list">
                    <?php
                    include 'Database.php';
                    try {
                        $database = new Database();
                        $db = $database->getConnection();
                        
                        $query = "SELECT id, first_name, last_name, email, is_admin, is_super_admin, approved, created_at 
                                 FROM users ORDER BY created_at DESC";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $user_type = '';
                                if ($row['is_super_admin']) {
                                    $user_type = 'Super Admin';
                                    $type_class = 'super-admin';
                                } elseif ($row['is_admin']) {
                                    $user_type = 'Admin';
                                    $type_class = 'admin';
                                } else {
                                    $user_type = 'Student';
                                    $type_class = 'student';
                                }
                                
                                echo '<div class="user-card ' . $type_class . '">';
                                echo '<div class="user-header">';
                                echo '<h4>' . $row['first_name'] . ' ' . $row['last_name'] . '</h4>';
                                echo '<span class="user-type ' . $type_class . '">' . $user_type . '</span>';
                                echo '</div>';
                                echo '<p><strong>Email:</strong> ' . $row['email'] . '</p>';
                                echo '<p><strong>Status:</strong> ' . ($row['approved'] ? 'Approved' : 'Pending') . '</p>';
                                echo '<p><strong>Joined:</strong> ' . $row['created_at'] . '</p>';
                                
                                echo '<div class="user-actions">';
                                if (!$row['is_super_admin']) {
                                    echo '<button onclick="deleteUser(' . $row['id'] . ', \'' . $user_type . '\')" class="delete-btn">Delete ' . $user_type . '</button>';
                                }
                                if ($row['is_admin'] && !$row['is_super_admin']) {
                                    echo '<button onclick="demoteAdmin(' . $row['id'] . ')" class="demote-btn">Demote to Student</button>';
                                    echo '<button onclick="changeAdminPassword(' . $row['id'] . ', \'' . $row['email'] . '\')" class="password-btn">Change Password</button>';
                                }
                                if (!$row['is_admin'] && !$row['is_super_admin']) {
                                    echo '<button onclick="promoteToAdmin(' . $row['id'] . ')" class="promote-btn">Promote to Admin</button>';
                                }
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                    } catch (Exception $e) {
                        echo '<p>Error loading users: ' . $e->getMessage() . '</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- System Statistics -->
            <div class="admin-section">
                <h3>System Statistics</h3>
                <div class="stats-grid">
                    <?php
                    try {
                        // Total users
                        $query = "SELECT COUNT(*) as total_users FROM users";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

                        // Total admins
                        $query = "SELECT COUNT(*) as total_admins FROM users WHERE is_admin = TRUE";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $total_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total_admins'];

                        // Total super admins
                        $query = "SELECT COUNT(*) as total_super_admins FROM users WHERE is_super_admin = TRUE";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $total_super_admins = $stmt->fetch(PDO::FETCH_ASSOC)['total_super_admins'];

                        // Total applications
                        $query = "SELECT COUNT(*) as total_applications FROM scholarship_applications";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $total_applications = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];

                        // Pending applications
                        $query = "SELECT COUNT(*) as pending_applications FROM scholarship_applications WHERE status = 'pending'";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $pending_applications = $stmt->fetch(PDO::FETCH_ASSOC)['pending_applications'];

                        echo '<div class="stat-card">';
                        echo '<h4>Total Users</h4>';
                        echo '<p class="stat-number">' . $total_users . '</p>';
                        echo '</div>';

                        echo '<div class="stat-card">';
                        echo '<h4>Total Admins</h4>';
                        echo '<p class="stat-number">' . $total_admins . '</p>';
                        echo '</div>';

                        echo '<div class="stat-card">';
                        echo '<h4>Super Admins</h4>';
                        echo '<p class="stat-number">' . $total_super_admins . '</p>';
                        echo '</div>';

                        echo '<div class="stat-card">';
                        echo '<h4>Total Applications</h4>';
                        echo '<p class="stat-number">' . $total_applications . '</p>';
                        echo '</div>';

                        echo '<div class="stat-card">';
                        echo '<h4>Pending Applications</h4>';
                        echo '<p class="stat-number">' . $pending_applications . '</p>';
                        echo '</div>';

                    } catch (Exception $e) {
                        echo '<p>Error loading statistics: ' . $e->getMessage() . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Admin Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideChangePasswordModal()">&times;</span>
            <h3>Change Admin Password</h3>
            <form id="changePasswordForm">
                <input type="hidden" id="changePasswordUserId" name="user_id">
                <div class="input-group">
                    <label for="adminEmail">Admin Email:</label>
                    <input type="text" id="adminEmail" readonly style="background: #1e293b; color: #64748b; cursor: not-allowed;">
                </div>
                <div class="input-group">
                    <label for="newPassword">New Password *</label>
                    <input type="password" id="newPassword" name="new_password" required>
                    <small>Password must be at least 6 characters long</small>
                </div>
                <div class="input-group">
                    <label for="confirmNewPassword">Confirm New Password *</label>
                    <input type="password" id="confirmNewPassword" name="confirm_new_password" required>
                </div>
                <button type="submit" class="send-btn">Change Password</button>
            </form>
        </div>
    </div>

    <script>
        function deleteUser(userId, userType) {
            if (confirm('Are you sure you want to delete this ' + userType + '? This will also delete their applications.')) {
                fetch('super_admin_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=delete_user&user_id=' + userId
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(userType + ' deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function promoteToAdmin(userId) {
            if (confirm('Are you sure you want to promote this user to admin?')) {
                fetch('super_admin_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=promote_to_admin&user_id=' + userId
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User promoted to admin successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function demoteAdmin(userId) {
            if (confirm('Are you sure you want to demote this admin to regular user?')) {
                fetch('super_admin_actions.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=demote_admin&user_id=' + userId
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Admin demoted to student successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function changeAdminPassword(userId, email) {
            document.getElementById('changePasswordUserId').value = userId;
            document.getElementById('adminEmail').value = email;
            
            const modal = document.getElementById('changePasswordModal');
            modal.style.display = 'block';
            
            // Prevent body scroll when modal is open
            document.body.classList.add('modal-open');
        }

        function hideChangePasswordModal() {
            const modal = document.getElementById('changePasswordModal');
            modal.style.display = 'none';
            
            // Restore body scroll
            document.body.classList.remove('modal-open');
            
            // Reset form
            document.getElementById('changePasswordForm').reset();
        }

        // Fixed modal close functionality
        document.querySelectorAll('.modal .close').forEach(function(closeBtn) {
            closeBtn.onclick = function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    // Reset any forms in the modal
                    const form = modal.querySelector('form');
                    if (form) {
                        form.reset();
                    }
                }
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const changePasswordModal = document.getElementById('changePasswordModal');
            if (event.target == changePasswordModal) {
                hideChangePasswordModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideChangePasswordModal();
            }
        });

        // Form submission handling for create admin
        document.querySelector('.admin-form').onsubmit = function(e) {
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
        };

        // Form submission for change admin password
        document.getElementById('changePasswordForm').onsubmit = function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmNewPassword = document.getElementById('confirmNewPassword').value;
            const userId = document.getElementById('changePasswordUserId').value;
            
            if (newPassword !== confirmNewPassword) {
                alert('Passwords do not match!');
                return false;
            }
            
            if (newPassword.length < 6) {
                alert('Password must be at least 6 characters long!');
                return false;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'change_admin_password');
            
            fetch('super_admin_actions.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Admin password changed successfully!');
                    hideChangePasswordModal();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        };
    </script>
</body>
</html>