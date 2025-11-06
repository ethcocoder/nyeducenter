-- Seed roles table
INSERT INTO roles (name, description) VALUES 
('admin', 'System administrator with full access'),
('teacher', 'Teacher with access to courses, assignments, and student data'),
('student', 'Student with access to enrolled courses and assignments'),
('parent', 'Parent with access to child progress')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Create test admin user (password: admin123)
INSERT INTO users (role_id, email, password, first_name, last_name, is_active)
VALUES (
    1, 
    'admin@example.com', 
    '$2a$10$H.B9Aok5z2r3OFFfGZVCwu4wDJZq6W3xFHbZnAYnfz5UXZrF3xQfS',  -- hashed 'admin123'
    'Admin', 
    'User', 
    1
)
ON DUPLICATE KEY UPDATE id = id;

-- Create test teacher user (password: teacher123)
INSERT INTO users (role_id, email, password, first_name, last_name, is_active)
VALUES (
    2, 
    'teacher@example.com', 
    '$2a$10$uCrDn2Hl5.PP/2KvElwfKuqsV.0TfbBBFTDOjDOEbWzafqkK/qgyy',  -- hashed 'teacher123' 
    'Teacher', 
    'User', 
    1
)
ON DUPLICATE KEY UPDATE id = id;

-- Create test student user (password: student123)
INSERT INTO users (role_id, email, password, first_name, last_name, grade_level_id, is_active)
VALUES (
    3, 
    'student@example.com', 
    '$2a$10$hWDHZs7UIfSxYZNQIARt1uC.DrS7E9jfqqvKqP4uYSCt6AW0EXu4K',  -- hashed 'student123'
    'Student', 
    'User', 
    1,
    1
)
ON DUPLICATE KEY UPDATE id = id; 