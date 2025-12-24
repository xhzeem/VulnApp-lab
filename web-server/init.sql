-- Create database and tables
CREATE DATABASE IF NOT EXISTS webapp;
USE webapp;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts table for XSS/IDOR vulnerabilities
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200),
    content TEXT,
    is_private BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Comments table for stored XSS
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample users (passwords are MD5 hashed - another vulnerability!)
INSERT INTO users (username, password, email, role) VALUES
('admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'admin@vulnerable.local', 'admin'),
('john', '098f6bcd4621d373cade4e832627b4f6', 'john@vulnerable.local', 'user'),
('alice', '5f4dcc3b5aa765d61d8327deb882cf99', 'alice@vulnerable.local', 'user'),
('bob', '098f6bcd4621d373cade4e832627b4f6', 'bob@vulnerable.local', 'user');

-- Insert sample posts
INSERT INTO posts (user_id, title, content, is_private) VALUES
(1, 'Welcome to VulnApp', 'This is a vulnerable web application for penetration testing practice.', 0),
(2, 'My Private Notes', 'This contains sensitive information that should not be accessible to others.', 1),
(3, 'Public Announcement', 'Everyone can see this post.', 0),
(1, 'Admin Secret', 'FLAG{admin_idor_vulnerability}', 1);

-- Insert sample comments
INSERT INTO comments (post_id, user_id, comment) VALUES
(1, 2, 'Great application!'),
(1, 3, 'Looking forward to testing this.'),
(3, 1, 'Thank you for your participation.');
