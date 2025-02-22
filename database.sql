CREATE DATABASE church_management;
USE church_management;

CREATE TABLE members (
    member_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    whatsapp_number VARCHAR(15),
    location VARCHAR(200),
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

CREATE TABLE events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(200),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE daily_sermons (
    sermon_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    scheduled_date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT,
    type ENUM('event', 'sermon', 'general') NOT NULL,
    content TEXT NOT NULL,
    channels SET('email', 'sms', 'whatsapp') NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    error_message TEXT,
    FOREIGN KEY (member_id) REFERENCES members(member_id)
);

-- Add new role enum value to users table
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'staff', 'member') NOT NULL DEFAULT 'member';