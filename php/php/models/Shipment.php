<?php
/**
 * Shipment model.
 */

class Shipment {
    public ?int $id = null;
    public string $tracking_number;
    public ?int $customer_id = null;
    public ?string $reference_number = null;
    public string $status;
    public string $service_type;
    public string $priority;
    public string $origin_country;
    public string $origin_city;
    public string $destination_country;
    public string $destination_city;
    public ?float $total_weight = null;
    public ?float $total_volume = null;
    public ?float $declared_value = null;
    public string $currency;
    public int $pieces;
    public bool $is_fragile;
    public bool $is_insured;
    public ?float $insurance_amount = null;
    public string $payment_status;
    public ?string $payment_method = null;
    public ?float $total_amount = null;
    public ?string $notes = null;
    public ?string $special_instructions = null;
    public ?string $estimated_delivery = null;
    public ?string $actual_delivery = null;
    public ?int $delivered_by = null;
    public ?string $sender_name = null;
    public ?string $sender_phone = null;
    public ?string $sender_email = null;
    public ?string $sender_address = null;
    public ?string $sender_city = null;
    public ?string $sender_state = null;
    public ?string $sender_postal = null;
    public string $sender_country;
    public ?string $receiver_name = null;
    public ?string $receiver_phone = null;
    public ?string $receiver_email = null;
    public ?string $receiver_address = null;
    public ?string $receiver_city = null;
    public ?string $receiver_state = null;
    public ?string $receiver_postal = null;
    public string $receiver_country;
    public ?int $created_by = null;
    public string $created_at;
    public string $updated_at;

    public static function fromArray(array $row): self {
        $model = new self();
        $model->id = isset($row['id']) ? (int)$row['id'] : null;
        $model->tracking_number = (string)$row['tracking_number'];
        $model->customer_id = isset($row['customer_id']) ? (int)$row['customer_id'] : null;
        $model->reference_number = $row['reference_number'] ?? null;
        $model->status = (string)$row['status'];
        $model->service_type = (string)$row['service_type'];
        $model->priority = (string)$row['priority'];
        $model->origin_country = (string)$row['origin_country'];
        $model->origin_city = (string)$row['origin_city'];
        $model->destination_country = (string)$row['destination_country'];
        $model->destination_city = (string)$row['destination_city'];
        $model->total_weight = isset($row['total_weight']) ? (float)$row['total_weight'] : null;
        $model->total_volume = isset($row['total_volume']) ? (float)$row['total_volume'] : null;
        $model->declared_value = isset($row['declared_value']) ? (float)$row['declared_value'] : null;
        $model->currency = (string)$row['currency'];
        $model->pieces = isset($row['pieces']) ? (int)$row['pieces'] : 1;
        $model->is_fragile = !empty($row['is_fragile']);
        $model->is_insured = !empty($row['is_insured']);
        $model->insurance_amount = isset($row['insurance_amount']) ? (float)$row['insurance_amount'] : null;
        $model->payment_status = (string)$row['payment_status'];
        $model->payment_method = $row['payment_method'] ?? null;
        $model->total_amount = isset($row['total_amount']) ? (float)$row['total_amount'] : null;
        $model->notes = $row['notes'] ?? null;
        $model->special_instructions = $row['special_instructions'] ?? null;
        $model->estimated_delivery = $row['estimated_delivery'] ?? null;
        $model->actual_delivery = $row['actual_delivery'] ?? null;
        $model->delivered_by = isset($row['delivered_by']) ? (int)$row['delivered_by'] : null;
        $model->sender_name = $row['sender_name'] ?? null;
        $model->sender_phone = $row['sender_phone'] ?? null;
        $model->sender_email = $row['sender_email'] ?? null;
        $model->sender_address = $row['sender_address'] ?? null;
        $model->sender_city = $row['sender_city'] ?? null;
        $model->sender_state = $row['sender_state'] ?? null;
        $model->sender_postal = $row['sender_postal'] ?? null;
        $model->sender_country = (string)($row['sender_country'] ?? 'US');
        $model->receiver_name = $row['receiver_name'] ?? null;
        $model->receiver_phone = $row['receiver_phone'] ?? null;
        $model->receiver_email = $row['receiver_email'] ?? null;
        $model->receiver_address = $row['receiver_address'] ?? null;
        $model->receiver_city = $row['receiver_city'] ?? null;
        $model->receiver_state = $row['receiver_state'] ?? null;
        $model->receiver_postal = $row['receiver_postal'] ?? null;
        $model->receiver_country = (string)($row['receiver_country'] ?? 'US');
        $model->created_by = isset($row['created_by']) ? (int)$row['created_by'] : null;
        $model->created_at = (string)$row['created_at'];
        $model->updated_at = (string)$row['updated_at'];

        return $model;
    }

    public function toArray(): array {
        return get_object_vars($this);
    }

    public function toPublicArray(): array {
        return [
            'id' => $this->id,
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
            'service_type' => $this->service_type,
            'priority' => $this->priority,
            'origin_country' => $this->origin_country,
            'origin_city' => $this->origin_city,
            'destination_country' => $this->destination_country,
            'destination_city' => $this->destination_city,
            'total_weight' => $this->total_weight,
            'total_volume' => $this->total_volume,
            'currency' => $this->currency,
            'pieces' => $this->pieces,
            'estimated_delivery' => $this->estimated_delivery,
            'actual_delivery' => $this->actual_delivery,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function toTrackingArray(): array {
        return [
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
            'service_type' => $this->service_type,
            'priority' => $this->priority,
            'origin_country' => $this->origin_country,
            'origin_city' => $this->origin_city,
            'destination_country' => $this->destination_country,
            'destination_city' => $this->destination_city,
            'total_weight' => $this->total_weight,
            'currency' => $this->currency,
            'estimated_delivery' => $this->estimated_delivery,
            'actual_delivery' => $this->actual_delivery,
            'created_at' => $this->created_at,
        ];
    }
}
