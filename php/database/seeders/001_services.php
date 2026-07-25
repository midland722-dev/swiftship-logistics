<?php
/**
 * Seeder: services + calculator
 */

require_once __DIR__ . '/../config/db.php';

$services = [
    ['name' => 'Standard', 'description' => 'Economy ground and sea.', 'priority' => 'standard'],
    ['name' => 'Express', 'description' => 'Air freight 2–3 days.', 'priority' => 'high'],
    ['name' => 'Priority', 'description' => 'Next business day.', 'priority' => 'express'],
];

foreach ($services as $s) {
    db_execute(
        'INSERT INTO services (name, description, is_active, created_at, updated_at)
         VALUES (:name, :description, 1, NOW(), NOW())
         ON DUPLICATE KEY UPDATE name = VALUES(name)',
        [
            ':name' => $s['name'],
            ':description' => $s['description'],
        ]
    );
}

$calc = db_fetch_one('SELECT id FROM calculator WHERE id = 1');
if (!$calc) {
    db_execute('INSERT INTO calculator (id, normal, express, currency) VALUES (1, 0.07, 0.09, "USD")');
} else {
    db_execute('UPDATE calculator SET normal = 0.07, express = 0.09, currency = "USD" WHERE id = 1');
}

echo "Seeder 001 completed.\n";
