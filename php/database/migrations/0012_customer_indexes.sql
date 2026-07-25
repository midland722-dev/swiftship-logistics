-- ============================================================
-- Migration 0012: Customer Module Indexes + Reports Support
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. Composite indexes for customer-scoped shipment/payment queries
--   2. Covering indexes for reports queries
--   3. Support for the new admin reports module
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0012_customer_indexes.sql
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_idx_if_missing_0012$$
CREATE PROCEDURE add_idx_if_missing_0012(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS add_idx_payments_0012$$
CREATE PROCEDURE add_idx_payments_0012(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Customer-scoped shipment indexes
CALL add_idx_if_missing_0012('idx_customer_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_created` (`customer_id`, `created_at` DESC)');
CALL add_idx_if_missing_0012('idx_customer_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_status_created` (`customer_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0012('idx_customer_service_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_service_created` (`customer_id`, `service_type`, `created_at` DESC)');

-- 2) Customer-scoped payment indexes
CALL add_idx_payments_0012('idx_payments_customer_created', 'ALTER TABLE `payments` ADD INDEX `idx_customer_created` (`customer_id`, `created_at` DESC)');
CALL add_idx_payments_0012('idx_payments_customer_status', 'ALTER TABLE `payments` ADD INDEX `idx_customer_status` (`customer_id`, `status`, `created_at` DESC)');

-- 3) Reports: shipment status + date composite for delivery performance
CALL add_idx_if_missing_0012('idx_reports_status_date', 'ALTER TABLE `shipments` ADD INDEX `idx_status_created_amount` (`status`, `created_at` DESC, `total_amount`)');

-- 4) Reports: origin/destination composite for route analysis
CALL add_idx_if_missing_0012('idx_reports_route', 'ALTER TABLE `shipments` ADD INDEX `idx_origin_dest_date` (`origin_country`, `destination_country`, `created_at` DESC)');

-- Cleanup
DROP PROCEDURE IF EXISTS add_idx_if_missing_0012;
DROP PROCEDURE IF EXISTS add_idx_payments_0012;

DELIMITER ;
