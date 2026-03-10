-- =====================================================================
-- University Lost & Found Hub — MySQL Database Schema
-- =====================================================================
-- HOW TO USE:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Click "New" in sidebar → name it: lost_found_hub → Create
-- 3. Click the "SQL" tab → paste this entire file → click "Go"
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `lost_found_hub`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `lost_found_hub`;

-- ——————————————————————————————————————
-- 1. USERS TABLE
-- ——————————————————————————————————————
CREATE TABLE IF NOT EXISTS `users` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL,
  `student_id`  VARCHAR(50)  NOT NULL UNIQUE,
  `email`       VARCHAR(150) NOT NULL UNIQUE,
  `password`    VARCHAR(255) NOT NULL,
  `role`        ENUM('student','admin') DEFAULT 'student',
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ——————————————————————————————————————
-- 2. ITEMS TABLE (lost & found items)
-- ——————————————————————————————————————
CREATE TABLE IF NOT EXISTS `items` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `title`         VARCHAR(200) NOT NULL,
  `description`   TEXT NOT NULL,
  `category`      VARCHAR(100) DEFAULT 'Other',
  `location_name` VARCHAR(200) NOT NULL,
  `latitude`      DECIMAL(10,8) DEFAULT NULL,
  `longitude`     DECIMAL(11,8) DEFAULT NULL,
  `date`          DATE NOT NULL,
  `image`         VARCHAR(255) DEFAULT NULL,
  `type`          ENUM('lost','found') NOT NULL,
  `status`        ENUM('pending','approved','claimed','returned','rejected') DEFAULT 'pending',
  `user_id`       INT NOT NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ——————————————————————————————————————
-- 3. CLAIMS TABLE
-- ——————————————————————————————————————
CREATE TABLE IF NOT EXISTS `claims` (
  `id`                INT AUTO_INCREMENT PRIMARY KEY,
  `item_id`           INT NOT NULL,
  `claimer_id`        INT NOT NULL,
  `student_name`      VARCHAR(100) NOT NULL,
  `student_id_number` VARCHAR(50) NOT NULL,
  `contact_number`    VARCHAR(30) NOT NULL,
  `proof_description` TEXT NOT NULL,
  `status`            ENUM('pending','approved','rejected','collected') DEFAULT 'pending',
  `qr_code`           VARCHAR(120) UNIQUE,
  `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`)    REFERENCES `items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`claimer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ——————————————————————————————————————
-- 4. NOTIFICATIONS TABLE
-- ——————————————————————————————————————
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT NOT NULL,
  `message`     TEXT NOT NULL,
  `is_read`     TINYINT(1) DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ——————————————————————————————————————
-- 5. INDEXES (performance)
-- ——————————————————————————————————————
CREATE INDEX idx_items_type     ON items(type);
CREATE INDEX idx_items_status   ON items(status);
CREATE INDEX idx_items_category ON items(category);
CREATE INDEX idx_claims_status  ON claims(status);
CREATE INDEX idx_notif_user     ON notifications(user_id, is_read);

-- ——————————————————————————————————————
-- 6. DEFAULT ADMIN USER
--    Email:    admin@university.edu
--    Password: admin123
-- ——————————————————————————————————————
INSERT INTO `users` (`name`, `student_id`, `email`, `password`, `role`) VALUES
('Admin User', 'ADMIN-001', 'admin@university.edu',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ——————————————————————————————————————
-- 7. SAMPLE DATA (optional – remove if not needed)
-- ——————————————————————————————————————
INSERT INTO `users` (`name`, `student_id`, `email`, `password`, `role`) VALUES
('John Doe', 'STU-2024-001', 'john@university.edu',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Jane Smith', 'STU-2024-002', 'jane@university.edu',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

INSERT INTO `items` (`title`, `description`, `category`, `location_name`, `latitude`, `longitude`, `date`, `type`, `status`, `user_id`) VALUES
('Black iPhone 15 Pro', 'Black iPhone 15 Pro with clear case. Lock screen has a dog photo.', 'Electronics', 'Library 2nd Floor', 14.5995, 120.9842, CURDATE(), 'lost', 'approved', 2),
('Blue Adidas Backpack', 'Blue Adidas backpack with textbooks and a water bottle inside.', 'Bags & Wallets', 'Cafeteria Main Building', 14.5998, 120.9845, CURDATE(), 'found', 'approved', 3),
('Gold Necklace', 'Thin gold necklace with small heart pendant.', 'Jewelry', 'Gym Locker Room', 14.5990, 120.9840, CURDATE(), 'lost', 'pending', 2),
('Student ID Card', 'Student ID card for Engineering department.', 'ID Cards & Documents', 'Engineering Building Hallway', 14.6000, 120.9850, CURDATE(), 'found', 'approved', 3);
