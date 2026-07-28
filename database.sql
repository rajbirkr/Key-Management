-- SQL Database Schema for Campus Space Manager (ATLAS HUB)
-- Import this SQL file into PHPMyAdmin or MySQL Database

CREATE DATABASE IF NOT EXISTS campus_booking;
USE campus_booking;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Floors Table
CREATE TABLE IF NOT EXISTS floors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    occupancy_rate INT DEFAULT 0,
    total_rooms INT DEFAULT 8,
    occupied INT DEFAULT 0
);

-- 3. Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    category ENUM('classroom', 'laboratory') NOT NULL,
    department VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    user_phone VARCHAR(20) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE
);

-- 4. Key Status Table
-- Tracks the physical key for each room that has an approved booking:
-- whether it's sitting in the cabinet, checked out to someone, or reported lost.
CREATE TABLE IF NOT EXISTS key_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    booking_id INT DEFAULT NULL,
    status ENUM('in_cabinet', 'issued', 'lost') DEFAULT 'in_cabinet',
    held_by VARCHAR(100) DEFAULT NULL,
    held_by_phone VARCHAR(20) DEFAULT NULL,
    issued_at TIMESTAMP NULL DEFAULT NULL,
    returned_at TIMESTAMP NULL DEFAULT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_room_key (floor_id, room_number),
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);

-- Insert Default Floors Data
-- occupied / occupancy_rate are seeded to agree with each other and with the
-- demo bookings below (previously these two columns were seeded with
-- unrelated numbers and the app never kept them in sync).
INSERT INTO floors (id, floor_name, description, occupancy_rate, total_rooms, occupied) VALUES
(1, 'First Floor', 'Main Academic block, containing large assembly lecture halls and freshman computing labs.', 13, 8, 1),
(2, 'Second Floor', 'Advanced research block with software testing facilities, graphics workstations, and project cubicles.', 0, 8, 0),
(3, 'Third Floor', 'Electronics, electrical hardware, robotics assembly workshops, and network communications testbeds.', 0, 8, 0),
(4, 'Fourth Floor', 'Virtual reality testing hubs, cybersecurity sandboxes, and cloud systems simulation environments.', 0, 8, 0),
(5, 'Fifth Floor', 'Post-graduate workspace, administrative council chambers, thesis defense halls, and quiet study zones.', 0, 8, 0);

-- Insert Demo Users
-- Password for user is 'user', password for admin is 'admin'
INSERT INTO users (name, department, phone, password, role) VALUES
('Raj Thakur', 'Computer Science', '9876543210', 'user', 'user'),
('Dr. Sarah Connor', 'Campus Operations', '1234567890', 'admin', 'admin');

-- Insert Demo Bookings
INSERT INTO bookings (floor_id, room_number, category, department, phone_number, user_phone, user_name, status) VALUES
(1, '102', 'classroom', 'Computer Science', '9876543210', '9876543210', 'Raj Thakur', 'approved'),
(1, '105', 'laboratory', 'Information Tech', '9876543210', '9876543210', 'Raj Thakur', 'pending'),
(2, '201', 'classroom', 'Computer Science', '9876543210', '9876543210', 'Raj Thakur', 'rejected');

-- Insert Demo Key Status (matches the one approved booking above)
INSERT INTO key_status (floor_id, room_number, booking_id, status, held_by, held_by_phone, issued_at) VALUES
(1, '102', 1, 'issued', 'Raj Thakur', '9876543210', CURRENT_TIMESTAMP);
