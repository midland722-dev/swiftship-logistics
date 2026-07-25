-- ============================================================
-- Migration 0009: Performance Indexes + Security Hardening
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. Composite indexes for critical query patterns
--   2. api_integrations secondary indexes
--   3. jobs table migration (from inline PHP to formal DDL)
--   4. Fix api_integrations base schema PK/AI
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_idx_if_missing_0009$$
CREATE PROCEDURE add_idx_if_missing_0009(IN idx VARCHAR(64), IN def VARCHAR(255))
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

-- 1) tracking_history: composite index for public tracking lookup
CALL add_idx_if_missing_0009('idx_tracking_history_shipment_ts', 'ALTER TABLE `tracking_history` ADD INDEX `idx_shipment_event` (`shipment_id`, `event_timestamp`, `id`)');

-- 2) tracking_logs: composite index for fallback path
CALL add_idx_if_missing_0009('idx_tracking_logs_shipment_ts', 'ALTER TABLE `tracking_logs` ADD INDEX `idx_shipment_occurred` (`shipment_id`, `occurred_at`, `id`)');

-- 3) carrier_tracking_events: composite index for public merge query
CALL add_idx_if_missing_0009('idx_carrier_events_shipment_proc_ts', 'ALTER TABLE `carrier_tracking_events` ADD INDEX `idx_shipment_processed_event` (`shipment_id`, `processed`, `event_timestamp`, `id`)');

-- 4) api_integrations: secondary indexes for queries
CALL add_idx_if_missing_0009('idx_api_integrations_active_type', 'ALTER TABLE `api_integrations` ADD INDEX `idx_active_type` (`is_active`, `integration_type`)');
CALL add_idx_if_missing_0009('idx_api_integrations_provider', 'ALTER TABLE `api_integrations` ADD INDEX `idx_provider_type` (`provider`, `integration_type`)');

-- 5) payments: composite for dashboard revenue queries
CALL add_idx_if_missing_0009('idx_payments_status_date', 'ALTER TABLE `payments` ADD INDEX `idx_status_paid_date` (`status`, `paid_at`, `created_at`)');
CALL add_idx_if_missing_0009('idx_payments_customer_status', 'ALTER TABLE `payments` ADD INDEX `idx_customer_status` (`customer_id`, `status`)');

-- 6) shipments: composite for status + date queries
CALL add_idx_if_missing_0009('idx_shipments_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_status_created` (`status`, `created_at` DESC)');
CALL add_idx_if_missing_0009('idx_shipments_carrier_int', 'ALTER TABLE `shipments` ADD INDEX `idx_carrier_integration` (`carrier_integration_id`, `last_carrier_sync_at`)');

-- 7) notifications: composite for user inbox queries
CALL add_idx_if_missing_0009('idx_notifications_user_unread', 'ALTER TABLE `notifications` ADD INDEX `idx_user_unread` (`user_id`, `is_read`, `created_at`)');

-- 8) tracking_history: DB-level dedup (prevent duplicate timeline entries)
CALL add_idx_if_missing_0009('uq_tracking_history_event', 'ALTER TABLE `tracking_history` ADD UNIQUE INDEX `uq_shipment_event` (`shipment_id`, `tracking_number`, `event_timestamp`, `status`)');

-- 9) Ensure jobs table has migration coverage (same DDL as queue.php runtime creation)
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `queue` varchar(50) NOT NULL,
    `payload` longtext NOT NULL,
    `attempts` int(11) DEFAULT 0,
    `status` enum('pending','processing','done','failed') DEFAULT 'pending',
    `available_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `processed_at` datetime NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_queue_status` (`queue`, `status`, `available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10) Ensure api_integrations has PK + AUTO_INCREMENT
CALL add_idx_if_missing_0009('pk_api_integrations', 'ALTER TABLE `api_integrations` ADD PRIMARY KEY (`id`)');
CALL add_idx_if_missing_0009('ai_api_integrations', 'ALTER TABLE `api_integrations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT');

-- Cleanup
DROP PROCEDURE IF EXISTS add_idx_if_missing_0009$$

DELIMITER ;
