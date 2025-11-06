-- Clear tables and reset auto-increment
TRUNCATE TABLE admin;
TRUNCATE TABLE instructor;
TRUNCATE TABLE student;

-- Seed Users for PHP Online Learning System
-- Password for all users is 'password123' (hashed with password_hash)

-- Admin Users
INSERT INTO admin (full_name, email, username, password) VALUES
('John Smith', 'admin1@example.com', 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Sarah Johnson', 'admin2@example.com', 'admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Instructor Users
INSERT INTO instructor (username, password, first_name, last_name, email, date_of_birth, date_of_joined, status, profile_img) VALUES
('instructor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael', 'Brown', 'instructor1@example.com', '1985-03-15', '2024-01-01', 'Active', 'default.jpg'),
('instructor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emily', 'Davis', 'instructor2@example.com', '1990-07-22', '2024-01-01', 'Active', 'default.jpg'),
('instructor3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David', 'Wilson', 'instructor3@example.com', '1982-11-05', '2024-01-01', 'Active', 'default.jpg');

-- Student Users
INSERT INTO student (username, password, first_name, last_name, email, date_of_birth, date_of_joined, status, profile_img) VALUES
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex', 'Thompson', 'student1@example.com', '2001-04-10', '2024-01-01', 'Active', 'default.jpg'),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jessica', 'Martinez', 'student2@example.com', '2002-09-18', '2024-01-01', 'Active', 'default.jpg'),
('student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ryan', 'Anderson', 'student3@example.com', '1999-12-01', '2024-01-01', 'Active', 'default.jpg'),
('student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophia', 'Lee', 'student4@example.com', '2000-06-25', '2024-01-01', 'Active', 'default.jpg'),
('student5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Daniel', 'Garcia', 'student5@example.com', '1998-02-14', '2024-01-01', 'Active', 'default.jpg'); 