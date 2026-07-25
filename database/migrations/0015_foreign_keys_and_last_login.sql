-- ============================================================
-- Migration 0015: Foreign keys and last_login_at
-- ============================================================
-- Adds foreign key constraints and missing last_login_at column.
-- Safe to re-run via IF NOT EXISTS / ALTER TABLE IGNORE.

-- 1) Add last_login_at to users if missing
SET @dbname = DATABASE();
SET @tbl = 'users';
SET @col = 'last_login_at';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` datetime DEFAULT NULL AFTER `created_at`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE stmt;

-- 2) Add foreign keys (MySQL ignores IF EXISTS for FK, so we use IGNORE)
-- Note: InnoDB required. These are additive only.

SET @fk1 := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'shipments' AND COLUMN_NAME = 'customer_id'
       AND REFERENCED_TABLE_NAME IS NOT NULL) > 0,
    'SELECT 1',
    'ALTER TABLE `shipments` ADD CONSTRAINT `fk_shipments_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE'
  )
);
PREPARE stmt FROM @fk1;
EXECUTE stmt;
DEALLOCATE stmt;

SET @fk2 := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'tracking_history' AND COLUMN_NAME = 'shipment_id'
       AND REFERENCED_TABLE_NAME IS NOT NULL) > 0,
    'SELECT 1',
    'ALTER TABLE `tracking_history` ADD CONSTRAINT `fk_tracking_history_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'
  )
);
PREPARE stmt FROM @fk2;
EXECUTE stmt;
DEALLOCATE stmt;
