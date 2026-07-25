<?php
/**
 * TrackingService
 *
 * Business logic for tracking operations.
 * Ensures every status change creates an immutable tracking_history record.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Shipment.php';
require_once __DIR__ . '/../models/TrackingHistory.php';
require_once __DIR__ . '/../repositories/ShipmentRepository.php';
require_once __DIR__ . '/../repositories/TrackingHistoryRepository.php';
require_once __DIR__ . '/../validation/TrackingValidator.php';

class TrackingService {
    /**
     * Track a shipment by tracking number.
     *
     * @return array{shipment: Shipment|null, history: TrackingHistory[]}
     */
    public static function track(string $trackingNumber): array {
        $shipment = ShipmentRepository::findByTrackingNumber($trackingNumber);
        $history = [];

        if ($shipment) {
            $history = TrackingHistoryRepository::findByShipmentId($shipment->id);
        }

        return ['shipment' => $shipment, 'history' => $history];
    }

    /**
     * Update shipment status and create an immutable tracking_history record.
     *
     * @return array{shipment: Shipment, event: TrackingHistory}
     */
    public static function updateStatus(array $input, int $userId): array {
        $errors = TrackingValidator::validateStatusUpdate($input);
        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        $shipmentId = isset($input['shipment_id']) ? (int)$input['shipment_id'] : 0;
        $trackingNumber = trim((string)$input['tracking_number']);
        $status = trim((string)$input['status']);
        $location = $input['location'] ?? null;
        $description = $input['description'] ?? null;
        $eventTimestamp = $input['event_timestamp'] ?? date('Y-m-d H:i:s');
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $eventTimestamp)) {
            throw new InvalidArgumentException('Invalid event_timestamp format. Use YYYY-MM-DD HH:MM:SS.');
        }

        if ($shipmentId > 0) {
            $shipment = ShipmentRepository::findById($shipmentId);
        } else {
            $shipment = ShipmentRepository::findByTrackingNumber($trackingNumber);
        }

        if (!$shipment) {
            throw new RuntimeException('Shipment not found.', 404);
        }

        if ($shipment->tracking_number !== $trackingNumber) {
            throw new RuntimeException('Tracking number mismatch.', 422);
        }

        $transitionErrors = TrackingValidator::validateTransition($shipment->status, $status);
        if ($transitionErrors) {
            throw new InvalidArgumentException(implode(' ', $transitionErrors));
        }

        $event = TrackingHistoryRepository::insert([
            'shipment_id'      => $shipment->id,
            'tracking_number'  => $trackingNumber,
            'status'           => $status,
            'location'         => $location,
            'description'      => $description,
            'event_timestamp'  => $eventTimestamp,
            'updated_by'       => (string)$userId,
        ]);

        $updated = ShipmentRepository::update($shipment->id, ['status' => $status]);

        return ['shipment' => $updated, 'event' => $event];
    }

    /**
     * Create a new shipment with initial tracking_history event.
     *
     * @return array{shipment: Shipment, event: TrackingHistory}
     */
    public static function createShipment(array $data, int $userId): array {
        if (empty($data['tracking_number'])) {
            $data['tracking_number'] = generate_tracking_number();
        }

        $errors = TrackingValidator::validateShipmentCreate($data);
        if ($errors) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        $data['created_by'] = $userId;
        $data['status'] = $data['status'] ?? 'pending';

        $shipment = ShipmentRepository::insert($data);

        $event = TrackingHistoryRepository::insert([
            'shipment_id'      => $shipment->id,
            'tracking_number'  => $shipment->tracking_number,
            'status'           => $shipment->status,
            'location'         => $shipment->origin_city,
            'description'      => 'Shipment created',
            'event_timestamp'  => date('Y-m-d H:i:s'),
            'updated_by'       => (string)$userId,
        ]);

        return ['shipment' => $shipment, 'event' => $event];
    }

    /**
     * @return TrackingHistory[]
     */
    public static function getHistory(string $trackingNumber): array {
        $shipment = ShipmentRepository::findByTrackingNumber($trackingNumber);
        if (!$shipment) {
            return [];
        }
        return TrackingHistoryRepository::findByShipmentId($shipment->id);
    }
}
