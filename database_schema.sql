-- Hostel Booking System Database Schema

-- Create Database
CREATE DATABASE IF NOT EXISTS hostel_booking_db;
USE hostel_booking_db;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,  
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms Table
CREATE TABLE rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_no VARCHAR(20) UNIQUE NOT NULL,
    type VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    price DECIMAL(8, 2) NOT NULL,
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Occupants Table
CREATE TABLE occupants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    room_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Bookings Table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('confirmed', 'cancelled', 'checked-out') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Create Indexes for Performance
CREATE INDEX idx_rooms_status ON rooms(status);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_room_id ON bookings(room_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_occupants_room_id ON occupants(room_id);

-- Insert Sample Admin Account
INSERT INTO users (name, email, password, role) VALUES (
    'Admin User',
    'admin@hostel.com',
    '$2y$12$R9h/cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jWMUm',
    'admin'
);
-- Password: admin123

-- Insert Sample Customer Account
INSERT INTO users (name, email, password, role) VALUES (
    'John Doe',
    'john@example.com',
    '$2y$12$R9h/cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jWMUm',
    'customer'
);
-- Password: admin123

-- Insert Sample Rooms
INSERT INTO rooms (room_no, type, capacity, price, status) VALUES
('101', 'Single', 1, 30.00, 'available'),
('102', 'Double', 2, 50.00, 'available'),
('103', 'Double', 2, 50.00, 'occupied'),
('104', 'Dormitory', 4, 20.00, 'available'),
('105', 'Suite', 3, 75.00, 'available'),
('201', 'Single', 1, 30.00, 'maintenance'),
('202', 'Double', 2, 50.00, 'available'),
('203', 'Dormitory', 4, 20.00, 'available'),
('204', 'Suite', 3, 75.00, 'occupied');

-- Insert Sample Occupants
INSERT INTO occupants (name, email, room_id) VALUES
('Alice Smith', 'alice@example.com', 3),
('Bob Johnson', 'bob@example.com', 3),
('Charlie Brown', 'charlie@example.com', 9);

-- Insert Sample Booking
INSERT INTO bookings (user_id, room_id, check_in, check_out, total_price, status) VALUES
(2, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 100.00, 'confirmed');