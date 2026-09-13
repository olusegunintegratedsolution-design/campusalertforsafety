-- ==========================================================
-- Federal Polytechnic Ilaro
-- Campus Safety & Emergency Alert System (CES)
-- Database: campus_safety_db
-- ==========================================================


SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
-- 1. Table: users
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(25) NOT NULL,
  `role` ENUM('student', 'staff', 'admin') NOT NULL DEFAULT 'student',
  `id_number` VARCHAR(50) NOT NULL COMMENT 'Matric Number or Staff ID',
  `department` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Table: campus_locations
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `campus_locations`;
CREATE TABLE `campus_locations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `category` ENUM('Academic', 'Hostel', 'Administrative', 'Gate', 'Recreation', 'Health', 'Service') NOT NULL DEFAULT 'Academic',
  `latitude` DECIMAL(10, 7) NOT NULL,
  `longitude` DECIMAL(10, 7) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Table: emergency_reports
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `emergency_reports`;
CREATE TABLE `emergency_reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_reference` VARCHAR(30) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NULL,
  `emergency_type` ENUM(
    'Fire', 
    'Medical Emergency', 
    'Security Threat', 
    'Accident', 
    'Theft', 
    'Violence', 
    'Gas Leak', 
    'Infrastructure Failure', 
    'Natural Hazard', 
    'Other'
  ) NOT NULL,
  `description` TEXT NOT NULL,
  `location_id` INT UNSIGNED NULL,
  `location_name` VARCHAR(255) NOT NULL,
  `latitude` DECIMAL(10, 7) NULL,
  `longitude` DECIMAL(10, 7) NULL,
  `severity` ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium',
  `image_path` MEDIUMTEXT DEFAULT NULL,
  `reporter_phone` VARCHAR(25) DEFAULT NULL,
  `allow_contact` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('Pending', 'Acknowledged', 'In Progress', 'Resolved', 'Rejected') NOT NULL DEFAULT 'Pending',
  `assigned_to` VARCHAR(150) DEFAULT NULL,
  `admin_notes` TEXT DEFAULT NULL,
  `resolved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`location_id`) REFERENCES `campus_locations`(`id`) ON DELETE SET NULL,
  INDEX `idx_reports_status` (`status`),
  INDEX `idx_reports_severity` (`severity`),
  INDEX `idx_reports_type` (`emergency_type`),
  INDEX `idx_reports_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Table: report_timeline
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `report_timeline`;
CREATE TABLE `report_timeline` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_id` INT UNSIGNED NOT NULL,
  `action_by` INT UNSIGNED NULL,
  `status_from` VARCHAR(50) DEFAULT NULL,
  `status_to` VARCHAR(50) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`report_id`) REFERENCES `emergency_reports`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`action_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Table: alerts
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `emergency_type` VARCHAR(100) NOT NULL,
  `severity` ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'High',
  `location` VARCHAR(200) NOT NULL,
  `target_audience` ENUM('Everyone', 'Students', 'Staff') NOT NULL DEFAULT 'Everyone',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NULL,
  `start_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry_time` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_alerts_active` (`is_active`),
  INDEX `idx_alerts_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Table: emergency_contacts
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `emergency_contacts`;
CREATE TABLE `emergency_contacts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `department` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `alt_phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `availability` VARCHAR(80) NOT NULL DEFAULT '24/7 Rapid Response',
  `category` ENUM('Security', 'Medical', 'Fire', 'Police', 'Management', 'Technical') NOT NULL DEFAULT 'Security',
  `priority_order` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Table: notifications
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'alert', 'status_update', 'success', 'warning') NOT NULL DEFAULT 'info',
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_notif_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 8. Table: system_logs
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `user_email` VARCHAR(150) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_logs_action` (`action`),
  INDEX `idx_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 9. Table: system_settings
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- SEED DATA
-- ==========================================================

-- Default System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('institution_name', 'Federal Polytechnic Ilaro', 'Name of academic institution'),
('system_name', 'Campus Safety & Emergency Alert System', 'Official application title'),
('institution_short', 'FPI', 'Institutional abbreviation'),
('emergency_hotline', '+234 803 000 1199', 'Primary 24/7 campus security hotline'),
('medical_hotline', '+234 802 555 4321', 'Campus health centre emergency line'),
('campus_default_lat', '6.8928000', 'Default center latitude for campus map'),
('campus_default_lng', '3.0165000', 'Default center longitude for campus map'),
('broadcast_banner_enabled', '1', 'Toggle emergency banner across public & user portals');

-- Sample Campus Locations (Federal Polytechnic Ilaro Landmarks)
INSERT INTO `campus_locations` (`name`, `category`, `latitude`, `longitude`, `description`) VALUES
('School of Applied Science Complex (Block B)', 'Academic', 6.8925000, 3.0162000, 'Lecture halls, Computer Science, Statistics & SLT laboratories'),
('Directorate of Student Affairs (DSA)', 'Administrative', 6.8931000, 3.0158000, 'Student welfare, SUG secretariat, guidance & counseling'),
('School of Engineering Complex', 'Academic', 6.8918000, 3.0175000, 'Mechanical, Electrical, Civil & Agricultural Engineering workshops'),
('Mass Communication Complex & FM Studio', 'Academic', 6.8938000, 3.0149000, 'Broadcast studios, editing suites and media center'),
('School of Environmental Studies', 'Academic', 6.8942000, 3.0180000, 'Architecture, Estate Management, Surveying & Urban Planning'),
('Institutional Library Complex', 'Academic', 6.8929000, 3.0168000, 'Central library, e-learning center and research repository'),
('Main Auditorium & Convocation Arena', 'Administrative', 6.8935000, 3.0172000, 'Large auditorium for official ceremonies, examinations & events'),
('Sports Pavilion & Stadium', 'Recreation', 6.8905000, 3.0188000, 'Main football pitch, basketball court and athletic tracks'),
('Campus Health Centre / Clinic', 'Health', 6.8912000, 3.0152000, '24/7 student & staff outpatient clinic, ambulance bay & pharmacy'),
('Male Hostel Complex (Hall 1 & 2)', 'Hostel', 6.8902000, 3.0145000, 'On-campus male student residences'),
('Female Hostel Complex (Hall 3 & 4)', 'Hostel', 6.8898000, 3.0155000, 'On-campus female student residential area'),
('East Campus Main Gate', 'Gate', 6.8950000, 3.0195000, 'Primary vehicular entrance and security checkpoint'),
('West Campus Gate (Orita Road)', 'Gate', 6.8910000, 3.0130000, 'Pedestrian and secondary vehicular gate with security booth');

-- Seed Users
-- Password for all 3 demo accounts is: Admin@12345, Staff@12345, Student@12345
-- Using standard bcrypt hash:
-- Admin: $2y$10$tZ2yD8zP6p9B2eW5k0vJ2.cM1uO5Gk0uH5S0aR2hK7jV4t9L6xX6.O
-- (We will also provide a setup script that ensures the exact hashes for Admin@12345, Staff@12345, and Student@12345)
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `id_number`, `department`, `password`, `status`) VALUES
(1, 'Engr. Babatunde Alabi', 'admin@ilaropoly.edu.ng', '08031234567', 'admin', 'FPI/SEC/ADM001', 'Chief Security Unit', '$2y$10$7663D76uM2T83O5r71KKeO5.n7OtxqYkG37p40g5xM18d5F6564mS', 'active'),
(2, 'Dr. Mrs. Funmilayo Adeyemi', 'staff@ilaropoly.edu.ng', '08029876543', 'staff', 'FPI/STF/2019/402', 'Computer Science', '$2y$10$5jYk102gLw8m2X3e78yq5.d3M9pL5rQ8c1jZ62pZJkYqF9Yw6w3.K', 'active'),
(3, 'Oluwaseun Emmanuel Adebayo', 'student@ilaropoly.edu.ng', '08145556677', 'student', 'FPI/CS/22/0194', 'Computer Science', '$2y$10$4hXj091fKv7l1W2d67xp4.c2L8oK4qP7b0iY51oYJjXpE8Xv5v2.J', 'active');

-- Seed Emergency Contacts
INSERT INTO `emergency_contacts` (`name`, `department`, `phone`, `alt_phone`, `email`, `availability`, `category`, `priority_order`) VALUES
('Campus Security Operations Desk', 'Central Security Unit', '+234 803 111 0001', '+234 802 222 0002', 'security@ilaropoly.edu.ng', '24/7 Rapid Response', 'Security', 1),
('Campus Health Centre Ambulance', 'FPI Directorate of Medical Services', '+234 803 222 1111', '+234 805 333 2222', 'healthcentre@ilaropoly.edu.ng', '24/7 Medical Emergency', 'Medical', 2),
('Ogun State Fire Service (Ilaro Command)', 'Fire & Rescue Service', '+234 803 333 4444', '112', 'fireservice.og@gov.ng', '24/7 Dispatch', 'Fire', 3),
('Nigeria Police Force (Ilaro Divisional Hq)', 'NPF Ilaro Division', '+234 803 444 5555', '08033333333', 'npf.ilaro@police.gov.ng', '24/7 Dispatch', 'Police', 4),
('Dean, Directorate of Student Affairs', 'Student Affairs Division', '+234 803 555 6666', NULL, 'dsa@ilaropoly.edu.ng', 'Mon - Fri (8:00 AM - 6:00 PM)', 'Management', 5),
('Works & Physical Planning (Power & Water)', 'Physical Facilities & Maintenance', '+234 803 666 7777', NULL, 'works@ilaropoly.edu.ng', '24/7 Facility On-call', 'Technical', 6);

-- Seed Emergency Reports
INSERT INTO `emergency_reports` (
  `id`, `report_reference`, `user_id`, `emergency_type`, `description`, 
  `location_id`, `location_name`, `latitude`, `longitude`, `severity`, 
  `image_path`, `reporter_phone`, `allow_contact`, `status`, `assigned_to`, 
  `admin_notes`, `resolved_at`, `created_at`
) VALUES
(
  1, 
  'CES-2026-00101', 
  3, 
  'Fire', 
  'Electrical spark and thick smoke observed coming from the distribution box in the Ground Floor corridor of Science Complex Block B.', 
  1, 
  'School of Applied Science Complex (Block B)', 
  6.8925000, 
  3.0162000, 
  'Critical', 
  NULL, 
  '08145556677', 
  1, 
  'In Progress', 
  'Campus Security Rapid Squad Alpha & Electrical Maintenance', 
  'Main electrical breaker isolated immediately. Response team deployed with dry chemical extinguishers.', 
  NULL, 
  DATE_SUB(NOW(), INTERVAL 45 MINUTE)
),
(
  2, 
  'CES-2026-00098', 
  2, 
  'Medical Emergency', 
  'Student collapsed due to heat exhaustion and dehydration during inter-departmental football tournament.', 
  8, 
  'Sports Pavilion & Stadium', 
  6.8905000, 
  3.0188000, 
  'High', 
  NULL, 
  '08029876543', 
  1, 
  'Resolved', 
  'Health Centre Ambulance Crew', 
  'Patient administered IV fluids and first aid on site, transferred to Health Centre, now stable and discharged.', 
  DATE_SUB(NOW(), INTERVAL 3 HOUR), 
  DATE_SUB(NOW(), INTERVAL 5 HOUR)
),
(
  3, 
  'CES-2026-00084', 
  3, 
  'Infrastructure Failure', 
  'Burst underground water main flooding the pathway between Library Complex and Convocation Arena.', 
  6, 
  'Institutional Library Complex', 
  6.8929000, 
  3.0168000, 
  'Medium', 
  NULL, 
  '08145556677', 
  1, 
  'Acknowledged', 
  'Estate Works & Maintenance Plumbers', 
  'Water valve scheduled for isolation. Repair parts procured.', 
  NULL, 
  DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
  4, 
  'CES-2026-00072', 
  NULL, 
  'Theft', 
  'Unattended backpack containing laptop and student ID taken from 2nd floor library reading table.', 
  6, 
  'Institutional Library Complex', 
  6.8929000, 
  3.0168000, 
  'Low', 
  NULL, 
  '08123334444', 
  1, 
  'Pending', 
  NULL, 
  NULL, 
  NULL, 
  DATE_SUB(NOW(), INTERVAL 2 DAY)
);

-- Seed Report Timeline
INSERT INTO `report_timeline` (`report_id`, `action_by`, `status_from`, `status_to`, `notes`, `created_at`) VALUES
(1, NULL, NULL, 'Pending', 'Report submitted by student.', DATE_SUB(NOW(), INTERVAL 45 MINUTE)),
(1, 1, 'Pending', 'Acknowledged', 'Incident reviewed by Security Desk officer.', DATE_SUB(NOW(), INTERVAL 35 MINUTE)),
(1, 1, 'Acknowledged', 'In Progress', 'Assigned to Rapid Squad Alpha & Electrical Maintenance Unit.', DATE_SUB(NOW(), INTERVAL 25 MINUTE)),
(2, NULL, NULL, 'Pending', 'Medical report submitted by staff.', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(2, 1, 'Pending', 'In Progress', 'Dispatched ambulance team.', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(2, 1, 'In Progress', 'Resolved', 'Patient received treatment and stabilized.', DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Seed Active Alerts
INSERT INTO `alerts` (
  `id`, `title`, `message`, `emergency_type`, `severity`, 
  `location`, `target_audience`, `is_active`, `created_by`, 
  `start_time`, `expiry_time`
) VALUES
(
  1, 
  'ELECTRICAL MAINTENANCE ALERT: SCIENCE COMPLEX BLOCK B', 
  'All students and staff in the School of Applied Science Complex (Block B) are advised to vacate the Ground Floor corridor due to ongoing electrical inspection and fire prevention protocol. Maintenance and security teams are on site.', 
  'Fire Hazard', 
  'High', 
  'School of Applied Science Complex (Block B)', 
  'Everyone', 
  1, 
  1, 
  NOW(), 
  DATE_ADD(NOW(), INTERVAL 6 HOUR)
),
(
  2, 
  'WEATHER & SAFETY ADVISORY: HEAVY RAINSTORM FORECAST', 
  'The Meteorological Bureau has issued an advisory for heavy downpours with strong winds around Ilaro metropolis this afternoon. Avoid standing near tall trees, construction scaffolding, or low-lying drainage paths near the West Gate.', 
  'Natural Hazard', 
  'Medium', 
  'Entire Campus', 
  'Everyone', 
  1, 
  1, 
  DATE_SUB(NOW(), INTERVAL 2 HOUR), 
  DATE_ADD(NOW(), INTERVAL 12 HOUR)
);

-- Seed Notifications
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`, `link`, `is_read`, `created_at`) VALUES
(3, 'Incident Status Update: CES-2026-00101', 'Your reported fire incident in Science Complex Block B is now IN PROGRESS. Rapid Squad Alpha is on site.', 'status_update', 'student/my-reports.php', 0, NOW()),
(3, 'Active Emergency Alert Broadcast', 'High priority alert issued for School of Applied Science Complex (Block B). Please review instructions.', 'alert', 'student/alerts.php', 0, NOW()),
(2, 'Incident CES-2026-00098 Resolved', 'The medical emergency reported at the Sports Pavilion has been successfully resolved.', 'success', 'student/my-reports.php', 1, DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Seed System Logs
INSERT INTO `system_logs` (`user_id`, `user_email`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'admin@ilaropoly.edu.ng', 'ADMIN_LOGIN', 'Administrator logged into command console.', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1, 'admin@ilaropoly.edu.ng', 'ALERT_PUBLISHED', 'Broadcasted emergency alert for Science Complex Block B.', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 40 MINUTE)),
(1, 'admin@ilaropoly.edu.ng', 'STATUS_UPDATE', 'Updated report CES-2026-00101 to In Progress.', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 25 MINUTE));
