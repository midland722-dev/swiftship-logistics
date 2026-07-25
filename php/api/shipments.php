<?php
/**
 * Shipments API
 *
 * GET    /api/shipments.php           - list shipments
 * GET    /api/shipments.php?id={id}   - get one shipment
 * POST   /api/shipments.php           - create shipment
 * PUT    /api/shipments.php?id={id}   - update shipment
 * DELETE /api/shipments.php?id={id}   - delete shipment
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../config/db.php';

require_role(['admin', 'staff']);

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($method === 'GET' && $id > 0) {
    $shipment = db_fetch_one('SELECT * FROM shipments WHERE id = :id LIMIT 1', [':id' => $id]);
    if (!$shipment) {
        json_error('Shipment not found.', 404, 'NOT_FOUND');
    }
    json_success($shipment);
}

if ($method === 'GET') {
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $limit    = max(1, min(100, (int)($_GET['limit'] ?? 20)));
    $offset   = ($page - 1) * $limit;
    $status   = trim($_GET['status'] ?? '');
    $customer = trim($_GET['customer_id'] ?? '');

    $where = [];
    $params = [];

    if ($status !== '') {
        $where[] = 's.status = :status';
        $params[':status'] = $status;
    }
    if ($customer !== '') {
        $where[] = 's.customer_id = :customer_id';
        $params[':customer_id'] = (int)$customer;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = (int)(db_fetch_one("SELECT COUNT(*) AS c FROM shipments s $whereSql", $params)['c'] ?? 0);
    $rows = db_fetch_all(
        "SELECT s.*, u.name AS customer_name, u.email AS customer_email
         FROM shipments s
         LEFT JOIN users u ON u.id = s.customer_id
         $whereSql
         ORDER BY s.created_at DESC
         LIMIT $limit OFFSET $offset",
        $params
    );

    json_success([
        'items' => $rows,
        'pagination' => [
            'page'  => $page,
            'limit' => $limit,
            'total' => $total,
        ],
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $required = ['origin_country', 'origin_city', 'destination_country', 'destination_city'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            json_error("Missing required field: $field", 422, 'VALIDATION_ERROR');
        }
    }

    $trackingNumber = trim((string)($input['tracking_number'] ?? ''));
    if ($trackingNumber === '') {
        $trackingNumber = generate_tracking_number();
    }

    $params = [
        ':tracking_number'   => $trackingNumber,
        ':customer_id'       => isset($input['customer_id']) ? (int)$input['customer_id'] : null,
        ':reference_number'  => trim((string)($input['reference_number'] ?? '')),
        ':status'            => trim((string)($input['status'] ?? 'pending')),
        ':service_type'      => trim((string)($input['service_type'] ?? 'standard')),
        ':priority'          => trim((string)($input['priority'] ?? 'standard')),
        ':origin_country'    => trim((string)$input['origin_country']),
        ':origin_city'       => trim((string)$input['origin_city']),
        ':destination_country'=> trim((string)$input['destination_country']),
        ':destination_city'  => trim((string)$input['destination_city']),
        ':total_weight'      => isset($input['total_weight']) ? (float)$input['total_weight'] : null,
        ':total_volume'      => isset($input['total_volume']) ? (float)$input['total_volume'] : null,
        ':declared_value'    => isset($input['declared_value']) ? (float)$input['declared_value'] : null,
        ':currency'          => trim((string)($input['currency'] ?? 'USD')),
        ':pieces'            => isset($input['pieces']) ? (int)$input['pieces'] : 1,
        ':is_fragile'        => !empty($input['is_fragile']) ? 1 : 0,
        ':is_insured'        => !empty($input['is_insured']) ? 1 : 0,
        ':insurance_amount'  => isset($input['insurance_amount']) ? (float)$input['insurance_amount'] : null,
        ':payment_status'    => trim((string)($input['payment_status'] ?? 'pending')),
        ':payment_method'    => trim((string)($input['payment_method'] ?? '')),
        ':total_amount'      => isset($input['total_amount']) ? (float)$input['total_amount'] : null,
        ':notes'             => trim((string)($input['notes'] ?? '')),
        ':special_instructions'=> trim((string)($input['special_instructions'] ?? '')),
        ':estimated_delivery'=> $input['estimated_delivery'] ?? null,
        ':sender_name'       => trim((string)($input['sender_name'] ?? '')),
        ':sender_phone'      => trim((string)($input['sender_phone'] ?? '')),
        ':sender_email'      => trim((string)($input['sender_email'] ?? '')),
        ':sender_address'    => trim((string)($input['sender_address'] ?? '')),
        ':sender_city'       => trim((string)($input['sender_city'] ?? '')),
        ':sender_state'      => trim((string)($input['sender_state'] ?? '')),
        ':sender_postal'     => trim((string)($input['sender_postal'] ?? '')),
        ':sender_country'    => trim((string)($input['sender_country'] ?? 'US')),
        ':receiver_name'     => trim((string)($input['receiver_name'] ?? '')),
        ':receiver_phone'    => trim((string)($input['receiver_phone'] ?? '')),
        ':receiver_email'    => trim((string)($input['receiver_email'] ?? '')),
        ':receiver_address'  => trim((string)($input['receiver_address'] ?? '')),
        ':receiver_city'     => trim((string)($input['receiver_city'] ?? '')),
        ':receiver_state'    => trim((string)($input['receiver_state'] ?? '')),
        ':receiver_postal'   => trim((string)($input['receiver_postal'] ?? '')),
        ':receiver_country'  => trim((string)($input['receiver_country'] ?? 'US')),
        ':created_by'        => (int)($_SESSION['user_id'] ?? 0),
    ];

    db_execute(
        'INSERT INTO shipments
            (tracking_number, customer_id, reference_number, status, service_type, priority,
             origin_country, origin_city, destination_country, destination_city,
             total_weight, total_volume, declared_value, currency, pieces,
             is_fragile, is_insured, insurance_amount, payment_status, payment_method, total_amount,
             notes, special_instructions, estimated_delivery,
             sender_name, sender_phone, sender_email, sender_address, sender_city, sender_state, sender_postal, sender_country,
             receiver_name, receiver_phone, receiver_email, receiver_address, receiver_city, receiver_state, receiver_postal, receiver_country,
             created_by, created_at, updated_at)
         VALUES
            (:tracking_number, :customer_id, :reference_number, :status, :service_type, :priority,
             :origin_country, :origin_city, :destination_country, :destination_city,
             :total_weight, :total_volume, :declared_value, :currency, :pieces,
             :is_fragile, :is_insured, :insurance_amount, :payment_status, :payment_method, :total_amount,
             :notes, :special_instructions, :estimated_delivery,
             :sender_name, :sender_phone, :sender_email, :sender_address, :sender_city, :sender_state, :sender_postal, :sender_country,
             :receiver_name, :receiver_phone, :receiver_email, :receiver_address, :receiver_city, :receiver_state, :receiver_postal, :receiver_country,
             :created_by, NOW(), NOW())',
        $params
    );

    $newId = (int)db_fetch_one('SELECT LAST_INSERT_ID() AS id')['id'];
    $shipment = db_fetch_one('SELECT * FROM shipments WHERE id = :id LIMIT 1', [':id' => $newId]);

    json_success($shipment, 201, 'Shipment created.');
}

if ($method === 'PUT' && $id > 0) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $allowed = [
        'status', 'service_type', 'priority', 'total_weight', 'total_volume',
        'declared_value', 'currency', 'pieces', 'is_fragile', 'is_insured',
        'insurance_amount', 'payment_status', 'payment_method', 'total_amount',
        'notes', 'special_instructions', 'estimated_delivery', 'actual_delivery',
        'sender_name', 'sender_phone', 'sender_email', 'sender_address',
        'receiver_name', 'receiver_phone', 'receiver_email', 'receiver_address',
    ];

    $sets = [];
    $params = [':id' => $id];

    foreach ($allowed as $field) {
        if (array_key_exists($field, $input)) {
            $sets[] = "`$field` = :$field";
            $params[":$field"] = $input[$field];
        }
    }

    if ($sets) {
        $sql = 'UPDATE shipments SET ' . implode(', ', $sets) . ', updated_at = NOW() WHERE id = :id';
        db_execute($sql, $params);
    }

    $shipment = db_fetch_one('SELECT * FROM shipments WHERE id = :id LIMIT 1', [':id' => $id]);
    json_success($shipment, 200, 'Shipment updated.');
}

if ($method === 'DELETE' && $id > 0) {
    db_execute('DELETE FROM shipments WHERE id = :id', [':id' => $id]);
    json_success(null, 204);
}

json_error('Method not allowed.', 405, 'METHOD_NOT_ALLOWED');
