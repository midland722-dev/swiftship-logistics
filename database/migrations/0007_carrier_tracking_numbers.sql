-- ============================================================
-- Migration 0007: Carrier Tracking Number Integration
-- ------------------------------------------------------------
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. carrier_tracking_number / carrier_name / carrier_integration_id
--      / last_carrier_sync_at to shipments
--   2. carrier_tracking_events table (raw carrier payload audit)
--   3. Unique key on (carrier_tracking_number, carrier_integration_id)
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0007_carrier_tracking_numbers.sql
-- or run via apply_0007.php
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `add_col_if_missing_0007`$$
CREATE PROCEDURE `add_col_if_missing_0007`(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `add_idx_if_missing_0007`$$
CREATE PROCEDURE `add_idx_if_missing_0007`(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `shipments` ADD ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Add carrier linkage columns to shipments
CALL add_col_if_missing_0007('shipments', 'carrier_tracking_number', 'varchar(120) DEFAULT NULL AFTER `tracking_number`');
CALL add_col_if_missing_0007('shipments', 'carrier_name', 'varchar(100) DEFAULT NULL AFTER `carrier_tracking_number`');
CALL add_col_if_missing_0007('shipments', 'carrier_integration_id', 'int(11) DEFAULT NULL AFTER `carrier_name`');
CALL add_col_if_missing_0007('shipments', 'last_carrier_sync_at', 'timestamp NULL DEFAULT NULL AFTER `carrier_integration_id`');

-- 2) Indexes
CALL add_idx_if_missing_0007('uq_carrier_tracking', 'UNIQUE KEY `uq_carrier_tracking` (`carrier_tracking_number`, `carrier_integration_id`)');
CALL add_idx_if_missing_0007('idx_carrier_sync', 'KEY `idx_carrier_sync` (`carrier_integration_id`, `last_carrier_sync_at`)');
CALL add_idx_if_missing_0007('idx_carrier_tn', 'KEY `idx_carrier_tn` (`carrier_tracking_number`)');

-- 3) Raw carrier event store
CREATE TABLE IF NOT EXISTS `carrier_tracking_events` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `integration_id` int(11) NOT NULL,
    `carrier_status` varchar(100) NOT NULL,
    `canonical_status` varchar(50) NOT NULL,
    `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`raw_payload`)),
    `location` varchar(255) DEFAULT NULL,
    `event_timestamp` datetime DEFAULT NULL,
    `processed` tinyint(1) DEFAULT 0,
    `processed_at` timestamp NULL DEFAULT NULL,
    `error_message` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `shipment_id` (`shipment_id`),
    KEY `integration_id` (`integration_id`),
    KEY `processed` (`processed`),
    KEY `event_timestamp` (`event_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cleanup
DROP PROCEDURE IF EXISTS `add_col_if_missing_0007`;
DROP PROCEDURE IF EXISTS `add_idx_if_missing_0007`;

DELIMITER ;
