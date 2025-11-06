ALTER TABLE messages
ADD COLUMN receiver_type ENUM('user', 'group') NOT NULL DEFAULT 'user'
AFTER receiver_id; 