<?php
/**
 * TrackingHistoryRepository
 *
 * Data-access layer for tracking_history.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/TrackingHistory.php';

class TrackingHistoryRepository {
    /**
     * @return TrackingHistory[]
     */
    public static function findByTrackingNumber(string $trackingNumber, int $limit = 100): array {
        $limit = (int)$limit;
        $rows = db_fetch_all(
            "SELECT * FROM tracking_history WHERE tracking_number = :tn ORDER BY event_timestamp ASC, id ASC LIMIT $limit",
            [':tn' => $trackingNumber]
        );

        return array_map([TrackingHistory::class, 'fromArray'], $rows);
    }

    /**
     * @return TrackingHistory[]
     */
    public static function findByShipmentId(int $shipmentId, int $limit = 100): array {
        $limit = (int)$limit;
        $rows = db_fetch_all(
            "SELECT * FROM tracking_history WHERE shipment_id = :sid ORDER BY event_timestamp DESC, id DESC LIMIT $limit",
            [':sid' => $shipmentId]
        );

        return array_map([TrackingHistory::class, 'fromArray'], $rows);
    }

    public static function insert(array $data): TrackingHistory {
        $params = [
            ':shipment_id'      => (int)$data['shipment_id'],
            ':tracking_number'  => (string)$data['tracking_number'],
            ':status'           => (string)$data['status'],
            ':location'         => $data['location'] ?? null,
            ':description'      => $data['description'] ?? null,
            ':customs_procedure'=> $data['customs_procedure'] ?? null,
            ':event_notes'      => $data['event_notes'] ?? null,
            ':latitude'         => $data['latitude'] ?? null,
            ':longitude'        => $data['longitude'] ?? null,
            ':event_timestamp'  => (string)$data['event_timestamp'],
            ':transit_location' => $data['transit_location'] ?? null,
            ':updated_by'       => $data['updated_by'] ?? null,
        ];

        db_execute(
            'INSERT INTO tracking_history
                (shipment_id, tracking_number, status, location, description, customs_procedure, event_notes, latitude, longitude, event_timestamp, transit_location, updated_by, created_at, updated_at)
             VALUES
                (:shipment_id, :tracking_number, :status, :location, :description, :customs_procedure, :event_notes, :latitude, :longitude, :event_timestamp, :transit_location, :updated_by, NOW(), NOW())',
            $params
        );

        $id = (int)db_fetch_one('SELECT LAST_INSERT_ID() AS id')['id'];

        return self::findById($id);
    }

    public static function findById(int $id): ?TrackingHistory {
        $row = db_fetch_one('SELECT * FROM tracking_history WHERE id = :id LIMIT 1', [':id' => $id]);
        return $row ? TrackingHistory::fromArray($row) : null;
    }
}
