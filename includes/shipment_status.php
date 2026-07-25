<?php
/**
 * Canonical Shipment Status Service
 * ----------------------------------
 * Single source of truth for the courier lifecycle, shared by BOTH the public
 * tracking layer (includes/tracking.php) and the admin helpers
 * (admin/includes/shipment_helpers.php).
 *
 * Before this file existed, two divergent definitions lived in those two
 * places (a narrow public stepper vs. a rich admin map) which could render a
 * valid status as "step 0" on the customer page. Centralising them removes the
 * drift and gives the API a single `isValidStatus()` to validate against.
 *
 * Everything is declared defensively (function_exists / defined) so the file
 * is safe to require multiple times and from any entry point.
 */

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

if (!defined('SHIPMENT_STATUS_MAP')) {
    define('SHIPMENT_STATUS_MAP', serialize(allShipmentStatuses()));
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

if (!function_exists('isValidStatus')) {
    /** True when $status is a recognised shipment status code. */
    function isValidStatus($status) {
        $map = allShipmentStatuses();
        return isset($map[strtolower((string)$status)]);
    }
}

if (!defined('TRACKING_WORKFLOW')) {
    /**
     * Canonical shipment lifecycle used to render the progress stepper.
     * Each step lists the DB `status` values that map onto it. This is the
     * ONLY place the stepper mapping is defined now.
     */
    define('TRACKING_WORKFLOW', serialize([
        ['key' => 'created',          'label' => 'Shipment Created',        'icon' => 'box',           'statuses' => ['pending', 'processing']],
        ['key' => 'picked_up',        'label' => 'Picked Up',               'icon' => 'hand-paper',    'statuses' => ['picked_up']],
        ['key' => 'sorting',          'label' => 'At Sorting Center',       'icon' => 'warehouse',     'statuses' => ['at_warehouse']],
        ['key' => 'in_transit',       'label' => 'In Transit',              'icon' => 'truck',         'statuses' => ['in_transit']],
        ['key' => 'customs',          'label' => 'Customs Clearance',       'icon' => 'passport',      'statuses' => ['customs_inspection', 'customs_clearance', 'customs_delayed', 'customs_seized', 'held']],
        ['key' => 'destination_hub',  'label' => 'Arrived at Destination Hub', 'icon' => 'building',   'statuses' => ['at_hub']],
        ['key' => 'out_for_delivery', 'label' => 'Out for Delivery',        'icon' => 'shipping-fast', 'statuses' => ['out_for_delivery']],
        ['key' => 'delivered',        'label' => 'Delivered',               'icon' => 'check-circle',  'statuses' => ['delivered']],
    ]));
}

if (!defined('TRACKING_TERMINAL')) {
    /** Terminal / exception outcomes that are not part of the linear flow. */
    define('TRACKING_TERMINAL', serialize([
        'returned' => ['label' => 'Returned to Sender', 'icon' => 'undo'],
        'cancelled' => ['label' => 'Cancelled', 'icon' => 'times-circle'],
    ]));
}
