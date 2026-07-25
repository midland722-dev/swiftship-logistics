<?php
/**
 * Quick test: create a shipment and generate a receipt PDF.
 *
 * Usage: visit this file in your browser after uploading to your host.
 *   http://localhost/ships/test-create-receipt.php
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/error-handler.php';
register_error_handler();

$trackingNumber = '';
$error = '';

try {
    $trackingNumber = generate_tracking_number();

    db_execute(
        'INSERT INTO shipments
            (tracking_number, status, service_type, origin_country, origin_city, destination_country, destination_city,
             total_weight, pieces, currency,
             sender_name, sender_email, sender_phone, sender_address, sender_city, sender_state, sender_country,
             receiver_name, receiver_city, receiver_country,
             created_by, created_at, updated_at)
         VALUES
            (:tracking_number, :status, :service_type, :origin_country, :origin_city, :destination_country, :destination_city,
             :total_weight, :pieces, :currency,
             :sender_name, :sender_email, :sender_phone, :sender_address, :sender_city, :sender_state, :sender_country,
             :receiver_name, :receiver_city, :receiver_country,
             :created_by, NOW(), NOW())',
        [
            ':tracking_number' => $trackingNumber,
            ':status'          => 'pending',
            ':service_type'    => 'express',
            ':origin_country'  => 'US',
            ':origin_city'     => 'New York',
            ':destination_country' => 'GB',
            ':destination_city'    => 'London',
            ':total_weight'    => 2.5,
            ':pieces'          => 1,
            ':currency'        => 'USD',
            ':sender_name'     => 'John Doe',
            ':sender_email'    => 'john@example.com',
            ':sender_phone'    => '+1 555-0100',
            ':sender_address'  => '123 Main St',
            ':sender_city'     => 'New York',
            ':sender_state'    => 'NY',
            ':sender_country'  => 'US',
            ':receiver_name'   => 'Jane Smith',
            ':receiver_city'   => 'London',
            ':receiver_country'=> 'GB',
            ':created_by'      => 1,
        ]
    );

    $shipmentId = (int)db()->lastInsertId();
} catch (Throwable $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test: Create Shipment + Receipt</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; padding: 2rem; }
        .card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); max-width: 640px; margin: 0 auto; }
        .success { color: #065f46; background: #d1fae5; padding: .75rem; border-radius: 6px; margin-bottom: 1rem; }
        .error { color: #991b1b; background: #fee2e2; padding: .75rem; border-radius: 6px; margin-bottom: 1rem; }
        .btn { display: inline-block; padding: .6rem 1rem; background: #D62B2B; color: #fff; text-decoration: none; border-radius: 4px; font-weight: 600; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Test: Create Shipment + Receipt</h1>

        <?php if ($error): ?>
            <div class="error">Error: <?= h($error) ?></div>
        <?php elseif ($trackingNumber): ?>
            <div class="success">
                Shipment created successfully!<br>
                <strong>Tracking number:</strong> <?= h($trackingNumber) ?><br>
                <strong>Shipment ID:</strong> <?= (int)$shipmentId ?>
            </div>
            <a class="btn" href="/ships/process/generate_receipt.php?tracking_number=<?= urlencode($trackingNumber) ?>" target="_blank">Download Receipt PDF</a>
            <br><br>
            <a href="/ships/track.php?id=<?= urlencode($trackingNumber) ?>">Track this shipment</a>
        <?php else: ?>
            <p>Click below to create a test shipment and generate a receipt.</p>
            <a class="btn" href="?run=1">Create Test Shipment</a>
        <?php endif; ?>
    </div>
</body>
</html>
