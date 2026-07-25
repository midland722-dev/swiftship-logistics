<?php
/**
 * TrackingValidator
 *
 * Validation rules for tracking-related input.
 */

class TrackingValidator {
    public static function validateTrackingNumber(string $trackingNumber): array {
        $errors = [];

        if ($trackingNumber === '') {
            $errors[] = 'Tracking number is required.';
        } elseif (!preg_match('/^[A-Za-z0-9\-_]{3,60}$/', $trackingNumber)) {
            $errors[] = 'Invalid tracking number format. Use 3-60 alphanumeric characters, hyphens, or underscores.';
        }

        return $errors;
    }

    public static function validateStatusUpdate(array $input): array {
        $errors = [];

        $shipmentId = isset($input['shipment_id']) ? (int)$input['shipment_id'] : 0;
        $trackingNumber = trim((string)($input['tracking_number'] ?? ''));

        if ($shipmentId <= 0 && $trackingNumber === '') {
            $errors[] = 'shipment_id or tracking_number is required.';
        }

        $status = trim((string)($input['status'] ?? ''));
        if ($status === '') {
            $errors[] = 'status is required.';
        }

        if (!empty($input['event_timestamp'])) {
            $d = DateTime::createFromFormat('Y-m-d H:i:s', $input['event_timestamp']);
            if (!$d) {
                $errors[] = 'event_timestamp must be in format YYYY-MM-DD HH:ii:ss.';
            }
        }

        return $errors;
    }

    private static array $allowedTransitions = [
        'pending'            => ['processing', 'cancelled', 'held', 'picked_up'],
        'processing'         => ['picked_up', 'cancelled', 'held', 'at_warehouse'],
        'picked_up'          => ['at_warehouse', 'held', 'returned', 'in_transit'],
        'at_warehouse'       => ['in_transit', 'held', 'returned', 'out_for_delivery'],
        'in_transit'         => ['at_hub', 'customs_inspection', 'held', 'delivered', 'returned', 'out_for_delivery'],
        'at_hub'             => ['out_for_delivery', 'in_transit', 'held', 'returned'],
        'customs_inspection' => ['customs_clearance', 'customs_delayed', 'held', 'returned', 'cancelled'],
        'customs_clearance'  => ['in_transit', 'out_for_delivery', 'held', 'returned'],
        'customs_delayed'    => ['customs_inspection', 'customs_clearance', 'held', 'returned', 'cancelled'],
        'customs_seized'     => ['held', 'returned', 'cancelled'],
        'security_check'     => ['customs_inspection', 'in_transit', 'held', 'returned', 'cancelled'],
        'held'               => ['in_transit', 'out_for_delivery', 'processing', 'cancelled', 'returned'],
        'out_for_delivery'   => ['delivered', 'held', 'returned'],
        'delivered'          => [],
        'returned'           => ['pending', 'processing', 'cancelled'],
        'cancelled'          => [],
        'created'            => ['pending_pickup', 'cancelled'],
        'pending_pickup'     => ['received_origin', 'cancelled'],
        'received_origin'    => ['sorted', 'held', 'returned'],
        'sorted'             => ['in_transit', 'held', 'returned'],
        'delivery_failed'    => ['out_for_delivery', 'held', 'returned'],
        'customer_unavailable' => ['out_for_delivery', 'held', 'returned'],
        'on_hold'            => ['in_transit', 'out_for_delivery', 'processing', 'cancelled', 'returned'],
        'lost'               => ['returned', 'cancelled'],
        'damaged'            => ['returned', 'cancelled'],
        'shipment_stopped'   => ['in_transit', 'returned', 'cancelled'],
    ];

    public static function validateTransition(string $currentStatus, string $newStatus): array {
        $errors = [];

        if ($currentStatus === $newStatus) {
            return $errors;
        }

        $allowed = self::$allowedTransitions[$currentStatus] ?? null;
        if ($allowed === null) {
            $errors[] = "Unknown current status: $currentStatus.";
        } elseif (!in_array($newStatus, $allowed, true)) {
            $errors[] = "Invalid status transition from '$currentStatus' to '$newStatus'.";
        }

        return $errors;
    }

    public static function validateShipmentCreate(array $input): array {
        $errors = [];

        $required = ['tracking_number', 'origin_country', 'origin_city', 'destination_country', 'destination_city'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $errors[] = "$field is required.";
            }
        }

        if (!empty($input['tracking_number']) && !preg_match('/^[A-Za-z0-9\-_]{3,60}$/', $input['tracking_number'])) {
            $errors[] = 'Invalid tracking_number format.';
        }

        if (isset($input['total_weight']) && ((float)$input['total_weight'] < 0 || (float)$input['total_weight'] > 10000)) {
            $errors[] = 'total_weight must be between 0 and 10,000 kg.';
        }

        if (isset($input['total_amount']) && (float)$input['total_amount'] < 0) {
            $errors[] = 'total_amount cannot be negative.';
        }

        return $errors;
    }
}
