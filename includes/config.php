<?php
/**
 * Load .env file if present (simple parser, no composer dependency needed).
 */
$envFile = __DIR__ . '/../.env';
$envLoaded = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        $_ENV[$key] = $value;
        putenv("$key=$value");
        $envLoaded[$key] = $value;
    }
}

define('SITE_NAME', 'Americans Shipping & Courier Logistics');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') === '1' || getenv('APP_DEBUG') === 'true');
$site_name = "Americans Shipping & Courier Logistics";
$site_name_short = "AS&CL";
$site_title = "Americans Shipping & Courier Logistics - Global Courier & Shipping Solutions";

// Performance: CDN base URL (set in production, leave empty for local)
define('CDN_BASE_URL', getenv('CDN_BASE_URL') ?: '');

require_once __DIR__ . '/../error_log.php';
require_once __DIR__ . '/error_handler.php';

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', '0');
}

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$google_translate_api_key = getenv('GOOGLE_TRANSLATE_API_KEY') ?: '';

$nav_pages = [
    'index' => ['label' => 'nav.home', 'file' => 'index.php'],
    'about' => ['label' => 'nav.about', 'file' => 'about.php'],
    'services' => ['label' => 'nav.services', 'file' => 'service.php'],
    'contact' => ['label' => 'nav.contact', 'file' => 'contact.php'],
];

$nav_dropdowns = [
    'solutions' => [
        'label' => 'Solutions',
        'pages' => [
            ['label' => 'Air Freight', 'file' => 'service.php'],
            ['label' => 'Ocean Freight', 'file' => 'service.php'],
            ['label' => 'Road Transport', 'file' => 'service.php'],
            ['label' => 'Warehousing', 'file' => 'service.php'],
            ['label' => 'Customs Clearance', 'file' => 'service.php'],
        ]
    ],
    'resources' => [
        'label' => 'Resources',
        'pages' => [
            ['label' => 'About Us', 'file' => 'about.php'],
            ['label' => 'Our Team', 'file' => 'team.php'],
            ['label' => 'Testimonials', 'file' => 'testimonial.php'],
            ['label' => 'FAQ', 'file' => 'contact.php' ],
        ]
    ]
];

$deprixa_nav = [
    'panel-customer' => ['label' => 'Customer Panel', 'file' => 'deprixa/panel-customer.php'],
    'booking' => ['label' => 'Book Shipment', 'file' => 'deprixa/customer-booking.php'],
    'my-shipments' => ['label' => 'My Shipments', 'file' => 'deprixa/customer-shipments.php'],
    'payments' => ['label' => 'Payments', 'file' => 'deprixa/customer-payments.php'],
    'profile' => ['label' => 'Profile', 'file' => 'deprixa/customer-profile.php'],
];

$dropdown_pages = [
    ['key' => 'team', 'label' => 'Our Team', 'file' => 'team.php'],
    ['key' => 'testimonial', 'label' => 'Testimonials', 'file' => 'testimonial.php']
];

$footer_services = [
    'Express Delivery' => 'service.php',
    'International Shipping' => 'service.php',
    'Air Freight' => 'service.php',
    'Sea Freight' => 'service.php',
    'Road Transport' => 'service.php',
    'Warehousing' => 'service.php',
    'Customs Clearance' => 'service.php',
    'E-commerce Fulfillment' => 'service.php'
];

$footer_links = [
    'About Us' => 'about.php',
    'Contact Us' => 'contact.php',
    'Our Services' => 'service.php',
    'Track Shipment' => 'track.php',
    'Get a Quote' => 'deprixa/customer-booking.php',
    'Admin Panel' => 'deprixa/login.php',
    'Terms & Conditions' => '#',
    'Privacy Policy' => '#',
    'Support' => '#'
];

function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'delivered':
            return 'success';
        case 'in_transit':
        case 'picked_up':
        case 'out_for_delivery':
            return 'primary';
        case 'pending':
        case 'processing':
            return 'warning';
        case 'cancelled':
        case 'returned':
            return 'danger';
        case 'held':
        case 'customs_hold':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getLogger() {
    static $logger = null;
    if ($logger === null) {
        require_once __DIR__ . '/Logger.php';
        $logger = new Logger();
    }
    return $logger;
}
