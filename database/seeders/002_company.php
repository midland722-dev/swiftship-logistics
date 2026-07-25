<?php
/**
 * Seeder: company
 */

require_once __DIR__ . '/../config/db.php';

$company = db_fetch_one('SELECT id FROM company WHERE id = 1');
if (!$company) {
    db_execute(
        'INSERT INTO company (id, cname, bemail, caddress, phone, website, created_at, updated_at)
         VALUES (1, "American Shipping & Logistics", "info@ascl-logistics.com", "123 Logistics Way", "+1 (215) 815-9791", "https://ascl-logistics.com", NOW(), NOW())'
    );
} else {
    db_execute(
        'UPDATE company SET cname = "American Shipping & Logistics", bemail = "info@ascl-logistics.com", phone = "+1 (215) 815-9791", updated_at = NOW() WHERE id = 1'
    );
}

echo "Seeder 002 completed.\n";
