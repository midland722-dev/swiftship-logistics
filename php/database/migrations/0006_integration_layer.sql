-- ============================================================
-- Migration 0006: Unified Integration Layer
-- ------------------------------------------------------------
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. integration_status_map  (provider status => canonical status)
--   2. payment_intents         (gateway payment lifecycle bridge)
--   3. tracking_history.source + integration_id (carrier-sourced events)
--   4. shipments carrier linkage + external id + rate quote
--   5. api_integrations.inbound_secret_encrypted (inbound webhook HMAC)
--   6. performance indexes
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0006_integration_layer.sql
-- or run database/migrations/apply_0006.php
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `add_col_if_missing_0006`$$
CREATE PROCEDURE `add_col_if_missing_0006`(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
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

DROP PROCEDURE IF EXISTS `add_idx_if_missing_0006`$$
CREATE PROCEDURE `add_idx_if_missing_0006`(IN tbl VARCHAR(64), IN idx VARCHAR(64), IN def VARCHAR(255))
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

-- 1) Provider status mapping
CREATE TABLE IF NOT EXISTS `integration_status_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `provider` varchar(100) NOT NULL,
    `provider_status` varchar(100) NOT NULL,
    `canonical_status` varchar(50) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_provider_status` (`provider`, `provider_status`),
    KEY `idx_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Payment intents bridge
CREATE TABLE IF NOT EXISTS `payment_intents` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `gateway` varchar(100) NOT NULL,
    `gateway_payment_id` varchar(255) NOT NULL,
    `invoice_id` int(11) DEFAULT NULL,
    `shipment_id` int(11) DEFAULT NULL,
    `customer_id` int(11) DEFAULT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'pending',
    `amount` decimal(12,2) DEFAULT NULL,
    `currency` varchar(10) DEFAULT 'USD',
    `client_secret` varchar(255) DEFAULT NULL,
    `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_gateway_payment` (`gateway`, `gateway_payment_id`),
    KEY `idx_invoice` (`invoice_id`),
    KEY `idx_shipment` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) tracking_history carrier source linkage
CALL add_col_if_missing_0006('tracking_history', 'source', "varchar(30) DEFAULT 'web'");
CALL add_col_if_missing_0006('tracking_history', 'integration_id', 'int(11) DEFAULT NULL');

-- 4) shipments carrier linkage
CALL add_col_if_missing_0006('shipments', 'carrier_integration_id', 'int(11) DEFAULT NULL');
CALL add_col_if_missing_0006('shipments', 'external_shipment_id', 'varchar(255) DEFAULT NULL');
CALL add_col_if_missing_0006('shipments', 'rate_quote_json', 'text DEFAULT NULL');

-- 5) inbound webhook secret on api_integrations
CALL add_col_if_missing_0006('api_integrations', 'inbound_secret_encrypted', 'text DEFAULT NULL');

-- 6) indexes
CALL add_idx_if_missing_0006('tracking_history', 'idx_th_source_intg', '(`source`, `integration_id`)');
CALL add_idx_if_missing_0006('shipments', 'idx_shp_carrier', '(`carrier_integration_id`)');

DROP PROCEDURE IF EXISTS `add_col_if_missing_0006`;
DROP PROCEDURE IF EXISTS `add_idx_if_missing_0006`;
