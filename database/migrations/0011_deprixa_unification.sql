-- ============================================================
-- Migration 0011: Deprixa Unification
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. deprixa_customer_id mapping column on users
--   2. deprixa_booking_id mapping column on shipments
--   3. Indexes for fast lookup by legacy deprixa IDs
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0011_deprixa_unification.sql
-- or run via a future apply_0011.php
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `add_col_if_missing_0011`$$
CREATE PROCEDURE `add_col_if_missing_0011`(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
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

DROP PROCEDURE IF EXISTS `add_idx_if_missing_0011`$$
CREATE PROCEDURE `add_idx_if_missing_0011`(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `users` ADD ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Map deprixa customer IDs to main users table
CALL add_col_if_missing_0011('users', 'deprixa_customer_id', 'INT(11) DEFAULT NULL AFTER `id`');

-- 2) Map deprixa booking IDs to main shipments table
CALL add_col_if_missing_0011('shipments', 'deprixa_booking_id', 'INT(11) DEFAULT NULL AFTER `reference_number`');

-- 3) Indexes
CALL add_idx_if_missing_0011('uq_deprixa_customer_id', 'UNIQUE KEY `uq_deprixa_customer_id` (`deprixa_customer_id`)');
CALL add_idx_if_missing_0011('idx_deprixa_customer_id', 'KEY `idx_deprixa_customer_id` (`deprixa_customer_id`)');

-- 4) Index on shipments for deprixa booking lookup
SET @idx_exists := 0;
SELECT COUNT(*) INTO @idx_exists FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = 'idx_deprixa_booking_id';
IF @idx_exists = 0 THEN
    ALTER TABLE shipments ADD INDEX idx_deprixa_booking_id (deprixa_booking_id);
END IF;

-- Cleanup
DROP PROCEDURE IF EXISTS `add_col_if_missing_0011`;
DROP PROCEDURE IF EXISTS `add_idx_if_missing_0011`;

DELIMITER ;
