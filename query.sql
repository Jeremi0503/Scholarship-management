-- Create the database
CREATE DATABASE IF NOT EXISTS scholarship_db;
USE scholarship_db;

-- Users table (main user accounts)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(500),
    school_id_image VARCHAR(500),
    grades_image VARCHAR(500),
    is_admin BOOLEAN DEFAULT FALSE,
    is_super_admin BOOLEAN DEFAULT FALSE,
    approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Scholarship applications table
CREATE TABLE IF NOT EXISTS scholarship_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    course VARCHAR(100) NOT NULL,
    school_id_number VARCHAR(50) NOT NULL,
    school_year VARCHAR(50) NOT NULL,
    semester VARCHAR(50) NOT NULL,
    section VARCHAR(20) NOT NULL,
    school_id_image VARCHAR(500) NOT NULL,
    proof_of_enrollment VARCHAR(500) NOT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Admin messages table
CREATE TABLE IF NOT EXISTS admin_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Application updates table
CREATE TABLE IF NOT EXISTS application_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    admin_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES scholarship_applications(id) ON DELETE CASCADE
);

-- Admin management table (for tracking admin creation)
CREATE TABLE IF NOT EXISTS admin_management (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    admin_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_approved ON users(approved);
CREATE INDEX idx_users_is_admin ON users(is_admin);
CREATE INDEX idx_users_is_super_admin ON users(is_super_admin);
CREATE INDEX idx_scholarship_applications_user_id ON scholarship_applications(user_id);
CREATE INDEX idx_scholarship_applications_status ON scholarship_applications(status);
CREATE INDEX idx_admin_messages_user_id ON admin_messages(user_id);
CREATE INDEX idx_admin_messages_is_read ON admin_messages(is_read);
CREATE INDEX idx_admin_messages_admin_id ON admin_messages(admin_id);

