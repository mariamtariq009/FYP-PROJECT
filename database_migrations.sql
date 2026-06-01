-- OPTIONAL: extra GPS columns (not required for live map to work)
USE `vehicles_management_system`;

CREATE TABLE IF NOT EXISTS `duty_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `duty_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `start_location` varchar(255) DEFAULT NULL,
  `end_location` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Skip errors if columns already exist (run one at a time in phpMyAdmin if needed):
-- ALTER TABLE `vehicle_gps_logs` ADD COLUMN `location_text` varchar(255) DEFAULT NULL AFTER `speed`;
-- ALTER TABLE `vehicle_gps_logs` ADD COLUMN `is_stop` tinyint(1) NOT NULL DEFAULT 0 AFTER `location_text`;
