-- ============================================================
-- Migration 0010: Additional Shipment Listing Optimizations
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. Missing single-column indexes on shipments for common filters
--   2. Composite indexes for frequent filter+sort combinations
--   3. Covering indexes for hot-path tracking lookups
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_idx_if_missing_0010$$
CREATE PROCEDURE add_idx_if_missing_0010(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) shipments: missing single-column indexes for common admin filters
CALL add_idx_if_missing_0010('idx_shipments_driver', 'ALTER TABLE `shipments` ADD INDEX `idx_driver` (`driver_id`)');
CALL add_idx_if_missing_0010('idx_shipments_branch', 'ALTER TABLE `shipments` ADD INDEX `idx_branch` (`branch_id`)');
CALL add_idx_if_missing_0010('idx_shipments_service', 'ALTER TABLE `shipments` ADD INDEX `idx_service` (`service_type`)');
CALL add_idx_if_missing_0010('idx_shipments_payment', 'ALTER TABLE `shipments` ADD INDEX `idx_payment` (`payment_status`)');
CALL add_idx_if_missing_0010('idx_shipments_destination', 'ALTER TABLE `shipments` ADD INDEX `idx_destination` (`destination_country`, `destination_city`)');

-- 2) shipments: composite indexes for common filter+sort patterns
CALL add_idx_if_missing_0010('idx_customer_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_status_created` (`customer_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0010('idx_branch_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_branch_status_created` (`branch_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0010('idx_driver_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_driver_status_created` (`driver_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0010('idx_status_destination', 'ALTER TABLE `shipments` ADD INDEX `idx_status_destination` (`status`, `destination_country`, `destination_city`)');

-- 3) tracking_history: covering index for public tracking page
CALL add_idx_if_missing_0010('idx_tracking_history_number', 'ALTER TABLE `tracking_history` ADD INDEX `idx_tracking_number_ts` (`tracking_number`, `event_timestamp`, `status`, `location`)');

-- 4) api_integrations: covering index for active integration lookups
CALL add_idx_if_missing_0010('idx_active_integration_cover', 'ALTER TABLE `api_integrations` ADD INDEX `idx_active_cover` (`is_active`, `integration_type`, `provider`, `id`)');

-- 5) tracking_logs: composite index for fallback path with is_public filter
CALL add_idx_if_missing_0010('idx_tracking_logs_shipment_public_ts', 'ALTER TABLE `tracking_logs` ADD INDEX `idx_shipment_public_ts` (`shipment_id`, `is_public`, `occurred_at`, `id`)');

-- Cleanup
DROP PROCEDURE IF EXISTS add_idx_if_missing_0010$$

DELIMITER ;
