-- ============================================================
-- Migration 0013: Add transit_location to tracking_history
-- ============================================================
-- Ensures the tracking_history table has a transit_location column
-- to store the parcel's physical location independently from
-- the customs procedure.
--
-- Safe to re-run via IF NOT EXISTS / ADD COLUMN IF NOT EXISTS.

SET @dbname = DATABASE();
SET @tbl = 'tracking_history';
SET @col = 'transit_location';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `location`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE stmt;
