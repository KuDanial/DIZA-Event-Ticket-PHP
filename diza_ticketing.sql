-- ==========================================
-- DIZA TICKETING SYSTEM DATABASE SCHEMA
-- Target: MySQL / MariaDB (XAMPP phpMyAdmin)
-- ==========================================

DROP DATABASE IF EXISTS diza_ticketing_db;
CREATE DATABASE diza_ticketing_db;
USE diza_ticketing_db;

-- ==========================================
-- 1. USER TABLE (users)
-- ==========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- MD5 hashed passwords
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    role ENUM('admin', 'organizer', 'attendee') NOT NULL,
    status ENUM('active', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- 2. ORGANIZER TABLE (organizer_details)
-- ==========================================
CREATE TABLE organizer_details (
    user_id INT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    business_phone VARCHAR(20),
    business_email VARCHAR(100),
    website_url VARCHAR(255),
    about_description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- 3. ATTENDEE TABLE (attendee_details)
-- ==========================================
CREATE TABLE attendee_details (
    user_id INT PRIMARY KEY,
    student_id VARCHAR(50) DEFAULT NULL,
    preferred_category VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- 4. ADMIN TABLE (admin_details)
-- ==========================================
CREATE TABLE admin_details (
    user_id INT PRIMARY KEY,
    admin_level VARCHAR(50) DEFAULT 'SuperAdmin',
    last_login TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- 5. EVENT TABLE (events)
-- ==========================================
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    venue_name VARCHAR(150) NOT NULL,
    venue_address TEXT NOT NULL,
    capacity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    image_path VARCHAR(255) DEFAULT 'default_event.png',
    status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================
-- 6. EVENT_SLOT TABLE (event_slots)
-- ==========================================
CREATE TABLE event_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    slot_capacity INT NOT NULL,
    remaining_capacity INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- ==========================================
-- 7. BOOKING TABLE (bookings)
-- ==========================================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('card', 'fpx', 'ewallet') NOT NULL,
    status ENUM('confirmed', 'cancelled', 'refunded') DEFAULT 'confirmed',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- ==========================================
-- DUMMY INSERT STATEMENTS (For testing, passwords are "123")
-- ==========================================

-- Insert Users
-- MD5 hash for "123" is "202cb962ac59075b964b07152d234b70"
INSERT INTO users (username, password_hash, first_name, last_name, email, phone_number, gender, address, role, status) VALUES 
('admin', '202cb962ac59075b964b07152d234b70', 'Super', 'Admin', 'admin@dizaet.com', '123456789', 'Male', 'UiTM Machang Admin Suite', 'admin', 'active'),
('org_tech', '202cb962ac59075b964b07152d234b70', 'Tech', 'Organizer', 'contact@techgiants.com', '+603-88889999', 'Female', 'Kuala Lumpur Tech Park', 'organizer', 'active'),
('Danial', '202cb962ac59075b964b07152d234b70', 'Tengku', 'Danial', 'danial@student.uitm.edu.my', '012-3456789', 'Male', 'UiTM Machang Student Residence', 'attendee', 'active');

-- Insert Organizer Profile
INSERT INTO organizer_details (user_id, company_name, business_phone, business_email, website_url, about_description) VALUES 
(2, 'Tech Giants Co.', '+603-88889999', 'contact@techgiants.com', 'https://techgiants.com', 'Leading developer organization in Malaysia.');

-- Insert Attendee Profile
INSERT INTO attendee_details (user_id, student_id, preferred_category) VALUES 
(3, '2026123456', 'Technology');

-- Insert Admin Profile
INSERT INTO admin_details (user_id, admin_level, last_login) VALUES 
(1, 'SuperAdmin', CURRENT_TIMESTAMP);

-- Insert Events
INSERT INTO events (id, organizer_id, title, description, event_date, venue_name, venue_address, capacity, price, status, image_path) VALUES 
(1, 2, 'DIZA Tech Summit 2026', 'Welcome to the annual DIZA Tech Summit exploring next-gen web applications.', '2026-03-05 09:00:00', 'UiTM Machang', 'Grand Hall, UiTM Kelantan', 500, 50.00, 'published', 'event4.png'),
(2, 2, 'Future AI Workshop', 'Hands-on training session on machine learning and local large language models.', '2026-06-12 14:00:00', 'Zoom Online', 'Online Link', 100, 0.00, 'published', 'event1.png');

-- Insert Event Slots (e.g. Morning and Afternoon slots)
INSERT INTO event_slots (event_id, start_time, end_time, slot_capacity, remaining_capacity) VALUES 
(1, '2026-03-05 09:00:00', '2026-03-05 12:00:00', 250, 250),
(1, '2026-03-05 13:00:00', '2026-03-05 17:00:00', 250, 250),
(2, '2026-06-12 14:00:00', '2026-06-12 17:00:00', 100, 100);

-- Insert Bookings
INSERT INTO bookings (user_id, event_id, quantity, total_price, payment_method, status) VALUES 
(3, 1, 2, 100.00, 'fpx', 'confirmed');
