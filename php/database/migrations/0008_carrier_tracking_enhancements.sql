-- ============================================================
-- Migration 0008: Carrier Tracking Enhancements + Deduplication
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. carrier_status + carrier_status_updated_at on shipments
--   2. dedup_hash unique key on carrier_tracking_events
--   3. (Optional) carrier_status_mappings master mapping table
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_col_if_missing_0008$$
CREATE PROCEDURE add_col_if_missing_0008(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
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

DROP PROCEDURE IF EXISTS add_idx_if_missing_0008$$
CREATE PROCEDURE add_idx_if_missing_0008(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'carrier_tracking_events' AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `carrier_tracking_events` ADD ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Carrier status cache on shipments (denormalized for fast filtering)
CALL add_col_if_missing_0008('shipments', 'carrier_status', 'varchar(100) DEFAULT NULL AFTER `carrier_integration_id`');
CALL add_col_if_missing_0008('shipments', 'carrier_status_updated_at', 'timestamp NULL DEFAULT NULL AFTER `carrier_status`');
CALL add_col_if_missing_0008('shipments', 'last_carrier_error', 'text DEFAULT NULL AFTER `last_carrier_sync_at`');

-- 2) Indexes on shipments for carrier queries
DELIMITER $$
DROP PROCEDURE IF EXISTS add_idx_shipments_0008$$
CREATE PROCEDURE add_idx_shipments_0008()
BEGIN
    DECLARE idx_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO idx_exists FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = 'idx_carrier_status';
    IF idx_exists = 0 THEN
        ALTER TABLE shipments ADD INDEX idx_carrier_status (carrier_status);
    END IF;
END$$
DELIMITER ;
CALL add_idx_shipments_0008();

-- 3) Dedup hash on carrier_tracking_events
CALL add_idx_if_missing_0008('uq_carrier_event_dedup', 'UNIQUE KEY `uq_carrier_event_dedup` (`dedup_hash`)');
CALL add_idx_if_missing_0008('idx_carrier_event_shipment_ts', 'KEY `idx_carrier_event_shipment_ts` (`shipment_id`, `event_timestamp`)');

-- 4) Carrier status mapping table (optional master config)
CREATE TABLE IF NOT EXISTS `carrier_status_mappings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `integration_id` INT(11) NOT NULL,
    `provider_status` VARCHAR(100) NOT NULL,
    `canonical_status` VARCHAR(50) NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_provider_status` (`integration_id`, `provider_status`),
    KEY `idx_integration` (`integration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Seed some default mappings for common carrier statuses
INSERT IGNORE INTO `carrier_status_mappings` (`integration_id`, `provider_status`, `canonical_status`, `notes`) VALUES
(0, 'pre_transit', 'pending_pickup', 'Default mapping when no integration-specific override exists'),
(0, 'in_transit', 'in_transit', 'Default mapping'),
(0, 'out_for_delivery', 'out_for_delivery', 'Default mapping'),
(0, 'delivered', 'delivered', 'Default mapping'),
(0, 'return_to_sender', 'returned', 'Default mapping'),
(0, 'failure', 'delivery_failed', 'Default mapping'),
(0, 'cancelled', 'cancelled', 'Default mapping');

-- Cleanup
DROP PROCEDURE IF EXISTS add_col_if_missing_0008;
DROP PROCEDURE IF EXISTS add_idx_if_missing_0008;

DELIMITER ;
