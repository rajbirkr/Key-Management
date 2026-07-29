CREATE DATABASE IF NOT EXISTS keymanagment;
USE keymanagment;

-- ===========================
-- FLOORS TABLE
-- ===========================

CREATE TABLE floors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    total_rooms INT NOT NULL DEFAULT 0,
    occupied INT NOT NULL DEFAULT 0,
    occupancy_rate DECIMAL(5,2)
    GENERATED ALWAYS AS (
        CASE
            WHEN total_rooms > 0
            THEN occupied / total_rooms * 100
            ELSE 0
        END
    ) STORED
);

INSERT INTO floors (floor_name, description, total_rooms, occupied) VALUES
('First Floor','Main Academic block with lecture halls and computing labs.',9,0),
('Second Floor','Research block with software testing facilities.',9,0),
('Third Floor','Electronics, robotics and networking labs.',9,0),
('Fourth Floor','Cybersecurity and cloud simulation labs.',9,0),
('Fifth Floor','Postgraduate workspace and administration.',9,0);

-- ===========================
-- USERS TABLE
-- ===========================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (name, department, phone, password, role) VALUES
('Raj Thakur','MCA','9876543210','user','user'),
('Rajib Santra','MCA','9876543211','user','user'),
('Admin User','Campus Operations','1234567890','admin','admin');

-- ===========================
-- BOOKINGS TABLE
-- ===========================

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    category ENUM('classroom','laboratory') NOT NULL,
    department VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    user_phone VARCHAR(20) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (floor_id)
        REFERENCES floors(id)
        ON DELETE CASCADE
);

INSERT INTO bookings
(floor_id, room_number, category, department, phone_number, user_phone, user_name, status)
VALUES
(1,'102','classroom','MCA','9876543210','9876543210','Raj Thakur','approved'),
(1,'105','laboratory','MCA','9876543210','9876543211','Rajib Santra','pending'),
(2,'201','classroom','MCA','9876543210','9876543210','Raj Thakur','rejected');

-- ===========================
-- KEY STATUS TABLE
-- ===========================

CREATE TABLE key_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    floor_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    booking_id INT DEFAULT NULL,
    status ENUM('in_cabinet','issued','lost') DEFAULT 'in_cabinet',
    held_by VARCHAR(100),
    held_by_phone VARCHAR(20),
    issued_at TIMESTAMP NULL,
    returned_at TIMESTAMP NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (floor_id, room_number),

    FOREIGN KEY (floor_id)
        REFERENCES floors(id)
        ON DELETE CASCADE,

    FOREIGN KEY (booking_id)
        REFERENCES bookings(id)
        ON DELETE SET NULL
);

INSERT INTO key_status
(floor_id, room_number, booking_id, status, held_by, held_by_phone, issued_at)
VALUES
(1,'102',1,'issued','Raj Thakur','9876543210',CURRENT_TIMESTAMP);