<?php
/**
 * Quote calculation API.
 *
 * Accepts GET/POST parameters:
 *   - from: string (origin city/country)
 *   - to: string (destination city/country)
 *   - weight: float (kg)
 *   - length: float (cm)
 *   - width: float (cm)
 *   - height: float (cm)
 *   - speed: string (standard|express|priority)
 *
 * Returns JSON:
 * {
 *   "success": true,
 *   "data": {
 *     "price": "29.40",
 *     "currency": "USD",
 *     "breakdown": { ... }
 *   }
 * }
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../includes/rate-limit.php';

if (!rate_limit('quote_api', 60, 60)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please slow down.', 'errors' => ['Too many requests. Please wait a moment.']]);
    exit;
}

// Only allow GET/POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

// ------------------------------------------------------------------
// Input validation
// ------------------------------------------------------------------
$from    = trim($_REQUEST['from'] ?? '');
$to      = trim($_REQUEST['to'] ?? '');
$weight  = floatval($_REQUEST['weight'] ?? 0);
$length  = floatval($_REQUEST['length'] ?? 0);
$width   = floatval($_REQUEST['width'] ?? 0);
$height  = floatval($_REQUEST['height'] ?? 0);
$speed   = strtolower(trim($_REQUEST['speed'] ?? 'standard'));

$errors = [];

if ($from === '') {
    $errors[] = 'Origin is required.';
}
if ($to === '') {
    $errors[] = 'Destination is required.';
}
if ($weight <= 0 || $weight > 10000) {
    $errors[] = 'Weight must be between 0.1 and 10,000 kg.';
}
if ($length <= 0 || $length > 500) {
    $errors[] = 'Length must be between 1 and 500 cm.';
}
if ($width <= 0 || $width > 500) {
    $errors[] = 'Width must be between 1 and 500 cm.';
}
if ($height <= 0 || $height > 500) {
    $errors[] = 'Height must be between 1 and 500 cm.';
}

$allowedSpeeds = ['standard', 'express', 'priority'];
if (!in_array($speed, $allowedSpeeds, true)) {
    $speed = 'standard';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors),
        'errors' => $errors,
    ]);
    exit;
}

// ------------------------------------------------------------------
// Pricing calculation
// ------------------------------------------------------------------
$volumetric = ($length * $width * $height) / 5000;
$billable = max($weight, $volumetric);

// Pull live rates from DB
$rates = db_fetch_one('SELECT normal, express, currency FROM calculator WHERE id = 1');
$rateNormal  = $rates ? (float)$rates['normal']  : 0.07;
$rateExpress = $rates ? (float)$rates['express'] : 0.09;

$multipliers = [
    'standard' => 1.0,
    'express'  => ($rateExpress / $rateNormal),
    'priority' => 3.2,
];

$mult = $multipliers[$speed] ?? 1.0;
$base = 8 + $billable * $rateNormal;
$price = $base * $mult;

$currency = $rates['currency'] ?? 'USD';

echo json_encode([
    'success' => true,
    'data' => [
        'price'    => number_format($price, 2, '.', ''),
        'currency' => $currency,
        'breakdown' => [
            'from'         => $from,
            'to'           => $to,
            'weight_kg'    => $weight,
            'volume_m3'    => number_format($volumetric, 4, '.', ''),
            'billable_kg'  => number_format($billable, 2, '.', ''),
            'speed'        => $speed,
            'multiplier'   => $mult,
            'base_cost'    => number_format($base, 2, '.', ''),
            'total'        => number_format($price, 2, '.', ''),
        ],
    ],
]);
ob_end_flush();
exit;
