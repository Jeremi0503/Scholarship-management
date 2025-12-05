<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: LoginPage.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard </title>
    <link rel="stylesheet" href="AdminDashboardStyle.css">
</head>
<body>
    <header>
         <img class="logo" src="images/School.jpg" alt="Logo">
        <nav>   
            <a href="Homepage.php">Home</a>
            <a href="logout.php" class="nav-logout-btn">Logout</a>
        </nav>
    </header>

    <div class="admin-dashboard-container">
        <div class="welcome-section">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['user_name']; ?>! Manage scholarship applications and user accounts.</p>
        </div>

        <div class="admin-content">
            <!-- Pending Approvals Section -->
            <div class="admin-section">
                <h2>Pending User Approvals</h2>
                <div class="users-list">
                    <?php
                    include 'Database.php';
                    try {
                        $database = new Database();
                        $db = $database->getConnection();
                        
                        $query = "SELECT id, first_name, middle_name, last_name, email, school_id_image, grades_image, created_at 
                                 FROM users WHERE approved = FALSE AND is_admin = FALSE ORDER BY created_at DESC";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="user-card">';
                                echo '<h3>' . $row['first_name'] . ' ' . $row['last_name'] . '</h3>';
                                echo '<p><strong>Email:</strong> ' . $row['email'] . '</p>';
                                echo '<p><strong>Registered:</strong> ' . $row['created_at'] . '</p>';
                                echo '<div class="file-links">';
                                echo '<a href="' . $row['school_id_image'] . '" target="_blank">View School ID</a>';
                                echo '<a href="' . $row['grades_image'] . '" target="_blank">View Grades</a>';
                                echo '</div>';
                                echo '<div class="admin-actions">';
                                echo '<button onclick="approveUser(' . $row['id'] . ')" class="approve-btn">Approve</button>';
                                echo '<button onclick="rejectUser(' . $row['id'] . ')" class="reject-btn">Reject</button>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No pending user approvals.</p>';
                        }
                    } catch (Exception $e) {
                        echo '<p>Error loading pending users: ' . $e->getMessage() . '</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Scholarship Applications Section -->
          
            <div class="admin-section">
                <h2>Scholarship Applications</h2>
                <div class="applications-list">
                    <?php
                    try {
                        $query = "SELECT sa.*, u.email, u.approved as user_approved, u.profile_picture 
                                FROM scholarship_applications sa 
                                JOIN users u ON sa.user_id = u.id 
                                ORDER BY sa.application_date DESC";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $status_class = 'status-' . $row['status'];
                                echo '<div class="application-card ' . $status_class . '">';
                                
                                // Display profile picture if available
                                if ($row['profile_picture']) {
                                    echo '<div class="applicant-profile">';
                                    echo '<img src="' . $row['profile_picture'] . '" alt="Profile Picture" class="profile-thumb">';
                                    echo '</div>';
                                }
                                
                                echo '<h3>' . $row['first_name'] . ' ' . $row['last_name'] . '</h3>';
                                echo '<p><strong>Email:</strong> ' . $row['email'] . '</p>';
                                echo '<p><strong>Course:</strong> ' . $row['course'] . '</p>';
                                echo '<p><strong>Year Level:</strong> ' . $row['school_year'] . '</p>';
                                echo '<p><strong>Semester:</strong> ' . $row['semester'] . '</p>';
                                echo '<p><strong>Section:</strong> ' . $row['section'] . '</p>';
                                echo '<p><strong>School ID No:</strong> ' . $row['school_id_number'] . '</p>';
                                echo '<p><strong>Contact:</strong> ' . $row['contact_number'] . '</p>';
                                echo '<p><strong>Address:</strong> ' . $row['address'] . '</p>';
                                echo '<p><strong>Status:</strong> <span class="status">' . $row['status'] . '</span></p>';
                                echo '<p><strong>Applied:</strong> ' . $row['application_date'] . '</p>';
                                
                                // File links
                                echo '<div class="file-links">';
                                echo '<a href="' . $row['school_id_image'] . '" target="_blank">View School ID</a>';
                                echo '<a href="' . $row['proof_of_enrollment'] . '" target="_blank">View Proof of Enrollment</a>';
                                echo '</div>';
                                
                                if ($row['user_approved']) {
                                    echo '<div class="application-actions">';
                                    if ($row['status'] == 'pending') {
                                        echo '<button onclick="updateApplication(' . $row['id'] . ', \'approved\')" class="approve-btn">Approve Application</button>';
                                        echo '<button onclick="updateApplication(' . $row['id'] . ', \'rejected\')" class="reject-btn">Reject Application</button>';
                                    }
                                    echo '<button onclick="sendMessage(' . $row['user_id'] . ')" class="update-btn">Send Message</button>';
                                    echo '</div>';
                                } else {
                                    echo '<p class="warning">User account not approved yet</p>';
                                }
                                
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No scholarship applications found.</p>';
                        }
                    } catch (Exception $e) {
                        echo '<p>Error loading applications: ' . $e->getMessage() . '</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- All Users Section -->
            <div class="admin-section">
                <h2>All Users</h2>
                <div class="users-list">
                    <?php
                    try {
                        $query = "SELECT id, first_name, last_name, email, is_admin, approved, created_at 
                                 FROM users ORDER BY created_at DESC";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<div class="user-card">';
                                echo '<h3>' . $row['first_name'] . ' ' . $row['last_name'] . '</h3>';
                                echo '<p><strong>Email:</strong> ' . $row['email'] . '</p>';
                                echo '<p><strong>Type:</strong> ' . ($row['is_admin'] ? 'Admin' : 'Student') . '</p>';
                                echo '<p><strong>Status:</strong> ' . ($row['approved'] ? 'Approved' : 'Pending') . '</p>';
                                echo '<p><strong>Joined:</strong> ' . $row['created_at'] . '</p>';
                                if (!$row['is_admin']) {
                                    echo '<button onclick="deleteUser(' . $row['id'] . ')" class="delete-btn">Delete User</button>';
                                }
                                echo '</div>';
                            }
                        }
                    } catch (Exception $e) {
                        echo '<p>Error loading users: ' . $e->getMessage() . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Modal for sending updates -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideMessageModal()">&times;</span>
        <h3>Send Message to Applicant</h3>
        <form id="messageForm">
            <input type="hidden" id="messageUserId" name="user_id">
            <div class="input-group">
                <label for="adminMessage">Message:</label>
                <textarea id="adminMessage" name="message" required placeholder="Enter your message for the applicant..." rows="8" style="width: 100%; min-height: 200px; resize: vertical;"></textarea>
            </div>
            <button type="submit" class="send-btn">Send Message</button>
        </form>
    </div>
</div>

<script>
    // User management functions
    function sendMessage(userId) {
        document.getElementById('messageUserId').value = userId;
        document.getElementById('messageModal').style.display = 'block';
    }

    function hideMessageModal() {
        document.getElementById('messageModal').style.display = 'none';
        document.getElementById('messageForm').reset(); // Clear the form
    }

    // Modal functions - FIXED VERSION
    const messageModal = document.getElementById('messageModal');
    const updateModal = document.getElementById('updateModal'); // If you still have this modal

    // Close modals when clicking the 'x' button
    document.querySelectorAll('.close').forEach(function(closeBtn) {
        closeBtn.onclick = function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
                // Reset forms when closing
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                }
            }
        }
    });

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target == messageModal) {
            hideMessageModal();
        }
        if (updateModal && event.target == updateModal) {
            updateModal.style.display = 'none';
        }
    }

    document.getElementById('messageForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'send_message');
        
        fetch('admin_actions.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Message sent successfully!');
                hideMessageModal();
            } else {
                alert('Error: ' + data.message);
            }
        });
    };

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user? This will also delete their applications.')) {
            fetch('admin_actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=delete_user&user_id=' + userId
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }

    function updateApplication(appId, status) {
        const action = status === 'approved' ? 'approve_application' : 'reject_application';
        const message = status === 'approved' ? 'approve this application?' : 'reject this application?';
        
        if (confirm('Are you sure you want to ' + message)) {
            fetch('admin_actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=' + action + '&application_id=' + appId
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Application ' + status + ' successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
</script>
</body>
</html>