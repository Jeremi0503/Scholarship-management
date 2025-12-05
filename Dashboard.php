<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: LoginPage.php");
    exit();
}

// Check for admin messages
include 'Database.php';
$unread_messages = 0;
$total_messages = 0;
try {
    $database = new Database();
    $db = $database->getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Count unread messages
    $unread_query = "SELECT COUNT(*) as unread_count FROM admin_messages WHERE user_id = :user_id AND is_read = FALSE";
    $stmt = $db->prepare($unread_query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $unread_messages = $result['unread_count'];
    
    // Count total messages
    $total_query = "SELECT COUNT(*) as total_count FROM admin_messages WHERE user_id = :user_id";
    $stmt = $db->prepare($total_query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_messages = $result['total_count'];
} catch (Exception $e) {
    // Silently handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="DashboardStyle.css">
    
</head>
<body>
    <header>
        <img class="logo" src="images/School.jpg" alt="Logo">
        <nav>   
            <a href="Homepage.php">Home</a>
            <a href="#">Services</a>
            <!-- Messages Navigation Button with Badge -->
            <a href="javascript:void(0)" onclick="showMessages()" class="nav-messages-btn">
                Messages
                <?php if ($unread_messages > 0): ?>
                    <span class="message-badge"><?php echo $unread_messages; ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php" class="nav-profile-btn">Profile</a>
            <a href="logout.php" class="nav-logout-btn">Logout</a>
        </nav>
    </header>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo $_SESSION['user_name']; ?>!</h1>
            <p>This is your scholarship application dashboard.</p>
            
            <!-- Message Alert Banner -->
            <?php if ($unread_messages > 0): ?>
            <div class="message-alert" style="margin-top: 20px;">
                <h3>You have <?php echo $unread_messages; ?> unread message(s) from admin</h3>
                <button onclick="showMessages()" class="view-messages-btn">View Messages</button>
            </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-content">
            <div class="info-card">
                <h2>Scholarship Information</h2>
                <p>Here you can apply for scholarships and check your application status.</p>
                <div class="scholarship-details">
                    <h3>Available Scholarships</h3>
                    <ul>
                        <li>BATCHELOR IN TECHNICAL VOCATIONAL TEACHER EDUCATION</li>
                        <li>BATCHELOR OF SCIENCE IN ENTREPRENEURSHIP</li>
                        <li>BATCHELOR OF SCIENCE IN INFORMATION SYSTEM</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="action-section">
                <h2>Quick Actions</h2>
                <p>Apply for a scholarship or check your application status.</p>
                <button class="apply-now-btn" onclick="showApplicationForm()">Apply Now</button>
                
                <!-- Message History Preview -->
                <?php if ($total_messages > 0): ?>
                <div class="message-history">
                    <h4>Recent Messages (<?php echo $total_messages; ?> total)</h4>
                    <?php
                    try {
                        $recent_query = "SELECT am.message, am.created_at, am.is_read, u.first_name, u.last_name 
                                      FROM admin_messages am 
                                      JOIN users u ON am.admin_id = u.id 
                                      WHERE am.user_id = :user_id 
                                      ORDER BY am.created_at DESC 
                                      LIMIT 3";
                        $stmt = $db->prepare($recent_query);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        $recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($recent_messages) > 0) {
                            foreach ($recent_messages as $message) {
                                $message_class = $message['is_read'] ? 'read' : 'unread';
                                $status_text = $message['is_read'] ? 'Read' : 'New';
                                echo '<div class="message-item ' . $message_class . '">';
                                echo '<div class="message-header">';
                                echo '<span class="message-sender">Admin: ' . $message['first_name'] . ' ' . $message['last_name'] . '</span>';
                                echo '<div>';
                                echo '<span class="message-date">' . $message['created_at'] . '</span>';
                                echo '<span class="message-status ' . $message_class . '">' . $status_text . '</span>';
                                echo '</div>';
                                echo '</div>';
                                echo '<div class="message-content">' . htmlspecialchars(substr($message['message'], 0, 100)) . '...</div>';
                                echo '</div>';
                            }
                            if ($total_messages > 3) {
                                echo '<p style="text-align: center; margin-top: 10px;"><a href="javascript:void(0)" onclick="showMessages()" style="color: var(--primary-color);">View all ' . $total_messages . ' messages</a></p>';
                            }
                        }
                    } catch (Exception $e) {
                        echo '<p>Error loading recent messages.</p>';
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Application Form Section -->
            <div class="application-section" id="applicationForm" style="display: none;">
                <h3>Scholarship Application</h3>
                <form method="POST" action="apply_scholarship.php" enctype="multipart/form-data">
                    <!-- ... (keep your existing form fields exactly as they are) ... -->
                    <div class="form-row">
                        <div class="input-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        <div class="input-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name">
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="contact_number">Contact Number *</label>
                        <input type="text" id="contact_number" name="contact_number" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="address">Address *</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="course">Course *</label>
                        <select id="course" name="course" required>
                            <option value="">Select Course</option>
                            <option value="BTVTED">BATCHELOR IN TECHNICAL VOCATIONAL TEACHER EDUCATION</option>
                            <option value="BSE">BATCHELOR OF SCIENCE IN ENTREPRENEURSHIP</option>
                            <option value="BSIS">BATCHELOR OF SCIENCE IN INFORMATION SYSTEM</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label for="school_id_number">School ID Number *</label>
                            <input type="text" id="school_id_number" name="school_id_number" required>
                        </div>
                        <div class="input-group">
                            <label for="school_year">Year Level *</label>
                            <select id="school_year" name="school_year" required>
                                <option value="">Select Year Level</option>
                                <option value="First Year">First Year</option>
                                <option value="Second Year">Second Year</option>
                                <option value="Third Year">Third Year</option>
                                <option value="Fourth Year">Fourth Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label for="semester">Semester *</label>
                            <select id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="First Semester">First Semester</option>
                                <option value="Second Semester">Second Semester</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="section">Section *</label>
                            <input type="text" id="section" name="section" required placeholder="e.g., A, B, C">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label for="school_id">School ID *</label>
                            <input type="file" id="school_id" name="school_id" accept="image/*,.pdf" required>
                            <small>Upload your valid School ID (Image or PDF)</small>
                        </div>
                        <div class="input-group">
                            <label for="proof_of_enrollment">Proof of Enrollment *</label>
                            <input type="file" id="proof_of_enrollment" name="proof_of_enrollment" accept="image/*,.pdf" required>
                            <small>Upload your Proof of Enrollment (Image or PDF)</small>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="apply-btn">Submit Application</button>
                        <button type="button" class="cancel-btn" onclick="hideApplicationForm()">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Application Status Section -->
            <div class="application-status">
                <h2>Application Status</h2>
                <div class="status-card">
                    <?php
                    // Check if user has an application
                    try {
                        $database = new Database();
                        $db = $database->getConnection();
                        $user_id = $_SESSION['user_id'];
                        $check_query = "SELECT status FROM scholarship_applications WHERE user_id = :user_id";
                        $stmt = $db->prepare($check_query);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $status = $row['status'];
                            $status_class = 'status-' . $status;
                            echo "<h3>Your application is: <span class='$status_class'>$status</span></h3>";
                            echo "<p>We will notify you once there's an update.</p>";
                        } else {
                            echo "<h3>You haven't submitted an application yet.</h3>";
                            echo "<p>Click the 'Apply Now' button to start your application.</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p>Error checking application status.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Modal -->
    <div id="messagesModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideMessages()">&times;</span>
            <h3>Admin Messages</h3>
            <div id="messagesList"></div>
        </div>
    </div>

    <script>
        function showApplicationForm() {
            document.getElementById('applicationForm').style.display = 'block';
        }

        function hideApplicationForm() {
            document.getElementById('applicationForm').style.display = 'none';
        }

        function showMessages() {
            fetch('get_messages.php')
                .then(response => response.json())
                .then(messages => {
                    const messagesList = document.getElementById('messagesList');
                    messagesList.innerHTML = '';
                    
                    if (messages.length === 0) {
                        messagesList.innerHTML = '<p>No messages found.</p>';
                    } else {
                        messages.forEach(message => {
                            const messageDiv = document.createElement('div');
                            const messageClass = message.is_read ? 'message-item read' : 'message-item unread';
                            const statusText = message.is_read ? 'Read' : 'New';
                            
                            messageDiv.className = messageClass;
                            messageDiv.innerHTML = `
                                <div class="message-header">
                                    <strong>From: ${message.first_name} ${message.last_name}</strong>
                                    <div>
                                        <span class="message-date">${message.created_at}</span>
                                        <span class="message-status ${message.is_read ? 'read' : 'unread'}">${statusText}</span>
                                    </div>
                                </div>
                                <div class="message-content">${message.message}</div>
                            `;
                            messagesList.appendChild(messageDiv);
                        });
                    }
                    
                    document.getElementById('messagesModal').style.display = 'block';
                    
                    // Mark messages as read but don't delete them
                    fetch('mark_messages_read.php', { method: 'POST' })
                        .then(() => {
                            // Update the badge count without reloading the page
                            updateMessageBadge(0);
                        });
                });
        }

        function hideMessages() {
            document.getElementById('messagesModal').style.display = 'none';
            // Don't reload the page - messages are now persistent
        }

        function updateMessageBadge(count) {
            const badge = document.querySelector('.message-badge');
            const messagesBtn = document.querySelector('.nav-messages-btn');
            
            if (count > 0) {
                if (badge) {
                    badge.textContent = count;
                } else {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'message-badge';
                    newBadge.textContent = count;
                    messagesBtn.appendChild(newBadge);
                }
            } else {
                if (badge) {
                    badge.remove();
                }
            }
        }

        // Display messages from session
        <?php
        if (isset($_SESSION['application_success'])) {
            echo "alert('".$_SESSION['application_success']."');";
            unset($_SESSION['application_success']);
        }
        if (isset($_SESSION['application_error'])) {
            echo "alert('".$_SESSION['application_error']."');";
            unset($_SESSION['application_error']);
        }
        ?>
    </script>
</body>
</html>