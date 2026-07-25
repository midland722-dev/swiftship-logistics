-- ============================================================
-- Migration 0016: production hardening — missing indexes + constraints
-- ============================================================
-- Adds indexes that are missing from the canonical schema
-- and are required by production query patterns.
--
-- SAFE TO RE-RUN (idempotent).
-- ============================================================

DELIMITER $$

-- 1) shipments: composite index for admin list filters
--    Used by: WHERE status = ? AND customer_id = ? ORDER BY created_at DESC
DROP PROCEDURE IF EXISTS add_idx_if_missing_0016$$
CREATE PROCEDURE add_idx_if_missing_0016(IN idx VARCHAR(64), IN def TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'shipments'
          AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

-- 2) shipments: index on tracking_number + status for public tracking + status counts
DROP PROCEDURE IF EXISTS add_idx_shipments_tracking_status_0016$$
CREATE PROCEDURE add_idx_shipments_tracking_status_0016()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'shipments'
          AND INDEX_NAME = 'idx_shipments_tracking_status'
    ) THEN
        ALTER TABLE `shipments` ADD INDEX `idx_shipments_tracking_status` (`tracking_number`, `status`);
    END IF;
END$$

-- 3) contact_messages: index on status + created_at for support queue
DROP PROCEDURE IF EXISTS add_idx_contact_messages_status_0016$$
CREATE PROCEDURE add_idx_contact_messages_status_0016()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'contact_messages'
          AND INDEX_NAME = 'idx_contact_messages_status'
    ) THEN
        ALTER TABLE `contact_messages` ADD INDEX `idx_contact_messages_status` (`status`, `created_at`);
    END IF;
END$$

-- 5) users: index on role + is_active for admin user lists
DROP PROCEDURE IF EXISTS add_idx_users_role_active_0016$$
CREATE PROCEDURE add_idx_users_role_active_0016()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND INDEX_NAME = 'idx_users_role_active'
    ) THEN
        ALTER TABLE `users` ADD INDEX `idx_users_role_active` (`role`, `is_active`);
    END IF;
END$$

DELIMITER ;

CALL add_idx_if_missing_0016(
    'idx_shipments_status_customer',
    'ALTER TABLE `shipments` ADD INDEX `idx_shipments_status_customer` (`status`, `customer_id`, `created_at`)'
);

CALL add_idx_shipments_tracking_status_0016();
CALL add_idx_contact_messages_status_0016();
CALL add_idx_users_role_active_0016();

DROP PROCEDURE IF EXISTS add_idx_if_missing_0016$$
DROP PROCEDURE IF EXISTS add_idx_shipments_tracking_status_0016$$
DROP PROCEDURE IF EXISTS add_idx_contact_messages_status_0016$$
DROP PROCEDURE IF EXISTS add_idx_users_role_active_0016$$

DELIMITER ;
