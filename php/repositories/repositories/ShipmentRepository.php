<?php
/**
 * ShipmentRepository
 *
 * Data-access layer for shipments.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Shipment.php';

class ShipmentRepository {
    public static function findById(int $id): ?Shipment {
        $row = db_fetch_one('SELECT * FROM shipments WHERE id = :id LIMIT 1', [':id' => $id]);
        return $row ? Shipment::fromArray($row) : null;
    }

    public static function findByTrackingNumber(string $trackingNumber): ?Shipment {
        $row = db_fetch_one('SELECT * FROM shipments WHERE tracking_number = :tn LIMIT 1', [':tn' => $trackingNumber]);
        return $row ? Shipment::fromArray($row) : null;
    }

    public static function insert(array $data): Shipment {
        $params = [
            ':tracking_number'     => (string)$data['tracking_number'],
            ':customer_id'         => isset($data['customer_id']) ? (int)$data['customer_id'] : null,
            ':reference_number'    => (string)($data['reference_number'] ?? ''),
            ':status'              => (string)($data['status'] ?? 'pending'),
            ':service_type'        => (string)($data['service_type'] ?? 'standard'),
            ':priority'            => (string)($data['priority'] ?? 'standard'),
            ':origin_country'      => (string)$data['origin_country'],
            ':origin_city'         => (string)$data['origin_city'],
            ':destination_country' => (string)$data['destination_country'],
            ':destination_city'    => (string)$data['destination_city'],
            ':total_weight'        => isset($data['total_weight']) ? (float)$data['total_weight'] : null,
            ':total_volume'        => isset($data['total_volume']) ? (float)$data['total_volume'] : null,
            ':declared_value'      => isset($data['declared_value']) ? (float)$data['declared_value'] : null,
            ':currency'            => (string)($data['currency'] ?? 'USD'),
            ':pieces'              => isset($data['pieces']) ? (int)$data['pieces'] : 1,
            ':is_fragile'          => !empty($data['is_fragile']) ? 1 : 0,
            ':is_insured'          => !empty($data['is_insured']) ? 1 : 0,
            ':insurance_amount'    => isset($data['insurance_amount']) ? (float)$data['insurance_amount'] : null,
            ':payment_status'      => (string)($data['payment_status'] ?? 'pending'),
            ':payment_method'      => (string)($data['payment_method'] ?? ''),
            ':total_amount'        => isset($data['total_amount']) ? (float)$data['total_amount'] : null,
            ':notes'               => (string)($data['notes'] ?? ''),
            ':special_instructions'=> (string)($data['special_instructions'] ?? ''),
            ':estimated_delivery'  => $data['estimated_delivery'] ?? null,
            ':actual_delivery'     => $data['actual_delivery'] ?? null,
            ':delivered_by'        => isset($data['delivered_by']) ? (int)$data['delivered_by'] : null,
            ':sender_name'         => (string)($data['sender_name'] ?? ''),
            ':sender_phone'        => (string)($data['sender_phone'] ?? ''),
            ':sender_email'        => (string)($data['sender_email'] ?? ''),
            ':sender_address'      => (string)($data['sender_address'] ?? ''),
            ':sender_city'         => (string)($data['sender_city'] ?? ''),
            ':sender_state'        => (string)($data['sender_state'] ?? ''),
            ':sender_postal'       => (string)($data['sender_postal'] ?? ''),
            ':sender_country'      => (string)($data['sender_country'] ?? 'US'),
            ':receiver_name'       => (string)($data['receiver_name'] ?? ''),
            ':receiver_phone'      => (string)($data['receiver_phone'] ?? ''),
            ':receiver_email'      => (string)($data['receiver_email'] ?? ''),
            ':receiver_address'    => (string)($data['receiver_address'] ?? ''),
            ':receiver_city'       => (string)($data['receiver_city'] ?? ''),
            ':receiver_state'      => (string)($data['receiver_state'] ?? ''),
            ':receiver_postal'     => (string)($data['receiver_postal'] ?? ''),
            ':receiver_country'    => (string)($data['receiver_country'] ?? 'US'),
            ':created_by'          => isset($data['created_by']) ? (int)$data['created_by'] : null,
        ];

        db_execute(
            'INSERT INTO shipments
                (tracking_number, customer_id, reference_number, status, service_type, priority,
                 origin_country, origin_city, destination_country, destination_city,
                 total_weight, total_volume, declared_value, currency, pieces,
                 is_fragile, is_insured, insurance_amount, payment_status, payment_method, total_amount,
                 notes, special_instructions, estimated_delivery, actual_delivery, delivered_by,
                 sender_name, sender_phone, sender_email, sender_address, sender_city, sender_state, sender_postal, sender_country,
                 receiver_name, receiver_phone, receiver_email, receiver_address, receiver_city, receiver_state, receiver_postal, receiver_country,
                 created_by, created_at, updated_at)
             VALUES
                (:tracking_number, :customer_id, :reference_number, :status, :service_type, :priority,
                 :origin_country, :origin_city, :destination_country, :destination_city,
                 :total_weight, :total_volume, :declared_value, :currency, :pieces,
                 :is_fragile, :is_insured, :insurance_amount, :payment_status, :payment_method, :total_amount,
                 :notes, :special_instructions, :estimated_delivery, :actual_delivery, :delivered_by,
                 :sender_name, :sender_phone, :sender_email, :sender_address, :sender_city, :sender_state, :sender_postal, :sender_country,
                 :receiver_name, :receiver_phone, :receiver_email, :receiver_address, :receiver_city, :receiver_state, :receiver_postal, :receiver_country,
                 :created_by, NOW(), NOW())',
            $params
        );

        $id = (int)db_fetch_one('SELECT LAST_INSERT_ID() AS id')['id'];
        return self::findById($id);
    }

    public static function update(int $id, array $data): Shipment {
        $allowed = [
            'status', 'service_type', 'priority', 'total_weight', 'total_volume',
            'declared_value', 'currency', 'pieces', 'is_fragile', 'is_insured',
            'insurance_amount', 'payment_status', 'payment_method', 'total_amount',
            'notes', 'special_instructions', 'estimated_delivery', 'actual_delivery',
            'delivered_by', 'sender_name', 'sender_phone', 'sender_email', 'sender_address',
            'sender_city', 'sender_state', 'sender_postal', 'sender_country',
            'receiver_name', 'receiver_phone', 'receiver_email', 'receiver_address',
            'receiver_city', 'receiver_state', 'receiver_postal', 'receiver_country',
        ];

        $sets = [];
        $params = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "`$field` = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if ($sets) {
            $sql = 'UPDATE shipments SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE id = :id';
            db_execute($sql, $params);
        }

        return self::findById($id);
    }

    public static function delete(int $id): bool {
        return db_execute('DELETE FROM shipments WHERE id = :id', [':id' => $id]) > 0;
    }

    /**
     * @return Shipment[]
     */
    public static function list(array $filters = [], int $page = 1, int $limit = 20): array {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 's.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['customer_id'])) {
            $where[] = 's.customer_id = :customer_id';
            $params[':customer_id'] = (int)$filters['customer_id'];
        }
        if (!empty($filters['service_type'])) {
            $where[] = 's.service_type = :service_type';
            $params[':service_type'] = $filters['service_type'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 's.created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 's.created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $limit;

        return db_fetch_all(
            "SELECT s.*, u.name AS customer_name, u.email AS customer_email
             FROM shipments s
             LEFT JOIN users u ON u.id = s.customer_id
             $whereSql
             ORDER BY s.created_at DESC
             LIMIT $limit OFFSET $offset",
            $params
        );
    }
}
