<?php
/**
 * Shared site header — navigation bar.
 *
 * Variables expected from the including page:
 *   $page_title       string  — <title> tag value
 *   $page_description string  — meta description
 *   $canonical        string  — canonical URL path (e.g. "/track.php")
 *   $active_nav       string  — label of the active nav link (e.g. "Track")
 */

// ------------------------------------------------------------------
// Security / CORS headers
// ------------------------------------------------------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS: allow local dev + configurable production origins.
// On shared hosting, set APP_URL in your control panel or .env.
$appUrl  = getenv('APP_URL') ?: '';
$parsed  = $appUrl ? parse_url($appUrl) : [];
$appHost = $parsed['host'] ?? '';

$allowedOrigins = array_filter([
    'http://localhost',
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    $appHost ? ('https://' . $appHost) : '',
    $appHost ? ('http://' . $appHost) : '',
]);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

$page_title       = $page_title       ?? 'American Shipping & Logistics — Global Courier & Freight Services';
$page_description = $page_description ?? 'American Shipping & Logistics moves parcels and freight to 220+ countries. Track shipments, get instant quotes, and book pickups in seconds.';
$canonical        = $canonical        ?? '/';
$active_nav       = $active_nav       ?? '';

$nav_links = [
    ['href' => 'services.php',  'label' => 'Services'],
    ['href' => 'track.php',     'label' => 'Track'],
    ['href' => 'quote.php',     'label' => 'Get a quote'],
    ['href' => 'pricing.php',   'label' => 'Pricing'],
    ['href' => 'about.php',     'label' => 'About'],
    ['href' => 'help.php',      'label' => 'Help'],
    ['href' => 'contact.php',   'label' => 'Contact'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title) ?></title>
    <meta name="description" content="<?= h($page_description) ?>">
    <link rel="canonical" href="<?= h($canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= h($page_title) ?>">
    <meta property="og:description" content="<?= h($page_description) ?>">
    <meta property="og:type"        content="website">

    <!-- Tailwind CDN (matches the React build's utility approach) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Inter', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#FFCC00',
                            foreground: '#1a1a1a',
                        },
                        accent: {
                            DEFAULT: '#D62B2B',
                            foreground: '#ffffff',
                        },
                        surface: '#F5F5F5',
                    },
                },
            },
        };
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">

    <style>
        /* Base CSS variables matching the React design system */
        :root {
            --brand: #FFCC00;
            --brand-foreground: #1a1a1a;
            --accent: #D62B2B;
            --accent-foreground: #ffffff;
            --background: #ffffff;
            --foreground: #111111;
            --muted-foreground: #6b7280;
            --border: #e5e7eb;
            --surface: #F5F5F5;
        }
        body { font-family: 'Inter', system-ui, sans-serif; color: var(--foreground); background: var(--background); }
        .container-x { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }
        .font-display { font-family: 'Inter', system-ui, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .bg-brand { background-color: var(--brand); }
        .text-brand { color: #b8940a; }
        .text-brand-foreground { color: var(--brand-foreground); }
        .bg-accent { background-color: var(--accent); }
        .text-accent { color: var(--accent); }
        .text-accent-foreground { color: var(--accent-foreground); }
        .bg-surface { background-color: var(--surface); }
        .border-brand { border-color: var(--brand); }
        .border-accent { border-color: var(--accent); }
        .text-muted { color: var(--muted-foreground); }
        .grid-lines {
            background-image: linear-gradient(rgba(0,0,0,.06) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0,0,0,.06) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        /* Mobile nav transition */
        #mobile-menu { transition: max-height .25s ease; overflow: hidden; max-height: 0; }
        #mobile-menu.open { max-height: 400px; }
    </style>
</head>
<body class="antialiased">

<!-- ====== HEADER ====== -->
<header class="sticky top-0 z-40 border-b border-gray-200 bg-white/80 backdrop-blur-xl">
    <div class="container-x flex h-16 items-center justify-between gap-6">

        <!-- Logo -->
        <a href="index.php" class="flex items-center gap-2 font-display text-lg font-bold tracking-tight">
            <span class="grid h-8 w-8 place-items-center rounded bg-brand text-accent">
                <!-- Package icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m7.5 4.27 9 5.15"/>
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/>
                    <path d="M12 22V12"/>
                </svg>
            </span>
            <span>American Shipping &amp; Logistics</span>
        </a>

        <!-- Desktop nav -->
        <nav class="hidden items-center gap-1 md:flex">
            <?php foreach ($nav_links as $link): ?>
                <?php $is_active = ($active_nav === $link['label']); ?>
                <a href="<?= h($link['href']) ?>"
                   class="rounded-md px-3 py-2 text-sm transition hover:bg-gray-100
                          <?= $is_active ? 'text-gray-900 bg-gray-100 font-medium' : 'text-gray-500 hover:text-gray-900' ?>">
                    <?= h($link['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Desktop CTA -->
        <div class="hidden items-center gap-2 md:flex">
            <a href="track.php"
               class="rounded bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-white transition hover:opacity-90">
                Track
            </a>
        </div>

        <!-- Mobile hamburger -->
        <button id="hamburger" onclick="toggleMobileMenu()"
                class="grid h-10 w-10 place-items-center rounded-md md:hidden"
                aria-label="Open menu" aria-expanded="false">
            <svg id="icon-menu" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
            </svg>
            <svg id="icon-close" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="hidden">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
            </svg>
        </button>
    </div>

    <!-- Mobile nav dropdown -->
    <div id="mobile-menu" class="border-t border-gray-200 bg-white md:hidden">
        <nav class="container-x flex flex-col gap-1 py-3">
            <?php foreach ($nav_links as $link): ?>
                <?php $is_active = ($active_nav === $link['label']); ?>
                <a href="<?= h($link['href']) ?>"
                   class="rounded-md px-3 py-2 text-sm transition hover:bg-gray-100
                          <?= $is_active ? 'text-gray-900 bg-gray-100 font-medium' : 'text-gray-500 hover:text-gray-900' ?>">
                    <?= h($link['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
<!-- ====== END HEADER ====== -->

<script>
function toggleMobileMenu() {
    const menu   = document.getElementById('mobile-menu');
    const btn    = document.getElementById('hamburger');
    const iconM  = document.getElementById('icon-menu');
    const iconC  = document.getElementById('icon-close');
    const isOpen = menu.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen);
    iconM.classList.toggle('hidden', isOpen);
    iconC.classList.toggle('hidden', !isOpen);
}
</script>
