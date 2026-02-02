CREATE DATABASE IF NOT EXISTS np03cy4a240057;
USE np03cy4a240057;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255) NOT NULL,
    status ENUM('pending','completed') NOT NULL DEFAULT 'pending',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$wHq3U0R8xQ4D9pKpXJzP4OQm9zjJ8GZp7k2Y3d5fC7nE0T6rVbA3G', 'admin'),
('magar', '$2y$10$8B1Xy0l6Jw7x7nZ1m5Y2eYqQ7j1Z2PzQv6GJ9n6X4N8s2AqP7y', 'user');



INSERT INTO tasks (task_name, status, user_id) VALUES
('Complete database assignment', 'pending', 2),
('Deploy project on college server', 'completed', 2),
('Review admin dashboard', 'pending', 1);
