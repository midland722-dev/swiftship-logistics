<?php
/**
 * Seeder: support categories
 */

require_once __DIR__ . '/../config/db.php';

$categories = [
    ['name' => 'General inquiry', 'description' => 'General questions about our services.'],
    ['name' => 'Shipment issue', 'description' => 'Problems with a specific shipment.'],
    ['name' => 'Billing', 'description' => 'Invoices, payments, and refunds.'],
    ['name' => 'Technical', 'description' => 'Website or tracking issues.'],
    ['name' => 'Customs', 'description' => 'Customs clearance and duties.'],
    ['name' => 'Feedback', 'description' => 'Suggestions and feedback.'],
    ['name' => 'Complaint', 'description' => 'Formal complaints.'],
    ['name' => 'Partnership', 'description' => 'Business partnership inquiries.'],
];

foreach ($categories as $c) {
    db_execute(
        'INSERT INTO support_categories (name, description, is_active, created_at)
         VALUES (:name, :description, 1, NOW())
         ON DUPLICATE KEY UPDATE name = VALUES(name)',
        [
            ':name' => $c['name'],
            ':description' => $c['description'],
        ]
    );
}

echo "Seeder 003 completed.\n";
