-- ============================================================
-- Migration 0003: Unify Status & Tracking History
-- ------------------------------------------------------------
-- SAFE TO RE-RUN.
--   1. Aligns the shipments.status ENUM with the canonical courier
--      lifecycle (idempotent MODIFY — preserves existing rows).
--   2. Back-fills the public tracking_history store from the admin
--      shipment_status_history_v2 table where a row is missing, so both
--      the admin detail page and the customer tracking page show the
--      same timeline.
--   3. Adds performance indexes used by the new filters (driver / branch /
--      vehicle / current_city).
--
-- How to apply (one time):
--   mysql -u shipuser -p shipping_db < database/migrations/0003_unify_history_and_status.sql
-- or import via phpMyAdmin.
-- ============================================================

-- 1) Align the status enum with the canonical set.
ALTER TABLE `shipments`
  MODIFY COLUMN `status` enum(
    'created','pending_pickup','picked_up','received_origin','sorted',
    'in_transit','at_hub','out_for_delivery','delivered','delivery_failed',
    'customer_unavailable','on_hold','returned','cancelled','lost','damaged',
    'pending','processing','at_warehouse','customs_inspection','customs_clearance',
    'customs_delayed','customs_seized','held','security_check','shipment_stopped'
  ) NOT NULL DEFAULT 'created';

-- 2) Back-fill tracking_history from shipment_status_history_v2 (missing rows only).
INSERT INTO `tracking_history`
    (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, created_at)
SELECT v.shipment_id, s.tracking_number, v.status_code, v.location, v.remarks, v.occurred_at, v.occurred_by, v.occurred_at
FROM `shipment_status_history_v2` v
JOIN `shipments` s ON s.id = v.shipment_id
LEFT JOIN `tracking_history` t
       ON t.shipment_id = v.shipment_id
      AND t.status = v.status_code
      AND t.event_timestamp = v.occurred_at
WHERE t.id IS NULL;

-- 3) Performance indexes for the Manage Shipments filters (added only if missing).
DROP PROCEDURE IF EXISTS `add_idx_if_missing`;
DELIMITER $$
CREATE PROCEDURE `add_idx_if_missing`(IN tbl VARCHAR(64), IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD INDEX `', idx, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL add_idx_if_missing('shipments', 'idx_shipments_driver',   '(`driver_id`)');
CALL add_idx_if_missing('shipments', 'idx_shipments_branch',  '(`branch_id`)');
CALL add_idx_if_missing('shipments', 'idx_shipments_vehicle', '(`vehicle_id`)');
CALL add_idx_if_missing('shipments', 'idx_shipments_curcity', '(`current_city`)');

DROP PROCEDURE IF EXISTS `add_idx_if_missing`;
