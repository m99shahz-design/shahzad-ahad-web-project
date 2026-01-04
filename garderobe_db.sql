-- =========================================
-- Garderobe E-Commerce Database Schema
-- Includes: Users, Products, ML-based Recommendations (views)
-- + Separate Admin Login Table
-- =========================================

-- Drop + create database (optional)
DROP DATABASE IF EXISTS garderobe_db;
CREATE DATABASE garderobe_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE garderobe_db;

-- ==========================
-- Users table (customers)
-- ==========================
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==========================
-- NEW: Separate Admin Login Table
-- ==========================
CREATE TABLE admin_accounts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin login (KEEPING EXACTLY AS YOU PROVIDED)
INSERT INTO admin_accounts (username, email, password_hash)
VALUES (
  'superadmin',
  'admin@gmail.com',
  '$2y$10$6k3qhtawc87K8QqvF6WJUOlHqU6ZZrVSu2CkWvQ5sAAXfUZyZI6ba'
  -- password = admin123
);

-- ==========================
-- Products table (with ML-field: views)
-- ==========================
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  old_price DECIMAL(10,2) DEFAULT NULL,
  category VARCHAR(100),
  image VARCHAR(255),
  views INT DEFAULT 0,                    -- ⭐ ML-based Recommendation System field
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==========================
-- Demo Products (editable anytime)
-- ==========================
INSERT INTO products (name, price, old_price, category, image, views) VALUES
('Snapback Cap',     30.00, 45.00, 'Accessories', 'cap-1.jpg',         5),
('Gray Shoes',       50.00, NULL,  'Sneakers',    'shoes-1.jpg',      12),
('Black T-shirt',    50.00, 80.00, 'T-Shirts',    'tshirt-1.jpg',     20),
('Retro Sunglasses', 55.00, 60.00, 'Accessories', 'sunglasses-1.jpg',  8);

-- ==========================
-- NEW: Orders table (Customer Orders)
-- ==========================
CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  customer_name VARCHAR(120) NOT NULL,
  customer_email VARCHAR(150),
  customer_phone VARCHAR(50),
  customer_address VARCHAR(255),

  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,

  status ENUM(
    'pending',
    'paid',
    'processing',
    'shipped',
    'delivered',
    'cancelled'
  ) NOT NULL DEFAULT 'pending',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ==========================
-- NEW: Order Items table (Products inside an Order)
-- ==========================
CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,

  product_name VARCHAR(200) NOT NULL,
  product_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  qty INT NOT NULL DEFAULT 1,
  product_image VARCHAR(255),

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id)
    REFERENCES orders(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id)
    REFERENCES products(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;
