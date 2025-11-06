-- Admin table: links to users table by user_id
CREATE TABLE IF NOT EXISTS admin (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  admin_level VARCHAR(50) DEFAULT 'super',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Auth table: logs authentication events
CREATE TABLE IF NOT EXISTS auth (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  event_type VARCHAR(50) NOT NULL, -- e.g., 'login', 'logout', 'password_reset'
  event_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45),
  user_agent TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
); 