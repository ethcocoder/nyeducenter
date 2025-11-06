-- Innovation Trading Center Platform Database Schema
-- For Ethiopia's Innovation Ecosystem

-- CREATE DATABASE IF NOT EXISTS innovation_trading_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE innovation_trading_center;

-- Default admin login: admin@innovationcenter.et / admin123

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('innovator', 'sponsor', 'admin') NOT NULL DEFAULT 'innovator',
    organization VARCHAR(255),
    bio TEXT,
    phone VARCHAR(20),
    location VARCHAR(255),
    profile_image VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Innovation categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Innovations table
CREATE TABLE innovations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category_id INT NOT NULL,
    funding_needs DECIMAL(15,2),
    funding_currency ENUM('ETB', 'USD', 'EUR') DEFAULT 'ETB',
    location VARCHAR(255),
    stage ENUM('idea', 'prototype', 'pilot', 'market_ready', 'scaling') DEFAULT 'idea',
    status ENUM('draft', 'published', 'funded', 'completed') DEFAULT 'draft',
    featured_image VARCHAR(255),
    video_url VARCHAR(255),
    website_url VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    views_count INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Innovation media (multiple images/documents)
CREATE TABLE innovation_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    innovation_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('image', 'document', 'video') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (innovation_id) REFERENCES innovations(id) ON DELETE CASCADE
);

-- Messages between users
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    innovation_id INT,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (innovation_id) REFERENCES innovations(id) ON DELETE SET NULL
);

-- Favorites/Bookmarks
CREATE TABLE favorites (
    user_id INT NOT NULL,
    innovation_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, innovation_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (innovation_id) REFERENCES innovations(id) ON DELETE CASCADE
);

-- Innovation views tracking
CREATE TABLE innovation_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    innovation_id INT NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (innovation_id) REFERENCES innovations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Admin settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories (name, slug, description, icon) VALUES
('Agriculture Technology', 'agriculture', 'Innovations in farming, irrigation, and agricultural processes', '🌾'),
('Health Technology', 'health', 'Medical devices, health apps, and healthcare solutions', '🏥'),
('Education Technology', 'education', 'Learning platforms, educational tools, and training solutions', '📚'),
('Energy & Environment', 'energy', 'Renewable energy, environmental protection, and sustainability', '⚡'),
('Financial Technology', 'finance', 'Digital banking, payment solutions, and financial services', '💰'),
('Transportation', 'transport', 'Mobility solutions, logistics, and transportation technology', '🚗'),
('Manufacturing', 'manufacturing', 'Industrial automation, production optimization, and manufacturing tech', '🏭'),
('Tourism & Hospitality', 'tourism', 'Travel technology, hospitality solutions, and tourism services', '🏨'),
('Other', 'other', 'Other innovative solutions and technologies', '💡');

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password_hash, role, organization, bio) VALUES
('Admin User', 'admin@innovationcenter.et', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Innovation Trading Center', 'System Administrator');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Innovation Trading Center', 'Website name'),
('site_description', 'Connecting Ethiopian innovators with investors and sponsors', 'Website description'),
('contact_email', 'info@innovationcenter.et', 'Contact email address'),
('max_file_size', '5242880', 'Maximum file upload size in bytes'),
('items_per_page', '12', 'Number of items to show per page'),
('enable_registration', '1', 'Enable user registration'),
('require_email_verification', '0', 'Require email verification for new users');

-- Create indexes for better performance
CREATE INDEX idx_innovations_user_id ON innovations(user_id);
CREATE INDEX idx_innovations_category_id ON innovations(category_id);
CREATE INDEX idx_innovations_status ON innovations(status);
CREATE INDEX idx_innovations_created_at ON innovations(created_at);
CREATE INDEX idx_messages_sender_id ON messages(sender_id);
CREATE INDEX idx_messages_receiver_id ON messages(receiver_id);
CREATE INDEX idx_innovation_views_innovation_id ON innovation_views(innovation_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role); 