-- ============================================================
-- Migration 0015: tracking_history production hardening
-- ============================================================
-- SAFE TO RE-RUN. Uses stored procedures to check metadata
-- before applying each change. No destructive operations.
--
-- For shared hosting: if stored procedures are not permitted,
-- use the PHP applier (apply_0015.php) instead — it issues
-- the same ALTERs conditionally without needing DELIMITER.
-- ============================================================

DELIMITER $$

-- 1) Add transit_location column if missing
DROP PROCEDURE IF EXISTS add_col_if_missing_0015$$
CREATE PROCEDURE add_col_if_missing_0015()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tracking_history'
          AND COLUMN_NAME = 'transit_location'
    ) THEN
        ALTER TABLE `tracking_history`
            ADD COLUMN `transit_location` varchar(255) DEFAULT NULL
            AFTER `location`;
    END IF;
END$$

-- 2) Add index if missing
DROP PROCEDURE IF EXISTS add_idx_if_missing_0015$$
CREATE PROCEDURE add_idx_if_missing_0015(IN idx VARCHAR(64), IN def TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tracking_history'
          AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

-- 3) Add foreign key only if no orphans exist
DROP PROCEDURE IF EXISTS add_fk_if_clean_0015$$
CREATE PROCEDURE add_fk_if_clean_0015(
    IN idx_name VARCHAR(64),
    IN col VARCHAR(64),
    IN ref_tbl VARCHAR(64),
    IN ref_col VARCHAR(64),
    IN on_delete VARCHAR(10)
)
BEGIN
    DECLARE orphan_count INT DEFAULT 0;
    DECLARE idx_exists INT DEFAULT 0;

    SELECT COUNT(*) INTO idx_exists
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tracking_history'
      AND INDEX_NAME = idx_name;

    IF idx_exists = 0 THEN
        SELECT COUNT(*) INTO orphan_count
        FROM `tracking_history` th
        LEFT JOIN `shipments` s ON s.id = th.`shipment_id`
        WHERE s.id IS NULL;

        IF orphan_count = 0 THEN
            SET @sql = CONCAT(
                'ALTER TABLE `tracking_history` ',
                'ADD CONSTRAINT `', idx_name, '` ',
                'FOREIGN KEY (`', col, '`) ',
                'REFERENCES `', ref_tbl, '`(`', ref_col, '`) ',
                'ON DELETE ', on_delete
            );
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END IF;
    END IF;
END$$

DELIMITER ;

-- Execute changes
CALL add_col_if_missing_0015();

CALL add_idx_if_missing_0015(
    'idx_tracking_history_number_ts',
    'ALTER TABLE `tracking_history` ADD INDEX `idx_tracking_history_number_ts` (`tracking_number`, `event_timestamp`)'
);

CALL add_idx_if_missing_0015(
    'idx_tracking_history_shipment_ts',
    'ALTER TABLE `tracking_history` ADD INDEX `idx_tracking_history_shipment_ts` (`shipment_id`, `event_timestamp` DESC)'
);

CALL add_fk_if_clean_0015(
    'fk_tracking_history_shipment',
    'shipment_id',
    'shipments',
    'id',
    'CASCADE'
);

CALL add_fk_if_clean_0015(
    'fk_tracking_history_updated_by',
    'updated_by',
    'users',
    'id',
    'SET NULL'
);

-- Cleanup
DROP PROCEDURE IF EXISTS add_col_if_missing_0015$$
DROP PROCEDURE IF EXISTS add_idx_if_missing_0015$$
DROP PROCEDURE IF EXISTS add_fk_if_clean_0015$$

DELIMITER ;
