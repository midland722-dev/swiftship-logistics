<?php
/**
 * Centralized Shipment Validation
 * -------------------------------
 * Validates all shipment data before creation or update.
 * Returns structured results with errors array and sanitized data.
 */

require_once __DIR__ . '/shipment_status.php';

if (!function_exists('validateShipmentData')) {
    /**
     * Validate a complete shipment data array.
     *
     * @param array $data Raw input data
     * @param bool  $isUpdate True when validating an update (fewer required fields)
     * @return array ['valid' => bool, 'errors' => array, 'data' => array]
     */
    function validateShipmentData(array $data, $isUpdate = false) {
        $errors = [];
        $sanitized = [];

        $required = $isUpdate ? [] : [
            'sender_name', 'receiver_name', 'package_name',
            'origin_city', 'destination_city', 'origin_country', 'destination_country',
        ];

        foreach ($required as $field) {
            $value = trim((string)($data[$field] ?? ''));
            if ($value === '') {
                $errors[] = ucwords(str_replace('_', ' ', $field)) . ' is required.';
            } else {
                $sanitized[$field] = $value;
            }
        }

        if (empty($errors)) {
            $sanitized['sender_name'] = trim($data['sender_name']);
            $sanitized['sender_company'] = trim($data['sender_company'] ?? '');
            $sanitized['sender_phone'] = preg_replace('/[^0-9+\-() ]/', '', trim($data['sender_phone'] ?? ''));
            $sanitized['sender_email'] = strtolower(trim($data['sender_email'] ?? ''));
            $sanitized['sender_address'] = trim($data['sender_address'] ?? '');
            $sanitized['sender_city'] = trim($data['sender_city'] ?? '');
            $sanitized['sender_state'] = trim($data['sender_state'] ?? '');
            $sanitized['sender_postal'] = trim($data['sender_postal'] ?? '');
            $sanitized['sender_country'] = strtoupper(trim($data['sender_country'] ?? 'US'));

            $sanitized['receiver_name'] = trim($data['receiver_name']);
            $sanitized['receiver_company'] = trim($data['receiver_company'] ?? '');
            $sanitized['receiver_phone'] = preg_replace('/[^0-9+\-() ]/', '', trim($data['receiver_phone'] ?? ''));
            $sanitized['receiver_email'] = strtolower(trim($data['receiver_email'] ?? ''));
            $sanitized['receiver_address'] = trim($data['receiver_address'] ?? '');
            $sanitized['receiver_city'] = trim($data['receiver_city'] ?? '');
            $sanitized['receiver_state'] = trim($data['receiver_state'] ?? '');
            $sanitized['receiver_postal'] = trim($data['receiver_postal'] ?? '');
            $sanitized['receiver_country'] = strtoupper(trim($data['receiver_country'] ?? 'US'));

            if ($sanitized['sender_email'] !== '' && !filter_var($sanitized['sender_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Sender email is invalid.';
            }
            if ($sanitized['receiver_email'] !== '' && !filter_var($sanitized['receiver_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Receiver email is invalid.';
            }

            $weight = isset($data['total_weight']) ? floatval($data['total_weight']) : null;
            if ($weight === null) {
                $weight = isset($data['weight']) ? floatval($data['weight']) : 0;
            }
            if ($weight <= 0 && !$isUpdate) {
                $errors[] = 'Weight must be greater than 0.';
            }
            $sanitized['total_weight'] = max(0, $weight);

            $pieces = isset($data['pieces']) ? intval($data['pieces']) : 1;
            $sanitized['pieces'] = max(1, $pieces);

            $length = isset($data['length']) ? floatval($data['length']) : null;
            $width = isset($data['width']) ? floatval($data['width']) : null;
            $height = isset($data['height']) ? floatval($data['height']) : null;
            $sanitized['length'] = $length !== null ? max(0, $length) : null;
            $sanitized['width'] = $width !== null ? max(0, $width) : null;
            $sanitized['height'] = $height !== null ? max(0, $height) : null;

            $volumetric = null;
            if ($length && $width && $height) {
                $volumetric = round(($length * $width * $height) / 5000, 3);
            }
            $sanitized['volumetric_weight'] = $volumetric;

            $declaredValue = isset($data['declared_value']) ? floatval($data['declared_value']) : 0;
            $sanitized['declared_value'] = max(0, $declaredValue);

            $totalAmount = isset($data['total_amount']) ? floatval($data['total_amount']) : 0;
            $sanitized['total_amount'] = max(0, $totalAmount);

            $serviceType = strtolower(trim($data['service_type'] ?? 'standard'));
            $validServices = ['standard', 'express', 'overnight', 'economy', 'same_day', 'same-day'];
            if (!in_array($serviceType, $validServices, true)) {
                $serviceType = 'standard';
            }
            $sanitized['service_type'] = $serviceType;

            $priority = strtolower(trim($data['priority'] ?? 'standard'));
            $validPriorities = ['low', 'standard', 'high', 'express'];
            if (!in_array($priority, $validPriorities, true)) {
                $priority = 'standard';
            }
            $sanitized['priority'] = $priority;

            $status = strtolower(trim($data['status'] ?? ''));
            if ($status !== '' && !isValidStatus($status)) {
                $errors[] = 'Invalid shipment status: ' . htmlspecialchars($status);
            }
            $sanitized['status'] = $status;

            $estDelivery = trim($data['estimated_delivery'] ?? '');
            if ($estDelivery !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $estDelivery)) {
                $errors[] = 'Estimated delivery date must be YYYY-MM-DD.';
            }
            $sanitized['estimated_delivery'] = $estDelivery !== '' ? $estDelivery : null;

            $shipmentDate = trim($data['shipment_date'] ?? '');
            if ($shipmentDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $shipmentDate)) {
                $errors[] = 'Shipment date must be YYYY-MM-DD.';
            }
            $sanitized['shipment_date'] = $shipmentDate !== '' ? $shipmentDate : null;

            $paymentStatus = strtolower(trim($data['payment_status'] ?? 'pending'));
            $validPaymentStatuses = ['pending', 'paid', 'partial', 'refunded', 'cancelled'];
            if (!in_array($paymentStatus, $validPaymentStatuses, true)) {
                $paymentStatus = 'pending';
            }
            $sanitized['payment_status'] = $paymentStatus;

            $sanitized['tracking_number'] = isset($data['tracking_number']) ? strtoupper(trim($data['tracking_number'])) : '';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $sanitized,
        ];
    }
}

if (!function_exists('validateTrackingNumber')) {
    /**
     * Validate a tracking number.
     *
     * @return array ['valid' => bool, 'error' => string|null, 'normalized' => string|null]
     */
    function validateTrackingNumber($raw, PDO $db, $excludeId = 0) {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return ['valid' => false, 'error' => 'Tracking number is required.', 'normalized' => null];
        }
        $normalized = strtoupper(str_replace(' ', '', $raw));
        if (!preg_match('/^[A-Za-z0-9\-]{4,64}$/', $normalized)) {
            return ['valid' => false, 'error' => 'Tracking number must be 4-64 alphanumeric characters or dashes.', 'normalized' => null];
        }
        if ($excludeId > 0) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM shipments WHERE tracking_number = :tn AND id != :id");
            $stmt->execute([':tn' => $normalized, ':id' => $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM shipments WHERE tracking_number = :tn");
            $stmt->execute([':tn' => $normalized]);
        }
        if ((int)$stmt->fetchColumn() > 0) {
            return ['valid' => false, 'error' => 'Tracking number already exists: ' . $normalized, 'normalized' => null];
        }
        return ['valid' => true, 'error' => null, 'normalized' => $normalized];
    }
}

if (!function_exists('validatePhone')) {
    /**
     * Validate and sanitize a phone number.
     *
     * @return array ['valid' => bool, 'error' => string|null, 'sanitized' => string]
     */
    function validatePhone($raw) {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return ['valid' => true, 'error' => null, 'sanitized' => ''];
        }
        $sanitized = preg_replace('/[^0-9+\-() ]/', '', $raw);
        $digits = preg_replace('/[^0-9]/', '', $sanitized);
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            return ['valid' => false, 'error' => 'Phone number must be 7-15 digits.', 'sanitized' => $sanitized];
        }
        return ['valid' => true, 'error' => null, 'sanitized' => $sanitized];
    }
}
