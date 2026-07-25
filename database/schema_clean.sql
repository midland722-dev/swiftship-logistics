-- Minimal clean schema for American Shipping & Logistics
-- Run this instead of the full dbs.sql to avoid duplicate table errors

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cname` varchar(255) NOT NULL,
  `bemail` varchar(255) NOT NULL,
  `caddress` text NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','customer') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role_active` (`role`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `support_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calculator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `normal` decimal(10,4) DEFAULT 0.0700,
  `express` decimal(10,4) DEFAULT 0.0900,
  `currency` varchar(10) DEFAULT 'USD',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier` (
  `cid` int(6) NOT NULL AUTO_INCREMENT,
  `cons_no` varchar(20) NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `phone` varchar(90) NOT NULL,
  `s_add` varchar(300) NOT NULL,
  `cc` varchar(120) NOT NULL,
  `rev_name` varchar(100) NOT NULL,
  `r_phone` varchar(90) NOT NULL,
  `r_add` varchar(300) NOT NULL,
  `cc_r` varchar(120) NOT NULL,
  `email` varchar(255) NOT NULL,
  `type` varchar(40) NOT NULL,
  `weight` double NOT NULL,
  `variable` varchar(255) NOT NULL,
  `shipping_subtotal` varchar(255) NOT NULL,
  `invice_no` varchar(150) NOT NULL,
  `qty` int(10) NOT NULL,
  `book_mode` varchar(20) NOT NULL,
  `declarate` varchar(120) NOT NULL,
  `freight` varchar(150) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `pick_date` varchar(250) NOT NULL,
  `schedule` varchar(250) NOT NULL,
  `pick_time` varchar(250) NOT NULL,
  `status` varchar(20) NOT NULL,
  `comments` varchar(250) NOT NULL,
  `book_date` date NOT NULL,
  `status_delivered` varchar(100) NOT NULL DEFAULT '0',
  `officename` varchar(255) NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  `pimage` varchar(255) DEFAULT NULL,
  `senderimg` varchar(255) DEFAULT NULL,
  `reciverimage` varchar(255) DEFAULT NULL,
  `percent` varchar(150) NOT NULL DEFAULT '80',
  PRIMARY KEY (`cid`),
  KEY `cons_no` (`cons_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_online` (
  `cid` int(10) NOT NULL AUTO_INCREMENT,
  `deliveryboy` varchar(100) NOT NULL,
  `receivedby` varchar(100) NOT NULL,
  `drs` varchar(100) NOT NULL,
  `cons_no` varchar(20) NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `s_phone` varchar(12) NOT NULL,
  `s_add` varchar(200) NOT NULL,
  `fromcity` varchar(200) NOT NULL,
  `rev_name` varchar(100) NOT NULL,
  `r_phone` varchar(12) NOT NULL,
  `r_add` varchar(200) NOT NULL,
  `tocity` varchar(200) NOT NULL,
  `type` varchar(40) NOT NULL,
  `note` varchar(255) NOT NULL,
  `weight` varchar(100) NOT NULL,
  `book_mode` varchar(20) NOT NULL,
  `freight` double NOT NULL,
  `Qnty` varchar(255) NOT NULL,
  `variable` varchar(255) NOT NULL,
  `shipping_subtotal` varchar(255) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `deliverydate` varchar(100) NOT NULL,
  `time` varchar(10) NOT NULL,
  `status` varchar(20) NOT NULL,
  `payment` varchar(255) NOT NULL,
  `paymode` varchar(255) NOT NULL,
  `comments` varchar(250) NOT NULL,
  `office` varchar(100) NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cid`),
  KEY `idx_cons_no` (`cons_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_paid` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL,
  `cons_no` varchar(20) NOT NULL,
  `book_mode` varchar(30) NOT NULL,
  `on_delivery` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cid` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_track` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL,
  `cons_no` varchar(20) NOT NULL,
  `status` varchar(50) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cons_no` (`cons_no`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tracking_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_id` int(11) NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `customs_procedure` varchar(255) DEFAULT NULL,
  `event_notes` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `event_timestamp` datetime NOT NULL,
  `transit_location` varchar(255) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tracking_history_number_ts` (`tracking_number`,`event_timestamp`),
  KEY `idx_tracking_history_shipment_ts` (`shipment_id`,`event_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(100) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('pending','processing','picked_up','at_warehouse','in_transit','at_hub','customs_inspection','customs_clearance','customs_delayed','customs_seized','held','out_for_delivery','delivered','returned','cancelled') DEFAULT 'pending',
  `service_type` varchar(50) DEFAULT 'standard',
  `priority` enum('low','standard','high','express') DEFAULT 'standard',
  `origin_country` char(2) NOT NULL,
  `origin_city` varchar(100) NOT NULL,
  `destination_country` char(2) NOT NULL,
  `destination_city` varchar(100) NOT NULL,
  `total_weight` decimal(10,2) DEFAULT NULL,
  `total_volume` decimal(10,2) DEFAULT NULL,
  `declared_value` decimal(12,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `pieces` int(11) DEFAULT 1,
  `is_fragile` tinyint(1) DEFAULT 0,
  `is_insured` tinyint(1) DEFAULT 0,
  `insurance_amount` decimal(12,2) DEFAULT NULL,
  `payment_status` enum('pending','paid','partial','refunded','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `delivered_by` int(11) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_number` (`tracking_number`),
  KEY `idx_shipments_tracking_status` (`tracking_number`,`status`),
  KEY `idx_shipments_status_customer` (`status`,`customer_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contact_messages_status` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `calculator` (`id`, `normal`, `express`, `currency`) VALUES (1, 0.0700, 0.0900, 'USD');
