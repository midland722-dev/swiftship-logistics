-- ============================================================
-- Migration 0004: Courier-style Tracking Enhancements
-- ============================================================
-- Adds:
--   - customs_procedure to shipment_status_history_v2
--   - transit_location + customs_procedure to shipments (current state)
-- Safe to re-run via IF NOT EXISTS / ADD COLUMN IF NOT EXISTS.

-- 1) Add customs_procedure to tracking history
SET @dbname = DATABASE();
SET @tbl = 'shipment_status_history_v2';
SET @col = 'customs_procedure';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `remarks`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col = 'event_notes';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text DEFAULT NULL AFTER `customs_procedure`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Add current transit/procedure state to shipments
SET @tbl = 'shipments';
SET @cols := JSON_OBJECT(
  'transit_location', 'IFNULL((SELECT location FROM shipment_status_history_v2 WHERE shipment_id=shipments.id ORDER BY occurred_at DESC LIMIT 1), current_city)',
  'customs_procedure', 'IFNULL((SELECT customs_procedure FROM shipment_status_history_v2 WHERE shipment_id=shipments.id ORDER BY occurred_at DESC LIMIT 1), NULL)'
);

SET @col = 'transit_location';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `current_branch`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col = 'customs_procedure';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `transit_location`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Indexes for timeline filters
SET @idx = 'idx_history_shipment_time';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'shipment_status_history_v2' AND INDEX_NAME = @idx) > 0,
    'SELECT 1',
    'ALTER TABLE `shipment_status_history_v2` ADD INDEX `idx_history_shipment_time` (`shipment_id`, `occurred_at` DESC)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
