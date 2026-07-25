-- ============================================================
-- Migration 0002: Courier Management System
-- ------------------------------------------------------------
-- This migration is SAFE TO RE-RUN.
--   1. Expands the shipments.status enum (idempotent MODIFY).
--   2. Creates the drivers / vehicles tables (IF NOT EXISTS).
--
-- New columns on `shipments` (sender/receiver/parcel/transit fields)
-- are added via `database/dbs.sql` and applied non-destructively by
-- the existing admin/sync_schema.php tool (it only ever ADDs missing
-- columns, never drops). After applying this file, run:
--     visit admin/sync_schema.php   (or: php admin/sync_schema.php)
-- to create any new shipments columns that do not yet exist.
-- ============================================================

-- 1) Expand the shipments.status enum with the courier lifecycle.
--    (Old values are preserved so existing rows keep displaying.)
ALTER TABLE `shipments`
  MODIFY COLUMN `status` enum(
    'created','pending_pickup','picked_up','received_origin','sorted',
    'in_transit','at_hub','out_for_delivery','delivered','delivery_failed',
    'customer_unavailable','on_hold','returned','cancelled','lost','damaged',
    'pending','processing','at_warehouse','customs_inspection','customs_clearance',
    'customs_delayed','customs_seized','held','security_check','shipment_stopped'
  ) NOT NULL DEFAULT 'created';

-- 2) Drivers (couriers)
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `phone` varchar(90) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `license_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Vehicles (schema matches the existing `vehicles` table in dbs.sql)
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_number` varchar(50) NOT NULL,
  `type` enum('van','truck','car','motorcycle','other') DEFAULT 'van',
  `make` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `capacity_kg` decimal(8,3) DEFAULT 0.000,
  `status` enum('active','maintenance','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `registration_number` (`registration_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Branches / hubs are sourced from the existing `locations` table.
--    No new table required.
