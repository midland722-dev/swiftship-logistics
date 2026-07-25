<?php
/**
 * TrackingHistory model.
 *
 * Represents a single row in tracking_history.
 */

class TrackingHistory {
    public ?int $id = null;
    public int $shipment_id;
    public string $tracking_number;
    public string $status;
    public ?string $location = null;
    public ?string $description = null;
    public ?string $customs_procedure = null;
    public ?string $event_notes = null;
    public ?string $latitude = null;
    public ?string $longitude = null;
    public string $event_timestamp;
    public ?string $transit_location = null;
    public ?string $updated_by = null;
    public string $created_at;
    public string $updated_at;

    public static function fromArray(array $row): self {
        $model = new self();
        $model->id = isset($row['id']) ? (int)$row['id'] : null;
        $model->shipment_id = (int)$row['shipment_id'];
        $model->tracking_number = (string)$row['tracking_number'];
        $model->status = (string)$row['status'];
        $model->location = $row['location'] ?? null;
        $model->description = $row['description'] ?? null;
        $model->customs_procedure = $row['customs_procedure'] ?? null;
        $model->event_notes = $row['event_notes'] ?? null;
        $model->latitude = $row['latitude'] ?? null;
        $model->longitude = $row['longitude'] ?? null;
        $model->event_timestamp = (string)$row['event_timestamp'];
        $model->transit_location = $row['transit_location'] ?? null;
        $model->updated_by = $row['updated_by'] ?? null;
        $model->created_at = (string)$row['created_at'];
        $model->updated_at = (string)$row['updated_at'];

        return $model;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'shipment_id' => $this->shipment_id,
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
            'location' => $this->location,
            'description' => $this->description,
            'customs_procedure' => $this->customs_procedure,
            'event_notes' => $this->event_notes,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'event_timestamp' => $this->event_timestamp,
            'transit_location' => $this->transit_location,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function toTrackingArray(): array {
        return [
            'status' => $this->status,
            'location' => $this->location,
            'description' => $this->description,
            'occurred_at' => $this->event_timestamp,
            'customs_procedure' => $this->customs_procedure,
            'transit_location' => $this->transit_location,
        ];
    }
}
