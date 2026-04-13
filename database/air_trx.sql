CREATE DATABASE IF NOT EXISTS air_trx_db;
USE air_trx_db;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS flights;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passport_no VARCHAR(50) NOT NULL,
    birthday DATE NULL,
    contact_no VARCHAR(30) DEFAULT '',
    address VARCHAR(255) DEFAULT '',
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_code VARCHAR(20) NOT NULL,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL DEFAULT '08:00:00',
    economy_fare DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    business_fare DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_reference VARCHAR(30) NOT NULL UNIQUE,
    seat_no VARCHAR(10) NOT NULL,
    seat_class VARCHAR(30) NOT NULL DEFAULT 'Economy',
    booking_status VARCHAR(50) NOT NULL DEFAULT 'Confirmed',
    fare_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'GCash',
    payment_status VARCHAR(50) NOT NULL DEFAULT 'Paid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_flight FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE
);
INSERT INTO admins (full_name, username, password_hash) VALUES ('Air-TRX Administrator', 'ferreradmin', '$2y$10$a9eTd0BxKL0cpQfOGMnHXu5DotMWi5P.yNs19T.WtUasyl5IcOAg2');
INSERT INTO users (full_name, email, passport_no, birthday, contact_no, address, password_hash) VALUES
('Demo Passenger', 'demo@airtrx.com', 'P8801221', '2003-06-21', '09171234567', 'Imus, Cavite', '$2y$10$9Gw5IZrBLzJWmZ19u1jfjuiC6h6S2tqgiw1gOBRb1v/L10N3g6N2W'),
('Maria Santos', 'maria@airtrx.com', 'P5542018', '1999-11-12', '09182345678', 'Cebu City', '$2y$10$9Gw5IZrBLzJWmZ19u1jfjuiC6h6S2tqgiw1gOBRb1v/L10N3g6N2W');
INSERT INTO flights (flight_code, origin, destination, departure_date, departure_time, economy_fare, business_fare) VALUES
('ATX101', 'Manila', 'Cebu', '2026-04-15', '08:15:00', 3680.00, 6900.00),
('ATX202', 'Manila', 'Davao', '2026-04-18', '13:30:00', 4590.00, 8290.00),
('ATX305', 'Clark', 'Iloilo', '2026-04-20', '10:45:00', 3125.00, 5990.00),
('ATX411', 'Cebu', 'Siargao', '2026-04-22', '07:25:00', 4290.00, 7990.00),
('ATX550', 'Manila', 'Boracay', '2026-04-24', '16:00:00', 3950.00, 7450.00);
INSERT INTO bookings (user_id, flight_id, booking_reference, seat_no, seat_class, booking_status, fare_amount, payment_method, payment_status) VALUES
(1, 1, 'ATX-A11BC2', '3A', 'Economy', 'Confirmed', 3680.00, 'GCash', 'Paid'),
(2, 2, 'ATX-B77D91', '1C', 'Business', 'Confirmed', 8290.00, 'Card', 'Paid');
