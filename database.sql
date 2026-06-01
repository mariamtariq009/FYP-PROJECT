-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2026 at 01:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `system`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `system`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicles_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `team_members` int(11) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cnic_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `facility` varchar(100) DEFAULT NULL,
  `departure_datetime` datetime DEFAULT NULL,
  `arrival_datetime` datetime DEFAULT NULL,
  `place_from` varchar(100) DEFAULT NULL,
  `place_to` varchar(100) DEFAULT NULL,
  `visiting_place` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `booking_type` enum('official','private','project','student_tour','other') DEFAULT NULL,
  `priority_level` enum('low','medium','high','emergency') DEFAULT 'medium',
  `bus_seats` int(11) DEFAULT NULL,
  `booked_by_admin` tinyint(1) DEFAULT 0,
  `vehicle_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `full_name`, `designation`, `department`, `team_members`, `phone_number`, `email`, `cnic_number`, `address`, `facility`, `departure_datetime`, `arrival_datetime`, `place_from`, `place_to`, `visiting_place`, `purpose`, `booking_type`, `priority_level`, `bus_seats`, `booked_by_admin`, `vehicle_id`, `staff_id`, `approved_by`, `status`, `created_at`) VALUES
(1, 'Eman Abdullah', 'Student', 'Computer Science', 24, '03086688453', 'abdullaheman256@gmail.com', '3310090089909', 'Faisalabad', 'UAF', '2026-05-23 08:00:00', '2026-05-31 18:00:00', 'Faisalabad', 'Murree', 'Murree Hills', 'Study Tour', 'student_tour', 'medium', 24, 1, 2, 3, 1, 'approved', '2026-05-21 10:42:07'),
(2, 'Eman Abdullah', 'professor', 'BSCS', 9, '03007878993', 'abdullaheman256@gmail.com', '1324234324234', 'dvjchfke', 'sciences', '2026-05-22 11:32:00', '2026-05-22 23:32:00', 'fsd', 'lhr', 'lahore', 'sgcijaslh;qw;kh;as', 'official', 'emergency', 24, 0, 1, 3, 1, 'approved', '2026-05-22 06:33:16'),
(3, 'Hafiza Eman', 'professor', 'BSCS', 9, '03086688453', 'abdullaheman256@gmail.com', '44444444444', 'jsiohgdccmocpqej', 'sciences', '2026-05-22 11:58:00', '2026-05-22 23:59:00', 'Faisalabad', 'lhr', 'lahore', 'dg;c gkwoadhoccmsabjxvcq', 'student_tour', 'medium', 34, 1, NULL, NULL, 1, 'rejected', '2026-05-22 06:59:37');

-- --------------------------------------------------------

--
-- Table structure for table `duties`
--

CREATE TABLE `duties` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `route_name` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `duty_date` date DEFAULT NULL,
  `status` enum('Active','Completed','Cancelled') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emergency_cases`
--

CREATE TABLE `emergency_cases` (
  `emergency_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `emergency_type` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` enum('active','resolved') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gps_tracking`
--

CREATE TABLE `gps_tracking` (
  `tracking_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `gps_device_number` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `speed` decimal(10,2) DEFAULT NULL,
  `location_text` varchar(255) DEFAULT NULL,
  `tracked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_book`
--

CREATE TABLE `log_book` (
  `log_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `log_date` date DEFAULT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `departure_time` time DEFAULT NULL,
  `return_time` time DEFAULT NULL,
  `meter_start` decimal(10,2) DEFAULT NULL,
  `meter_end` decimal(10,2) DEFAULT NULL,
  `distance` decimal(10,2) DEFAULT NULL,
  `avg_petrol` decimal(10,2) DEFAULT NULL,
  `petrol_issued` decimal(10,2) DEFAULT NULL,
  `petrol_consumed` decimal(10,2) DEFAULT NULL,
  `remaining_petrol` decimal(10,2) DEFAULT NULL,
  `fuel_cost` decimal(10,2) DEFAULT NULL,
  `total_passengers` int(11) DEFAULT NULL,
  `trip_purpose` text DEFAULT NULL,
  `trip_status` enum('started','completed','cancelled') DEFAULT 'started',
  `ac_status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `request_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `issue_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT NULL,
  `status` enum('pending','approved','in_progress','completed') DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `module` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `message`, `type`, `module`, `reference_id`, `action_url`, `is_read`, `seen_at`, `created_at`) VALUES
(1, 1, 'Vehicle Added', 'New vehicle added successfully', 'success', 'vehicles', 1, NULL, 1, NULL, '2026-05-21 10:42:07'),
(2, 3, 'New Duty Assigned', 'You have been assigned a new vehicle duty', 'info', 'duty', 1, NULL, 0, NULL, '2026-05-21 10:42:07'),
(3, 1, 'Staff Updated', 'Staff Ahmad details updated successfully.', 'info', 'users', NULL, NULL, 1, NULL, '2026-05-21 10:56:53'),
(4, 1, 'New Booking Request', 'Eman Abdullah sent booking request', 'info', 'booking', 2, NULL, 1, NULL, '2026-05-22 06:33:16'),
(5, 3, 'New Trip Assigned', 'You got new booking', 'success', 'booking', 2, NULL, 0, NULL, '2026-05-22 06:33:37'),
(6, 1, 'Booking Added', 'Booking request created successfully', 'success', 'booking', 3, NULL, 1, NULL, '2026-05-22 06:59:37'),
(7, 1, 'Vehicle Updated', 'Vehicle updated successfully.', 'info', 'vehicles', 2, NULL, 0, NULL, '2026-05-22 09:22:22');

-- --------------------------------------------------------

--
-- Table structure for table `pol_records`
--

CREATE TABLE `pol_records` (
  `pol_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `fuel_date` date DEFAULT NULL,
  `details` text DEFAULT NULL,
  `liters` decimal(10,2) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT NULL,
  `fuel_amount` decimal(10,2) DEFAULT NULL,
  `filter_change_type` varchar(100) DEFAULT NULL,
  `filter_change_amount` decimal(10,2) DEFAULT NULL,
  `gst` decimal(10,2) DEFAULT NULL,
  `pst` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repair_history`
--

CREATE TABLE `repair_history` (
  `repair_id` int(11) NOT NULL,
  `maintenance_request_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `repair_date` date DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `workshop_name` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `gst` decimal(10,2) DEFAULT NULL,
  `pst` decimal(10,2) DEFAULT NULL,
  `bill_no` varchar(50) DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `repair_status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `bonus` decimal(10,2) DEFAULT 0.00,
  `status` enum('Pending','Paid') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salaries`
--

INSERT INTO `salaries` (`id`, `user_id`, `month`, `amount`, `bonus`, `status`) VALUES
(1, 2, '4', 10000.00, 1000.00, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `staff_leaves`
--

CREATE TABLE `staff_leaves` (
  `leave_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `leave_type` enum('casual','medical','emergency','annual') DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `designation` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `cnic` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `license_image` varchar(255) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `employment_status` enum('active','inactive','suspended') DEFAULT 'active',
  `availability_status` enum('available','on_duty','leave','sick','absent') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `designation`, `department`, `cnic`, `phone`, `emergency_contact`, `address`, `joining_date`, `profile_image`, `license_number`, `license_image`, `license_expiry`, `employment_status`, `availability_status`, `created_at`) VALUES
(1, 'Admin', 'admin', 'admin@example.com', '$2y$10$O01T1QZOrxb81ZI0YjgYseU4HvORC8HIYCiHOT1Tvq1MZfC8LoPNG', 'admin', 'System Administrator', 'Transport', '33100-0000000-0', '03000000000', '03000000000', 'Main Office', '2026-01-01', NULL, NULL, NULL, NULL, 'active', 'available', '2026-05-21 10:42:07'),
(2, 'Ali', 'Ali123', 'ali@gmail.com', '$2y$10$pO5znG6lIV9NnJQ/6fBQCOFMFmSwvWsplLtPD4gGqqXVIs10VhEV2', 'staff', 'Driver', 'Transport', '33100-5698564-1', '03286778976', '03001112222', 'Faisalabad', '2026-04-08', NULL, '123556688', NULL, '2027-02-28', 'active', 'available', '2026-05-21 10:42:07'),
(3, 'Ahmad', 'Ahmad16', 'abdullaheman256@gmail.com', '$2y$10$ACDBp.RvZRNw/W0RjUdIGequhEK5OSTjm3Ti6DjeCfOMd4Fxnyjke', 'staff', 'Senior Driver', 'Transport', '33101-9874523-1', '03398762354', '03003334444', 'Faisalabad', '2026-04-10', '../uploads/1779361013_bus-driver.webp', '1234566779', '../uploads/1779361013_images.jfif', '2026-11-30', 'active', 'on_duty', '2026-05-21 10:42:07');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `vehicle_name` varchar(100) DEFAULT NULL,
  `make_model` varchar(100) DEFAULT NULL,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `model_year` int(11) DEFAULT NULL,
  `engine_capacity_cc` int(11) DEFAULT NULL,
  `seating_capacity` int(11) DEFAULT NULL,
  `fuel_type` enum('Petrol','Diesel','Hybrid','Electric') DEFAULT NULL,
  `chassis_number` varchar(100) DEFAULT NULL,
  `gps_device_number` varchar(100) DEFAULT NULL,
  `deployment_plan` text DEFAULT NULL,
  `insurance_expiry` date DEFAULT NULL,
  `token_expiry` date DEFAULT NULL,
  `current_status` enum('available','assigned','on_trip','maintenance','inactive','emergency') DEFAULT 'available',
  `last_location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `company_id`, `category_id`, `vehicle_name`, `make_model`, `vehicle_number`, `model_year`, `engine_capacity_cc`, `seating_capacity`, `fuel_type`, `chassis_number`, `gps_device_number`, `deployment_plan`, `insurance_expiry`, `token_expiry`, `current_status`, `last_location`, `latitude`, `longitude`, `created_at`) VALUES
(1, 1, 4, 'Toyota Corolla', 'GLI', 'BFA-123', 2022, 1800, 5, 'Petrol', 'CHS-1001', 'GPS-1001', 'Official Visits', '2027-12-31', '2026-12-31', 'assigned', 'Faisalabad', 31.4504000, 73.1350000, '2026-05-21 10:42:07'),
(2, 8, 1, 'Hino Bus', 'RM2', 'BFA-400', 2020, 4000, 45, 'Diesel', NULL, '861554061253799', 'Student Transport', '2027-11-30', '2026-11-30', 'on_trip', 'Lahore Motorway', 31.5204000, 74.3587000, '2026-05-21 10:42:07');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_assignments`
--

CREATE TABLE `vehicle_assignments` (
  `assignment_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assignment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `duty_status` enum('assigned','on_duty','completed','cancelled') DEFAULT 'assigned',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_assignments`
--

INSERT INTO `vehicle_assignments` (`assignment_id`, `vehicle_id`, `staff_id`, `assigned_by`, `assignment_date`, `start_time`, `end_time`, `duty_status`, `remarks`) VALUES
(1, 2, 3, 1, '2026-05-21 10:42:07', '2026-05-23 08:00:00', '2026-05-31 18:00:00', 'on_duty', 'Murree Study Tour');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_categories`
--

CREATE TABLE `vehicle_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_categories`
--

INSERT INTO `vehicle_categories` (`category_id`, `category_name`) VALUES
(5, 'APV'),
(1, 'Bus'),
(4, 'Car'),
(7, 'Coaster'),
(3, 'Hi Roof'),
(2, 'Mini Bus'),
(9, 'Other'),
(6, 'Truck'),
(8, 'Van');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_companies`
--

CREATE TABLE `vehicle_companies` (
  `company_id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_companies`
--

INSERT INTO `vehicle_companies` (`company_id`, `company_name`) VALUES
(8, '3'),
(3, 'Hino'),
(6, 'Honda'),
(5, 'Hyundai'),
(4, 'Isuzu'),
(7, 'Kia'),
(2, 'Suzuki'),
(1, 'Toyota');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_gps_logs`
--

CREATE TABLE `vehicle_gps_logs` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `speed` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_requests`
--

CREATE TABLE `vehicle_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `duties`
--
ALTER TABLE `duties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `emergency_cases`
--
ALTER TABLE `emergency_cases`
  ADD PRIMARY KEY (`emergency_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `gps_tracking`
--
ALTER TABLE `gps_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `log_book`
--
ALTER TABLE `log_book`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pol_records`
--
ALTER TABLE `pol_records`
  ADD PRIMARY KEY (`pol_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `repair_history`
--
ALTER TABLE `repair_history`
  ADD PRIMARY KEY (`repair_id`),
  ADD KEY `maintenance_request_id` (`maintenance_request_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `staff_leaves`
--
ALTER TABLE `staff_leaves`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `vehicle_number` (`vehicle_number`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_gps_device` (`gps_device_number`),
  ADD KEY `idx_current_status` (`current_status`);

--
-- Indexes for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `vehicle_companies`
--
ALTER TABLE `vehicle_companies`
  ADD PRIMARY KEY (`company_id`),
  ADD UNIQUE KEY `company_name` (`company_name`);

--
-- Indexes for table `vehicle_gps_logs`
--
ALTER TABLE `vehicle_gps_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vehicle_gps_vehicle` (`vehicle_id`),
  ADD KEY `idx_vehicle_gps_created` (`created_at`);

--
-- Indexes for table `vehicle_requests`
--
ALTER TABLE `vehicle_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `duties`
--
ALTER TABLE `duties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emergency_cases`
--
ALTER TABLE `emergency_cases`
  MODIFY `emergency_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gps_tracking`
--
ALTER TABLE `gps_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_book`
--
ALTER TABLE `log_book`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pol_records`
--
ALTER TABLE `pol_records`
  MODIFY `pol_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repair_history`
--
ALTER TABLE `repair_history`
  MODIFY `repair_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_leaves`
--
ALTER TABLE `staff_leaves`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vehicle_companies`
--
ALTER TABLE `vehicle_companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vehicle_gps_logs`
--
ALTER TABLE `vehicle_gps_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_requests`
--
ALTER TABLE `vehicle_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `duties`
--
ALTER TABLE `duties`
  ADD CONSTRAINT `duties_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `duties_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `emergency_cases`
--
ALTER TABLE `emergency_cases`
  ADD CONSTRAINT `emergency_cases_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emergency_cases_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `gps_tracking`
--
ALTER TABLE `gps_tracking`
  ADD CONSTRAINT `gps_tracking_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `log_book`
--
ALTER TABLE `log_book`
  ADD CONSTRAINT `log_book_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `log_book_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `maintenance_requests_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pol_records`
--
ALTER TABLE `pol_records`
  ADD CONSTRAINT `pol_records_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;

--
-- Constraints for table `repair_history`
--
ALTER TABLE `repair_history`
  ADD CONSTRAINT `repair_history_ibfk_1` FOREIGN KEY (`maintenance_request_id`) REFERENCES `maintenance_requests` (`request_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `repair_history_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `repair_history_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `salaries`
--
ALTER TABLE `salaries`
  ADD CONSTRAINT `salaries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_leaves`
--
ALTER TABLE `staff_leaves`
  ADD CONSTRAINT `staff_leaves_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `vehicle_companies` (`company_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehicles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicle_assignments`
--
ALTER TABLE `vehicle_assignments`
  ADD CONSTRAINT `vehicle_assignments_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_assignments_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicle_requests`
--
ALTER TABLE `vehicle_requests`
  ADD CONSTRAINT `vehicle_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_requests_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
