-- Migration: 0001_tracking_history
-- Purpose: Create tracking_history table (was previously created at runtime)
-- Created: 2026-07-14
-- Note: This migration should run before migration 0003

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
    `updated_by` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `tracking_number` (`tracking_number`),
    KEY `shipment_id` (`shipment_id`),
    KEY `event_timestamp` (`event_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;