-- ============================================================
-- Migration 0005: Add Missing Primary Keys and AUTO_INCREMENT
-- ------------------------------------------------------------
-- SAFE TO RE-RUN: Uses conditional checks before each ALTER.
--
-- This migration adds explicit PRIMARY KEY (`id`) AUTO_INCREMENT
-- to all tables where `id` exists but lacks a primary key, and
-- ensures AUTO_INCREMENT columns have explicit keys.
-- ============================================================

DELIMITER $$

-- Helper: add PK + AUTO_INCREMENT to a table if the column exists
-- and the table lacks a PRIMARY KEY.
DROP PROCEDURE IF EXISTS `add_pk_if_missing`;
CREATE PROCEDURE `add_pk_if_missing`(
    IN tbl VARCHAR(64),
    IN col VARCHAR(64),
    IN coltype VARCHAR(64)
)
BEGIN
    DECLARE has_pk INT DEFAULT 0;
    DECLARE has_col INT DEFAULT 0;

    SELECT COUNT(*) INTO has_pk
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND INDEX_NAME = 'PRIMARY';

    SELECT COUNT(*) INTO has_col
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND COLUMN_NAME = col;

    IF has_pk = 0 AND has_col = 1 THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ',
                          'MODIFY COLUMN `', col, '` ', coltype, ' NOT NULL AUTO_INCREMENT, ',
                          'ADD PRIMARY KEY (`', col, '`)');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

-- Helper: add PK + AUTO_INCREMENT using a non-`id` column.
DROP PROCEDURE IF EXISTS `add_pk_alt_if_missing`;
CREATE PROCEDURE `add_pk_alt_if_missing`(
    IN tbl VARCHAR(64),
    IN col VARCHAR(64),
    IN coltype VARCHAR(64)
)
BEGIN
    DECLARE has_pk INT DEFAULT 0;
    DECLARE has_col INT DEFAULT 0;

    SELECT COUNT(*) INTO has_pk
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND INDEX_NAME = 'PRIMARY';

    SELECT COUNT(*) INTO has_col
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND COLUMN_NAME = col;

    IF has_pk = 0 AND has_col = 1 THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ',
                          'MODIFY COLUMN `', col, '` ', coltype, ' NOT NULL AUTO_INCREMENT, ',
                          'ADD PRIMARY KEY (`', col, '`)');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Tables with `id` column missing PK/AUTO_INCREMENT
CALL add_pk_if_missing('activity_logs',           'id', 'bigint(20)');
CALL add_pk_if_missing('api_integration_logs',    'id', 'bigint(20)');
CALL add_pk_if_missing('api_integrations',        'id', 'int(11)');
CALL add_pk_if_missing('api_keys',                'id', 'int(11)');
CALL add_pk_if_missing('api_usage_logs',          'id', 'bigint(20)');
CALL add_pk_if_missing('attachments',             'id', 'int(11)');
CALL add_pk_if_missing('audit_logs_v2',           'id', 'bigint(20)');
CALL add_pk_if_missing('bulk_operations',         'id', 'int(11)');
CALL add_pk_if_missing('bulk_uploads',            'id', 'int(11)');
CALL add_pk_if_missing('communication_logs',      'id', 'bigint(20)');
CALL add_pk_if_missing('communication_logs_enhanced', 'id', 'bigint(20)');
CALL add_pk_if_missing('contact_messages',        'id', 'int(11)');
CALL add_pk_if_missing('customs_checkpoints',     'id', 'int(11)');
CALL add_pk_if_missing('customs_declarations',    'id', 'int(11)');
CALL add_pk_if_missing('customs_documents',       'id', 'int(11)');
CALL add_pk_if_missing('customs_duty_calculations', 'id', 'int(11)');
CALL add_pk_if_missing('customs_inspections',     'id', 'int(11)');
CALL add_pk_if_missing('customs_notification_templates', 'id', 'int(11)');
CALL add_pk_if_missing('customs_officer_logs',    'id', 'int(11)');
CALL add_pk_if_missing('customs_records',         'id', 'int(11)');
CALL add_pk_if_missing('customs_seizures',        'id', 'int(11)');
CALL add_pk_if_missing('customs_tariffs',         'id', 'int(11)');
CALL add_pk_if_missing('daily_stats',             'id', 'int(11)');
CALL add_pk_if_missing('delivery_zones',          'id', 'int(11)');
CALL add_pk_if_missing('document_queue',          'id', 'bigint(20)');
CALL add_pk_if_missing('flagging_rules',          'id', 'int(11)');
CALL add_pk_if_missing('locations',               'id', 'int(11)');
CALL add_pk_if_missing('notifications',           'id', 'int(11)');
CALL add_pk_if_missing('package_dimensions_history', 'id', 'bigint(20)');
CALL add_pk_if_missing('picking_orders',          'id', 'int(11)');
CALL add_pk_if_missing('price_history',           'id', 'int(11)');
CALL add_pk_if_missing('promotions',              'id', 'int(11)');
CALL add_pk_if_missing('receipt_templates',       'id', 'int(11)');
CALL add_pk_if_missing('receipts',                'id', 'int(11)');
CALL add_pk_if_missing('report_schedules',        'id', 'int(11)');
CALL add_pk_if_missing('restricted_item_detections', 'id', 'int(11)');
CALL add_pk_if_missing('services',                'id', 'int(11)');
CALL add_pk_if_missing('sessions',                'id', 'varchar(128)');
CALL add_pk_if_missing('setting_changes',         'id', 'int(11)');
CALL add_pk_if_missing('settlement_payments',     'id', 'int(11)');
CALL add_pk_if_missing('settlements',             'id', 'int(11)');
CALL add_pk_if_missing('shipment_discounts',      'id', 'int(11)');
CALL add_pk_if_missing('shipment_exceptions',     'id', 'int(11)');
CALL add_pk_if_missing('shipment_flags',          'id', 'int(11)');
CALL add_pk_if_missing('shipment_holds',          'id', 'int(11)');
CALL add_pk_if_missing('shipment_routes',         'id', 'int(11)');
CALL add_pk_if_missing('shipment_status_history', 'id', 'int(11)');
CALL add_pk_if_missing('shipment_status_history_v2', 'id', 'int(11)');
CALL add_pk_if_missing('staff_logs',              'id', 'int(11)');
CALL add_pk_if_missing('status_analytics_daily',  'id', 'int(11)');
CALL add_pk_if_missing('status_assignment_rules', 'id', 'int(11)');
CALL add_pk_if_missing('status_automation_log',   'id', 'int(11)');
CALL add_pk_if_missing('status_automation_rules', 'id', 'int(11)');
CALL add_pk_if_missing('status_change_audit',     'id', 'int(11)');
CALL add_pk_if_missing('status_change_requests',  'id', 'int(11)');
CALL add_pk_if_missing('status_dashboard_configs','id', 'int(11)');
CALL add_pk_if_missing('status_notification_logs','id', 'int(11)');
CALL add_pk_if_missing('status_notification_templates', 'id', 'int(11)');
CALL add_pk_if_missing('status_projections',      'id', 'int(11)');
CALL add_pk_if_missing('status_rate_impacts',     'id', 'int(11)');
CALL add_pk_if_missing('status_slas',             'id', 'int(11)');
CALL add_pk_if_missing('status_transition_rules', 'id', 'int(11)');
CALL add_pk_if_missing('support_categories',      'id', 'int(11)');
CALL add_pk_if_missing('system_alerts',           'id', 'int(11)');
CALL add_pk_if_missing('system_config',           'id', 'int(11)');
CALL add_pk_if_missing('system_notifications',    'id', 'int(11)');
CALL add_pk_if_missing('templates',               'id', 'int(11)');
CALL add_pk_if_missing('trade_verifications',     'id', 'int(11)');
CALL add_pk_if_missing('type_shipments',         'id', 'int(11)');
CALL add_pk_if_missing('vehicles',               'id', 'int(11)');
CALL add_pk_if_missing('warehouse_zones',        'id', 'int(11)');
CALL add_pk_if_missing('warehouses',             'id', 'int(11)');
CALL add_pk_if_missing('webhook_events',         'id', 'int(11)');
CALL add_pk_if_missing('zones',                  'id', 'int(11)');

-- Tables with alternate PK column
CALL add_pk_alt_if_missing('courier_paid',        'id', 'int(10)');
CALL add_pk_alt_if_missing('courier_track',       'id', 'int(10)');
CALL add_pk_alt_if_missing('online_booking',      'id', 'int(100)');
CALL add_pk_alt_if_missing('scheduledpickup',     'cid', 'int(11)');
CALL add_pk_alt_if_missing('support_tickets',     'id', 'int(11)');
CALL add_pk_alt_if_missing('support_messages',    'id', 'int(11)');
CALL add_pk_alt_if_missing('shipment_status_history_v2', 'id', 'int(11)');

-- Cleanup
DROP PROCEDURE IF EXISTS `add_pk_if_missing`;
DROP PROCEDURE IF EXISTS `add_pk_alt_if_missing`;

DELIMITER ;

-- ============================================================
-- Performance indexes for high-volume lookups
-- ============================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS `add_idx_if_missing`;
CREATE PROCEDURE `add_idx_if_missing`(
    IN tbl VARCHAR(64),
    IN idx VARCHAR(64),
    IN def VARCHAR(255)
)
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

CALL add_idx_if_missing('activity_logs',              'idx_activity_user',    '(`user_id`, `created_at`)');
CALL add_idx_if_missing('api_usage_logs',             'idx_api_usage_endpoint','(`endpoint`, `created_at`)');
CALL add_idx_if_missing('audit_logs_v2',              'idx_audit_entity_created','(`entity_type`, `entity_id`, `created_at`)');
CALL add_idx_if_missing('customs_records',            'idx_customs_shipment', '(`shipment_id`, `status`)');
CALL add_idx_if_missing('locations',                  'idx_locations_city_country', '(`city`, `country`)');
CALL add_idx_if_missing('notifications',              'idx_notifications_user', '(`user_id`, `is_read`, `created_at`)');
CALL add_idx_if_missing('shipment_status_history',    'idx_history_shipment_occurred','(`shipment_id`, `occurred_at`)');
CALL add_idx_if_missing('shipment_status_history_v2','idx_history_v2_shipment','(`shipment_id`, `occurred_at`)');
CALL add_idx_if_missing('tracking_history',          'idx_tracking_shipment_event','(`shipment_id`, `event_timestamp`)');

DROP PROCEDURE IF EXISTS `add_idx_if_missing`;
