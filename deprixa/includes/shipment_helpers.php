<?php
/**
 * Admin Shipment Helpers
 * ----------------------
 * Shared between shipments.php, shipment_details.php, edit_shipment.php and
 * the assignment/print pages so the courier lifecycle stays consistent.
 *
 * The status map, labels, badges and stepper workflow are now provided by the
 * shared canonical service (includes/shipment_status.php) so the admin and
 * public layers can never drift apart.
 */

require_once __DIR__ . '/../../includes/shipment_status.php';

if (!function_exists('allShipmentStatuses')) {
    /**
     * Canonical courier lifecycle (requirement §5) plus the legacy statuses
     * kept for backward compatibility with older records.
     */
    function allShipmentStatuses() {
        return [
            'created'              => 'Created',
            'pending_pickup'       => 'Pending Pickup',
            'picked_up'            => 'Picked Up',
            'received_origin'      => 'Received at Origin Branch',
            'sorted'               => 'Sorted',
            'in_transit'           => 'In Transit',
            'at_hub'               => 'Arrived at Destination Hub',
            'out_for_delivery'     => 'Out for Delivery',
            'delivered'            => 'Delivered',
            'delivery_failed'      => 'Delivery Failed',
            'customer_unavailable' => 'Customer Unavailable',
            'on_hold'              => 'On Hold',
            'returned'             => 'Returned to Sender',
            'cancelled'            => 'Cancelled',
            'lost'                 => 'Lost',
            'damaged'              => 'Damaged',
            // Legacy values preserved for existing data
            'pending'              => 'Pending',
            'processing'           => 'Processing',
            'at_warehouse'         => 'At Warehouse',
            'customs_inspection'   => 'Customs Inspection',
            'customs_clearance'    => 'Customs Clearance',
            'customs_delayed'      => 'Customs Delayed',
            'customs_seized'       => 'Customs Seized',
            'held'                 => 'Held',
            'security_check'       => 'Security Check',
            'shipment_stopped'      => 'Shipment Stopped',
        ];
    }
}

if (!function_exists('statusLabel')) {
    function statusLabel($status) {
        $map = allShipmentStatuses();
        return $map[strtolower((string)$status)] ?? ucwords(str_replace('_', ' ', (string)$status));
    }
}

if (!function_exists('statusBadgeClass')) {
    /** Bootstrap background class for a status badge. */
    function statusBadgeClass($status) {
        switch (strtolower((string)$status)) {
            case 'delivered':
                return 'bg-success';
            case 'in_transit':
            case 'picked_up':
            case 'received_origin':
            case 'sorted':
            case 'at_hub':
            case 'out_for_delivery':
                return 'bg-primary';
            case 'created':
            case 'pending_pickup':
            case 'pending':
            case 'processing':
            case 'at_warehouse':
                return 'bg-warning text-dark';
            case 'on_hold':
            case 'held':
            case 'customs_inspection':
            case 'customs_clearance':
            case 'customs_delayed':
            case 'customs_seized':
                return 'bg-info text-dark';
            case 'delivery_failed':
            case 'customer_unavailable':
            case 'lost':
            case 'damaged':
            case 'cancelled':
            case 'returned':
            case 'security_check':
            case 'shipment_stopped':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge($status) {
        return '<span class="badge ' . statusBadgeClass($status) . '">'
            . htmlspecialchars(statusLabel($status)) . '</span>';
    }
}

if (!function_exists('shipmentTypeOptions')) {
    function shipmentTypeOptions() {
        return ['document' => 'Document', 'parcel' => 'Parcel', 'freight' => 'Freight', 'express' => 'Express', 'international' => 'International'];
    }
}

if (!function_exists('serviceTypeOptions')) {
    function serviceTypeOptions() {
        return ['standard' => 'Standard', 'express' => 'Express', 'overnight' => 'Overnight', 'economy' => 'Economy', 'same_day' => 'Same Day'];
    }
}

if (!function_exists('fetchDrivers')) {
    function fetchDrivers(PDO $db) {
        try {
            return $db->query("SELECT id, name, employee_code, status FROM drivers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('fetchVehicles')) {
    function fetchVehicles(PDO $db) {
        try {
            return $db->query("SELECT id, registration_number, make, model, type, status FROM vehicles ORDER BY registration_number ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('fetchBranches')) {
    /** Branches/hubs are sourced from the existing `locations` table. */
    function fetchBranches(PDO $db) {
        try {
            return $db->query("SELECT id, name, code, city, country, type FROM locations WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('lookupName')) {
    /** Resolve a name for an id from a simple id=>name array. */
    function lookupName(array $rows, $id, $default = 'N/A') {
        foreach ($rows as $r) {
            if ((int)$r['id'] === (int)$id) {
                return $r['name'] ?? $r['employee_code'] ?? $r['registration_number'] ?? $default;
            }
        }
        return $default;
    }
}

if (!function_exists('shipmentStats')) {
    /** Per-status breakdown used by shipments.php and the dashboard. */
    function shipmentStats(PDO $db) {
        $out = [
            'total'          => 0,
            'pending_pickup' => 0,
            'picked_up'      => 0,
            'in_transit'     => 0,
            'at_hub'         => 0,
            'out_for_delivery' => 0,
            'delivered'      => 0,
            'delivery_failed' => 0,
            'returned'       => 0,
            'cancelled'      => 0,
        ];
        try {
            $rows = $db->query("SELECT status, COUNT(*) c FROM shipments GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
            $out['total'] = array_sum(array_column($rows, 'c'));
            foreach ($rows as $r) {
                $s = strtolower($r['status']);
                if (isset($out[$s])) {
                    $out[$s] = (int)$r['c'];
                }
            }
        } catch (Exception $e) { /* ignore */ }
        return $out;
    }
}

if (!function_exists('courierTableExists')) {
    function courierTableExists(PDO $db, $table) {
        try {
            $db->query("SELECT 1 FROM `$table` LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('ensureCourierTables')) {
    /**
     * Idempotently create the `drivers` and `vehicles` tables used by the
     * courier management module. Mirrors the project's existing
     * ensure*Table() helpers so the feature is self-installing.
     */
    function ensureCourierTables(PDO $db) {
        if (!courierTableExists($db, 'drivers')) {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `drivers` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `employee_code` varchar(50) DEFAULT NULL,
                    `phone` varchar(90) DEFAULT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `vehicle_id` int(11) DEFAULT NULL,
                    `branch_id` int(11) DEFAULT NULL,
                    `status` enum('active','inactive','on_leave') DEFAULT 'active',
                    `license_number` varchar(100) DEFAULT NULL,
                    `notes` text DEFAULT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `employee_code` (`employee_code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
        if (!courierTableExists($db, 'vehicles')) {
            $db->exec("
                CREATE TABLE IF NOT EXISTS `vehicles` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `registration_number` varchar(50) NOT NULL,
                    `type` enum('van','truck','car','motorcycle','other') DEFAULT 'van',
                    `make` varchar(100) DEFAULT NULL,
                    `model` varchar(100) DEFAULT NULL,
                    `year` int(11) DEFAULT NULL,
                    `capacity_kg` decimal(8,3) DEFAULT 0.000,
                    `status` enum('active','maintenance','inactive') DEFAULT 'active',
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `registration_number` (`registration_number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }
}

if (!function_exists('ensureShipmentColumns')) {
    /**
     * Idempotently add the courier-management columns to `shipments`.
     * Only adds columns that are missing (no destructive ALTER), so it is
     * safe to call on every request.
     */
    function ensureShipmentColumns(PDO $db) {
        $cols = [
            'reference_number'   => 'varchar(100) DEFAULT NULL',
            'shipment_type'      => "varchar(50) DEFAULT 'parcel'",
            'shipment_date'      => 'date DEFAULT NULL',
            'sender_name'        => 'varchar(255) DEFAULT NULL',
            'sender_company'     => 'varchar(255) DEFAULT NULL',
            'sender_phone'       => 'varchar(90) DEFAULT NULL',
            'sender_email'       => 'varchar(255) DEFAULT NULL',
            'sender_address'     => 'varchar(300) DEFAULT NULL',
            'sender_city'        => 'varchar(100) DEFAULT NULL',
            'sender_state'       => 'varchar(120) DEFAULT NULL',
            'sender_postal'      => 'varchar(20) DEFAULT NULL',
            'sender_country'     => "char(2) DEFAULT 'US'",
            'receiver_name'      => 'varchar(255) DEFAULT NULL',
            'receiver_company'   => 'varchar(255) DEFAULT NULL',
            'receiver_phone'     => 'varchar(90) DEFAULT NULL',
            'receiver_email'     => 'varchar(255) DEFAULT NULL',
            'receiver_address'   => 'varchar(300) DEFAULT NULL',
            'receiver_city'      => 'varchar(100) DEFAULT NULL',
            'receiver_state'     => 'varchar(120) DEFAULT NULL',
            'receiver_postal'    => 'varchar(20) DEFAULT NULL',
            'receiver_country'   => "char(2) DEFAULT 'US'",
            'package_name'       => 'varchar(255) DEFAULT NULL',
            'package_description' => 'text DEFAULT NULL',
            'length'             => 'decimal(10,2) DEFAULT NULL',
            'width'              => 'decimal(10,2) DEFAULT NULL',
            'height'             => 'decimal(10,2) DEFAULT NULL',
            'volumetric_weight'  => 'decimal(10,2) DEFAULT NULL',
            'contents'           => 'text DEFAULT NULL',
            'cod_amount'         => 'decimal(12,2) DEFAULT 0.00',
            'driver_id'          => 'int(11) DEFAULT NULL',
            'vehicle_id'         => 'int(11) DEFAULT NULL',
            'branch_id'          => 'int(11) DEFAULT NULL',
            'current_city'       => 'varchar(100) DEFAULT NULL',
            'current_country'    => 'char(2) DEFAULT NULL',
            'current_branch'     => 'varchar(255) DEFAULT NULL',
            'delivery_date'      => 'date DEFAULT NULL',
            'delivery_time'      => 'time DEFAULT NULL',
            'pod_photo'          => 'varchar(255) DEFAULT NULL',
            'internal_notes'     => 'text DEFAULT NULL',
            'progress_percent'   => "int(11) DEFAULT NULL",
            'progress_steps'     => 'json DEFAULT NULL',
        ];
        $countRow = $db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
        if ((int)$countRow > 10000) {
            return;
        }
        foreach ($cols as $name => $def) {
            try {
                $db->query("SELECT `$name` FROM shipments LIMIT 1");
            } catch (Exception $e) {
                try { $db->exec("ALTER TABLE shipments ADD COLUMN `$name` $def"); } catch (Exception $e2) { /* ignore */ }
            }
        }
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * True only for Super Admins. Tracking Number and destructive timeline
     * operations are restricted to this role. Promote a manager_admin / users
     * row to role 'super_admin' to grant access.
     */
    function isSuperAdmin() {
        return ($_SESSION['admin_role'] ?? '') === 'super_admin';
    }
}

if (!function_exists('ensureAdvancedShipmentColumns')) {
    /**
     * Idempotently add the advanced courier columns required by the full
     * Edit Shipment module (parcel meta, shipping charges, delivery
     * preferences, customs, internal/customer notes and shipment settings).
     * Safe to call on every request (no destructive ALTER).
     */
    function ensureAdvancedShipmentColumns(PDO $db) {
        $cols = [
            'item_category'            => 'varchar(120) DEFAULT NULL',
            'is_hazardous'             => "tinyint(1) DEFAULT 0",
            'pickup_date'              => 'date DEFAULT NULL',
            'shipping_cost'            => 'decimal(12,2) DEFAULT 0.00',
            'additional_charges'       => 'decimal(12,2) DEFAULT 0.00',
            'discount'                 => 'decimal(12,2) DEFAULT 0.00',
            'tax'                      => 'decimal(12,2) DEFAULT 0.00',
            'route'                    => 'varchar(255) DEFAULT NULL',
            'warehouse'                => 'varchar(255) DEFAULT NULL',
            'distribution_center'      => 'varchar(255) DEFAULT NULL',
            'transaction_id'           => 'varchar(120) DEFAULT NULL',
            'invoice_number'           => 'varchar(120) DEFAULT NULL',
            'signature_required'       => "tinyint(1) DEFAULT 0",
            'contact_before_delivery'  => "tinyint(1) DEFAULT 0",
            'leave_at_door'            => "tinyint(1) DEFAULT 0",
            'weekend_delivery'         => "tinyint(1) DEFAULT 0",
            'preferred_delivery_time'  => 'varchar(60) DEFAULT NULL',
            'customer_notes'           => 'text DEFAULT NULL',
            'is_active'                => "tinyint(1) DEFAULT 1",
            'is_on_hold'               => "tinyint(1) DEFAULT 0",
            'return_to_sender'         => "tinyint(1) DEFAULT 0",
            'is_cancelled'             => "tinyint(1) DEFAULT 0",
            'is_archived'              => "tinyint(1) DEFAULT 0",
            'customs_declaration_number' => 'varchar(120) DEFAULT NULL',
            'hs_code'                  => 'varchar(50) DEFAULT NULL',
            'country_of_origin'        => "char(2) DEFAULT NULL",
            'import_duty'              => 'decimal(12,2) DEFAULT NULL',
            'customs_documents'        => 'text DEFAULT NULL',
            'tax_info'                 => 'text DEFAULT NULL',
        ];
        $countRow = $db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
        if ((int)$countRow > 10000) {
            return;
        }
        foreach ($cols as $name => $def) {
            try {
                $db->query("SELECT `$name` FROM shipments LIMIT 1");
            } catch (Exception $e) {
                try { $db->exec("ALTER TABLE shipments ADD COLUMN `$name` $def"); } catch (Exception $e2) { /* ignore */ }
            }
        }
    }
}

if (!function_exists('ensureAttachmentDocType')) {
    /** Add a `doc_type` discriminator column to the shared `attachments` table. */
    function ensureAttachmentDocType(PDO $db) {
        try {
            $db->query("SELECT doc_type FROM attachments LIMIT 1");
        } catch (Exception $e) {
            try { $db->exec("ALTER TABLE attachments ADD COLUMN doc_type varchar(50) DEFAULT NULL"); } catch (Exception $e2) { /* ignore */ }
        }
    }
}

if (!function_exists('itemCategoryOptions')) {
    function itemCategoryOptions() {
        return [
            'documents'   => 'Documents',
            'electronics' => 'Electronics',
            'clothing'    => 'Clothing & Apparel',
            'food'        => 'Food & Perishables',
            'medicine'    => 'Medicine & Pharma',
            'books'       => 'Books & Media',
            'machinery'   => 'Machinery & Parts',
            'furniture'   => 'Furniture',
            'cosmetics'   => 'Cosmetics & Beauty',
            'automotive'  => 'Automotive',
            'toys'        => 'Toys & Games',
            'other'       => 'Other',
        ];
    }
}

if (!function_exists('currencyOptions')) {
    function currencyOptions() {
        return ['USD','EUR','GBP','CAD','AUD','JPY','CNY','INR','AED','NGN','ZAR','MXN','BRL'];
    }
}

if (!function_exists('yesNoOptions')) {
    function yesNoOptions() {
        return ['0' => 'No', '1' => 'Yes'];
    }
}

if (!function_exists('commonTrackingStatuses')) {
    /**
     * Tracking statuses offered by the Update Tracking module (requirement list).
     * These are a curated subset aligned to the canonical lifecycle.
     */
    function commonTrackingStatuses() {
        return [
            'created'          => 'Shipment Created',
            'pending_pickup'   => 'Pickup Scheduled',
            'picked_up'        => 'Picked Up',
            'received_origin'  => 'Received at Origin Facility',
            'sorted'           => 'Processing Shipment',
            'in_transit'       => 'In Transit',
            'at_hub'           => 'Arrived at Hub',
            'customs_inspection'=> 'Customs Clearance',
            'customs_clearance'=> 'Customs Released',
            'out_for_delivery' => 'Out for Delivery',
            'delivery_failed'  => 'Delivery Attempted',
            'delivered'        => 'Delivered',
            'on_hold'          => 'Delayed',
            'held'             => 'Held at Warehouse',
            'returned'         => 'Returned to Sender',
            'lost'             => 'Lost',
            'damaged'          => 'Damaged',
        ];
    }
}

if (!function_exists('shipmentDocumentTypes')) {
    /** The six manageable document categories for a shipment. */
    function shipmentDocumentTypes() {
        return [
            'shipping_label'    => 'Shipping Label',
            'commercial_invoice'=> 'Commercial Invoice',
            'packing_list'      => 'Packing List',
            'customs_forms'     => 'Customs Forms',
            'proof_of_payment'  => 'Proof of Payment',
            'delivery_documents'=> 'Delivery Documents',
        ];
    }
}

if (!function_exists('ensureShipmentStatusEnum')) {
    /**
     * Idempotently expand the shipments.status enum to include the courier
     * lifecycle statuses (plus legacy ones). Rebuilds the enum only when a
     * required value is missing, so it is safe to call on every request.
     */
    function ensureShipmentStatusEnum(PDO $db) {
        $required = [
            'created','pending_pickup','picked_up','received_origin','sorted',
            'in_transit','at_hub','out_for_delivery','delivered','delivery_failed',
            'customer_unavailable','on_hold','returned','cancelled','lost','damaged',
            'pending','processing','at_warehouse','customs_inspection','customs_clearance',
            'customs_delayed','customs_seized','held','security_check','shipment_stopped',
        ];
        try {
            $countRow = $db->query("SELECT COUNT(*) FROM shipments")->fetchColumn();
            if ((int)$countRow > 10000) {
                return;
            }
            $row = $db->query("SHOW COLUMNS FROM shipments LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            if (!$row) { return; }
            if (!preg_match("/^enum\((.*)\)$/i", $row['Type'], $m)) { return; }
            $existing = str_getcsv($m[1], ',', "'");
            $existing = array_map(fn($v) => trim($v, "'\""), $existing);
            $missing = array_diff($required, $existing);
            if (empty($missing)) { return; }
            $all = array_map(fn($v) => "'$v'", array_unique(array_merge($existing, $required)));
            $enum = 'enum(' . implode(',', $all) . ')';
            $db->exec("ALTER TABLE shipments MODIFY COLUMN status $enum NOT NULL DEFAULT 'created'");
        } catch (Exception $e) { /* ignore */ }
    }
}

if (!function_exists('ensureCourierTrackingEnhancements')) {
    function ensureCourierTrackingEnhancements(PDO $db) {
        try {
            $db->exec("ALTER TABLE shipment_status_history_v2 ADD COLUMN customs_procedure varchar(255) DEFAULT NULL AFTER remarks");
        } catch (Exception $e) { /* ignore */ }
        try {
            $db->exec("ALTER TABLE shipment_status_history_v2 ADD COLUMN event_notes text DEFAULT NULL AFTER customs_procedure");
        } catch (Exception $e) { /* ignore */ }
        try {
            $db->exec("ALTER TABLE shipments ADD COLUMN transit_location varchar(255) DEFAULT NULL AFTER current_branch");
        } catch (Exception $e) { /* ignore */ }
        try {
            $db->exec("ALTER TABLE shipments ADD COLUMN customs_procedure varchar(255) DEFAULT NULL AFTER transit_location");
        } catch (Exception $e) { /* ignore */ }
        $countRow = $db->query("SELECT COUNT(*) FROM shipment_status_history_v2")->fetchColumn();
        if ((int)$countRow > 10000) {
            return;
        }
        try {
            $db->exec("ALTER TABLE shipment_status_history_v2 ADD INDEX idx_history_shipment_time (shipment_id, occurred_at DESC)");
        } catch (Exception $e) { /* ignore */ }
    }
}

if (!function_exists('logShipmentAction')) {
    /**
     * Append an immutable audit entry to activity_logs for a shipment action.
     * Best-effort: never throws. Uses the admin session identity when present.
     */
    function logShipmentAction(PDO $db, $action, $entityId, $description, $metadata = null) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $uid = $_SESSION['admin_id'] ?? null;
            $meta = $metadata === null ? null : json_encode($metadata, JSON_UNESCAPED_SLASHES);
            $db->prepare("
                INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent, metadata, created_at)
                VALUES (:u, :a, 'shipment', :e, :d, :ip, :ua, :m, NOW())
            ")->execute([
                ':u' => $uid, ':a' => $action, ':e' => $entityId, ':d' => $description,
                ':ip' => $ip, ':ua' => substr($ua ?? '', 0, 255), ':m' => $meta,
            ]);
        } catch (Exception $e) { /* audit must never break the request */ }
    }
}

if (!function_exists('ensureModuleTables')) {
    /**
     * Idempotently create the tables used by the new admin modules
     * (assignment history, notification history, delivery confirmations,
     * refunds). Safe to call on every request.
     */
    function ensureModuleTables(PDO $db) {
        try {
            $db->exec("
                CREATE TABLE IF NOT EXISTS courier_assignments (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    shipment_id int(11) NOT NULL,
                    driver_id int(11) DEFAULT NULL,
                    vehicle_id int(11) DEFAULT NULL,
                    branch_id int(11) DEFAULT NULL,
                    route varchar(255) DEFAULT NULL,
                    distribution_center varchar(255) DEFAULT NULL,
                    warehouse varchar(255) DEFAULT NULL,
                    pickup_date date DEFAULT NULL,
                    assigned_by int(11) DEFAULT NULL,
                    created_at timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (id),
                    KEY shipment_id (shipment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS shipment_notifications (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    shipment_id int(11) NOT NULL,
                    channel enum('email','sms','push') NOT NULL DEFAULT 'email',
                    template varchar(50) DEFAULT NULL,
                    recipient varchar(255) DEFAULT NULL,
                    subject varchar(255) DEFAULT NULL,
                    body text DEFAULT NULL,
                    status enum('sent','failed','queued') DEFAULT 'sent',
                    sent_by int(11) DEFAULT NULL,
                    created_at timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (id),
                    KEY shipment_id (shipment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS delivery_confirmations (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    shipment_id int(11) NOT NULL,
                    receiver_name varchar(255) DEFAULT NULL,
                    signature_path varchar(255) DEFAULT NULL,
                    gps_lat decimal(10,7) DEFAULT NULL,
                    gps_lng decimal(11,7) DEFAULT NULL,
                    delivery_date date DEFAULT NULL,
                    delivery_time time DEFAULT NULL,
                    courier_notes text DEFAULT NULL,
                    customer_feedback text DEFAULT NULL,
                    photos json DEFAULT NULL,
                    confirmed_by int(11) DEFAULT NULL,
                    created_at timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (id),
                    KEY shipment_id (shipment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $db->exec("
                CREATE TABLE IF NOT EXISTS refunds (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    shipment_id int(11) NOT NULL,
                    amount decimal(12,2) NOT NULL,
                    currency varchar(10) DEFAULT 'USD',
                    reason varchar(255) DEFAULT NULL,
                    refunded_by int(11) DEFAULT NULL,
                    created_at timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY (id),
                    KEY shipment_id (shipment_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) { /* ignore */ }
    }
}

if (!function_exists('transitLocationOptions')) {
    function transitLocationOptions() {
        return [
            'origin_facility'        => 'Origin Facility',
            'export_warehouse'       => 'Export Warehouse',
            'departure_airport'      => 'Departure Airport',
            'departure_seaport'      => 'Departure Seaport',
            'in_transit'             => 'In Transit',
            'regional_transit_hub'   => 'Regional Transit Hub',
            'arrival_airport_usa'    => 'Arrival Airport (USA)',
            'arrival_seaport_usa'    => 'Arrival Seaport (USA)',
            'us_customs'             => 'US Customs Clearance Facility',
            'domestic_distribution'  => 'Domestic Distribution Center',
            'local_delivery'         => 'Local Delivery Facility',
            'out_for_delivery_loc'   => 'Out for Delivery',
            'delivered_loc'          => 'Delivered',
        ];
    }
}

if (!function_exists('customsProcedureOptions')) {
    function customsProcedureOptions() {
        return [
            'shipment_info_received'    => 'Shipment Information Received',
            'export_doc_verified'       => 'Export Documentation Verified',
            'export_customs_cleared'    => 'Export Customs Cleared',
            'international_transit'     => 'International Transit',
            'transit_processing'        => 'Transit Processing',
            'awaiting_customs'          => 'Awaiting Customs Inspection',
            'customs_inspection'        => 'Customs Inspection',
            'customs_cleared'           => 'Customs Cleared',
            'released_domestic'         => 'Released for Domestic Transport',
            'ready_for_delivery'        => 'Ready for Delivery',
            'final_delivery'            => 'Final Delivery',
            'delivery_completed'        => 'Delivery Completed',
        ];
    }
}

if (!function_exists('canonicalStepKey')) {
    function canonicalStepKey($status) {
        $map = [
            'created' => 'shipment_info_received',
            'pending_pickup' => 'shipment_info_received',
            'picked_up' => 'package_received',
            'received_origin' => 'package_received',
            'sorted' => 'domestic_distribution',
            'in_transit' => 'departed_origin_airport',
            'at_hub' => 'regional_transit_hub',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'delivery_failed' => 'final_delivery',
            'customer_unavailable' => 'final_delivery',
            'on_hold' => 'awaiting_customs',
            'returned' => 'returned',
            'cancelled' => 'cancelled',
            'lost' => 'lost',
            'damaged' => 'damaged',
            'pending' => 'shipment_info_received',
            'processing' => 'shipment_info_received',
            'at_warehouse' => 'export_warehouse',
            'customs_inspection' => 'customs_inspection',
            'customs_clearance' => 'customs_cleared',
            'customs_delayed' => 'awaiting_customs',
            'customs_seized' => 'customs_inspection',
            'held' => 'awaiting_customs',
            'security_check' => 'customs_inspection',
            'shipment_stopped' => 'shipment_stopped',
        ];
        return $map[strtolower((string)$status)] ?? null;
    }
}

if (!function_exists('canonicalStepLabel')) {
    function canonicalStepLabel($key) {
        $opts = customsProcedureOptions();
        return $opts[$key] ?? ucwords(str_replace('_', ' ', $key));
    }
}

if (!function_exists('ensureIntegrationColumns')) {
    /**
     * Idempotently add the integration-layer columns to `tracking_history`
     * and `shipments` (matches the project's ensure*() self-evolving pattern).
     * The authoritative schema lives in database/migrations/0006_integration_layer.sql;
     * this guard lets the feature work even before the migration is applied.
     */
    function ensureIntegrationColumns(PDO $db) {
        $th = [
            'source'          => "varchar(30) DEFAULT 'web'",
            'integration_id'  => 'int(11) DEFAULT NULL',
        ];
        foreach ($th as $name => $def) {
            try { $db->query("SELECT `$name` FROM tracking_history LIMIT 1"); }
            catch (Exception $e) {
                try { $db->exec("ALTER TABLE tracking_history ADD COLUMN `$name` $def"); } catch (Exception $e2) {}
            }
        }
        $sh = [
            'carrier_integration_id' => 'int(11) DEFAULT NULL',
            'external_shipment_id'   => 'varchar(255) DEFAULT NULL',
            'rate_quote_json'        => 'text DEFAULT NULL',
        ];
        foreach ($sh as $name => $def) {
            try { $db->query("SELECT `$name` FROM shipments LIMIT 1"); }
            catch (Exception $e) {
                try { $db->exec("ALTER TABLE shipments ADD COLUMN `$name` $def"); } catch (Exception $e2) {}
            }
        }
        $ai = ['inbound_secret_encrypted' => 'text DEFAULT NULL'];
        foreach ($ai as $name => $def) {
            try { $db->query("SELECT `$name` FROM api_integrations LIMIT 1"); }
            catch (Exception $e) {
                try { $db->exec("ALTER TABLE api_integrations ADD COLUMN `$name` $def"); } catch (Exception $e2) {}
            }
        }
    }
}

if (!function_exists('paymentDateColumn')) {
    function paymentDateColumn(PDO $db): string {
        static $col = null;
        if ($col !== null) return $col;
        try {
            $stmt = $db->query("SHOW COLUMNS FROM payments WHERE Field = 'paid_at'");
            if ($stmt->fetchColumn()) {
                $col = 'paid_at';
            }
        } catch (Exception $e) {}
        if ($col === null) {
            $col = 'created_at';
        }
        return $col;
    }
}

if (!function_exists('clearDashboardCache')) {
    function clearDashboardCache(): void {
        $file = sys_get_temp_dir() . '/shp_dashboard_cache.json';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
