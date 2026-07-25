<?php
/**
 * Customer tracking page.
 *
 * Shows:
 *   - Tracking number search
 *   - Current status and location
 *   - Progress bar
 *   - Event timeline
 *   - ETA placeholder
 *
 * Does NOT expose customer PII (names, emails, phones, addresses).
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/rate-limit.php';

$page_title       = 'Track shipment — American Shipping & Logistics';
$page_description = 'Live tracking for American Shipping & Logistics parcels and freight worldwide.';
$canonical        = '/track.php';
$active_nav       = 'Track';
require_once __DIR__ . '/includes/header.php';

$tracking_id = trim($_GET['id'] ?? '');
$error = '';
$shipment = null;
$history = [];
$source = 'shipments';

try {
    if ($tracking_id !== '') {
        if (!preg_match('/^[A-Za-z0-9\-_]{3,60}$/', $tracking_id)) {
            $error = 'Invalid tracking number format. Use 3-60 alphanumeric characters, hyphens, or underscores.';
        } elseif (!rate_limit('tracking_page', 60, 60)) {
            $error = 'Too many requests. Please wait a moment and try again.';
        } else {
            $shipment = db_fetch_one(
                'SELECT id, tracking_number, status, service_type, origin_city, origin_country, destination_city, destination_country, total_weight, currency, estimated_delivery, created_at
                 FROM shipments
                 WHERE tracking_number = :tn
                 LIMIT 1',
                [':tn' => $tracking_id]
            );

            if ($shipment) {
                $history = db_fetch_all(
                    'SELECT status, location, transit_location, description, customs_procedure, event_timestamp
                     FROM tracking_history
                     WHERE tracking_number = :tn
                     ORDER BY event_timestamp ASC
                     LIMIT 50',
                    [':tn' => $tracking_id]
                );
            } else {
                $source = 'legacy';
                $legacy = db_fetch_one(
                    'SELECT cid, cons_no, status, invice_no, pick_time, book_date, ship_name, rev_name, r_phone, s_add, r_add, type, comments, freight, shipping_subtotal, book_mode, declarate
                     FROM courier
                     WHERE cons_no = :tn
                     LIMIT 1',
                    [':tn' => $tracking_id]
                );

                if ($legacy) {
                    $shipment = [
                        'id' => (int)$legacy['cid'],
                        'tracking_number' => $legacy['cons_no'],
                        'status' => $legacy['status'],
                        'service_type' => $legacy['type'] ?? 'standard',
                        'origin_city' => $legacy['invice_no'] ?? '',
                        'origin_country' => '',
                        'destination_city' => $legacy['pick_time'] ?? '',
                        'destination_country' => '',
                        'total_weight' => null,
                        'currency' => 'USD',
                        'estimated_delivery' => $legacy['book_date'] ?? null,
                        'created_at' => $legacy['book_date'] ?? date('Y-m-d H:i:s'),
                    ];

                    $history = db_fetch_all(
                        'SELECT status, location, transit_location, description, customs_procedure, event_timestamp
                         FROM tracking_logs
                         WHERE tracking_number = :tn AND is_public = 1
                         ORDER BY event_timestamp ASC
                         LIMIT 50',
                        [':tn' => $tracking_id]
                    );

                    if (empty($history)) {
                        $history = db_fetch_all(
                            'SELECT status, location, "" AS transit_location, description, "" AS customs_procedure, date AS event_timestamp
                             FROM courier_track
                             WHERE cons_no = :tn
                             ORDER BY date ASC
                             LIMIT 50',
                            [':tn' => $tracking_id]
                        );
                    }
                }

                if (!$shipment) {
                    $online = db_fetch_one(
                        'SELECT cid, cons_no, status, type, ship_name, rev_name, s_add, r_add, date, note
                         FROM courier_online
                         WHERE cons_no = :tn
                         LIMIT 1',
                        [':tn' => $tracking_id]
                    );

                    if ($online) {
                        $shipment = [
                            'id' => (int)$online['cid'],
                            'tracking_number' => $online['cons_no'],
                            'status' => $online['status'],
                            'service_type' => $online['type'] ?? 'standard',
                            'origin_city' => $online['ship_name'] ?? '',
                            'origin_country' => '',
                            'destination_city' => $online['rev_name'] ?? '',
                            'destination_country' => '',
                            'total_weight' => null,
                            'currency' => 'USD',
                            'estimated_delivery' => $online['date'] ?? null,
                            'created_at' => $online['date'] ?? date('Y-m-d H:i:s'),
                        ];
                        $history = [];
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    $error = 'Unable to load tracking information. Please try again later.';
    if (getenv('APP_DEBUG') === 'true') {
        $error .= ' (' . $e->getMessage() . ')';
    }
}

$statusLabels = [
    'pending'              => 'Pending',
    'processing'           => 'Processing',
    'picked_up'            => 'Picked Up',
    'at_warehouse'         => 'At Warehouse',
    'in_transit'           => 'In Transit',
    'at_hub'               => 'At Hub',
    'customs_inspection'   => 'Customs Inspection',
    'customs_clearance'    => 'Customs Clearance',
    'customs_delayed'      => 'Customs Delayed',
    'held'                 => 'On Hold',
    'out_for_delivery'     => 'Out for Delivery',
    'delivered'            => 'Delivered',
    'returned'             => 'Returned',
    'cancelled'            => 'Cancelled',
];

$progressMap = [
    'pending'            => 5,
    'processing'         => 10,
    'picked_up'          => 20,
    'at_warehouse'       => 30,
    'in_transit'         => 50,
    'at_hub'             => 60,
    'customs_inspection' => 55,
    'customs_clearance'  => 65,
    'out_for_delivery'   => 80,
    'delivered'          => 100,
    'returned'           => 100,
    'cancelled'          => 100,
];

function statusColor(string $status): string {
    $map = [
        'pending'            => '#6b7280',
        'processing'         => '#3b82f6',
        'picked_up'          => '#8b5cf6',
        'in_transit'         => '#f59e0b',
        'at_hub'             => '#f59e0b',
        'out_for_delivery'   => '#10b981',
        'delivered'          => '#059669',
        'customs_inspection' => '#ef4444',
        'customs_clearance'  => '#f59e0b',
        'customs_delayed'    => '#ef4444',
        'held'               => '#ef4444',
        'returned'           => '#6b7280',
        'cancelled'          => '#6b7280',
    ];
    return $map[$status] ?? '#6b7280';
}

function formatDateTime(?string $value): string {
    if (!$value) return '—';
    $d = new DateTime($value);
    return $d->format('M j, Y g:i A');
}
?>

<section class="container-x py-10 md:py-14">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Live tracking</p>
    <h1 class="mt-2 font-display text-3xl font-bold md:text-4xl">Where's my shipment?</h1>
    <p class="mt-3 max-w-2xl text-muted-foreground">
        Track your American Shipping & Logistics shipment in real time. Enter your tracking number
        to see the latest status, transit locations, customs updates, and estimated delivery.
        We update tracking events as your package moves through our global network.
    </p>

    <!-- Search form -->
    <form method="get" class="mt-8 flex max-w-2xl flex-col gap-3 rounded-2xl border border-border bg-surface/60 p-4 sm:flex-row">
        <input
            type="text"
            name="id"
            value="<?= h($tracking_id) ?>"
            placeholder="Enter tracking number, e.g. ASC827950684213"
            required
            autocomplete="off"
            class="flex-1 rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none placeholder:text-muted-foreground/70 focus:border-brand"
        />
        <button type="submit" class="rounded bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">
            Track
        </button>
    </form>

    <?php if ($error): ?>
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($tracking_id !== '' && !$error && !$shipment): ?>
        <div class="mt-8 rounded-lg border border-border bg-surface/60 p-6 text-sm text-muted-foreground">
            No shipment found for tracking number <strong class="text-foreground"><?= h($tracking_id) ?></strong>.
        </div>
    <?php endif; ?>

    <div class="mt-10 grid gap-6 md:grid-cols-3">
        <div class="rounded-2xl border border-border bg-surface/60 p-6">
            <h3 class="font-semibold">How to track</h3>
            <p class="mt-2 text-sm text-muted-foreground">
                Enter your tracking number exactly as shown on your receipt or shipping confirmation email.
                Tracking numbers usually start with <span class="font-mono text-xs">ASC</span> followed by 12 digits.
            </p>
        </div>
        <div class="rounded-2xl border border-border bg-surface/60 p-6">
            <h3 class="font-semibold">Tracking updates</h3>
            <p class="mt-2 text-sm text-muted-foreground">
                Status updates appear here automatically. If a shipment was just created or is between
                facilities, there may be a short delay before the next event appears.
            </p>
        </div>
        <div class="rounded-2xl border border-border bg-surface/60 p-6">
            <h3 class="font-semibold">Need help?</h3>
            <p class="mt-2 text-sm text-muted-foreground">
                Can't find your tracking number or think there's an error? Contact our support team at
                <a href="mailto:<?= h($company['email'] ?? 'info@ascl-logistics.com') ?>" class="text-brand underline"><?= h($company['email'] ?? 'info@ascl-logistics.com') ?></a>
                or call <a href="tel:+12158159791" class="text-brand underline">+1 (215) 815-9791</a>.
            </p>
        </div>
    </div>

    <?php if ($shipment): ?>
        <?php
            $status = $shipment['status'];
            $label = $statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status));
            $color = statusColor($status);
            $progress = $progressMap[$status] ?? 40;
            $location = $shipment['destination_city'] ?? '—';
            $eta = $shipment['estimated_delivery'] ? formatDateTime($shipment['estimated_delivery']) : 'Not yet estimated';
        ?>
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
            <!-- Main tracking card -->
            <div class="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
                <!-- Status header -->
                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-4">
                    <div class="min-w-0">
                        <div class="font-mono text-xs text-muted-foreground"><?= h($shipment['tracking_number']) ?></div>
                        <h2 class="mt-1 truncate font-display text-2xl font-bold"><?= h($label) ?></h2>
                        <div class="mt-1 text-sm text-muted-foreground">
                            <?php if ($shipment['origin_city'] && $shipment['destination_city']): ?>
                                <?= h($shipment['origin_city']) ?>, <?= h($shipment['origin_country'] ?? '') ?>
                                &rarr;
                                <?= h($shipment['destination_city']) ?>, <?= h($shipment['destination_country'] ?? '') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold text-white" style="background: <?= h($color) ?>">
                        <?= h($label) ?>
                    </span>
                </div>

                <!-- Progress bar -->
                <div class="mt-6">
                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                        <span>Progress</span>
                        <span class="font-mono text-brand"><?= (int)$progress ?>%</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-background">
                        <div class="h-full rounded-full bg-brand transition-all" style="width: <?= (int)$progress ?>%"></div>
                    </div>
                </div>

                <!-- Timeline -->
                <?php if (!empty($history)): ?>
                    <ol class="mt-8 space-y-4">
                        <?php foreach ($history as $i => $ev): ?>
                            <?php
                                $isLast = ($i === count($history) - 1);
                                $isDone = !$isLast;
                                $d = new DateTime($ev['event_timestamp']);
                                $dateStr = $d->format('M j, Y');
                                $timeStr = $d->format('g:i A');
                                $evLabel = $statusLabels[$ev['status']] ?? ucwords(str_replace('_', ' ', $ev['status']));
                                $evColor = statusColor($ev['status']);
                                $loc = '';
                                if (!empty($ev['transit_location'])) {
                                    $loc = $ev['transit_location'];
                                } elseif (!empty($ev['location'])) {
                                    $loc = $ev['location'];
                                }
                            ?>
                            <li class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full border text-xs font-bold text-white" style="border-color: <?= h($evColor) ?>; background: <?= h($evColor) ?>">
                                        <?= $isDone ? '✓' : '' ?>
                                    </div>
                                    <?php if (!$isLast): ?>
                                        <div class="mt-1 h-8 w-px bg-border"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0 flex-1 pb-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-medium" style="color: <?= h($evColor) ?>"><?= h($evLabel) ?></span>
                                        <?php if ($loc): ?>
                                            <span class="text-xs text-muted-foreground">· <?= h($loc) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($ev['description'])): ?>
                                        <div class="mt-0.5 text-xs text-muted-foreground"><?= h($ev['description']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($ev['customs_procedure'])): ?>
                                        <div class="mt-0.5 text-xs text-muted-foreground">Customs: <?= h($ev['customs_procedure']) ?></div>
                                    <?php endif; ?>
                                    <div class="mt-0.5 text-xs text-muted-foreground"><?= h($dateStr) ?> · <?= h($timeStr) ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php else: ?>
                    <div class="mt-8 rounded-lg border border-border bg-background/60 p-6 text-sm text-muted-foreground">
                        No tracking events yet. Check back later for updates.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="space-y-4">
                <!-- Status card -->
                <div class="rounded-2xl border border-border bg-surface/60 p-6">
                    <h3 class="font-semibold">Status</h3>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="h-3 w-3 rounded-full" style="background: <?= h($color) ?>"></span>
                        <span class="text-sm font-medium"><?= h($label) ?></span>
                    </div>
                </div>

                <!-- Location card -->
                <div class="rounded-2xl border border-border bg-surface/60 p-6">
                    <h3 class="font-semibold">Current Location</h3>
                    <div class="mt-3 text-sm text-muted-foreground">
                        <?= h($location) ?>
                    </div>
                </div>

                <!-- ETA card -->
                <div class="rounded-2xl border border-border bg-surface/60 p-6">
                    <h3 class="font-semibold">Estimated Delivery</h3>
                    <div class="mt-3 text-sm text-muted-foreground">
                        <?= h($eta) ?>
                    </div>
                </div>

                <!-- Service card -->
                <div class="rounded-2xl border border-border bg-surface/60 p-6">
                    <h3 class="font-semibold">Service</h3>
                    <div class="mt-3 text-sm text-muted-foreground">
                        <?= h($shipment['service_type'] ?? '—') ?>
                    </div>
                </div>
            </aside>
        </div>
    <?php endif; ?>

    <section class="container-x pb-16 md:pb-24">
        <div class="rounded-3xl border border-border bg-surface/60 p-8 md:p-12">
            <h2 class="font-display text-2xl font-bold md:text-3xl">Tracking support</h2>
            <p class="mt-3 max-w-3xl text-muted-foreground">
                American Shipping & Logistics provides live tracking for parcels, freight, and express shipments
                across 220+ countries. If your shipment was booked through our online portal, agent, or customer
                support, you should have received a tracking number by email or SMS.
            </p>
            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div>
                    <h3 class="font-semibold">Where is my tracking number?</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Your tracking number appears on your shipping label, receipt, and confirmation email.
                        If you booked through an American Shipping & Logistics agent, ask them for a copy of
                        your receipt or request your tracking number via the contact form below.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold">What do the statuses mean?</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Statuses follow your shipment from pickup through delivery. <span class="text-foreground font-medium">Pending</span> means the shipment
                        has been created but not yet collected. <span class="text-foreground font-medium">In Transit</span> means it is moving between
                        facilities. <span class="text-foreground font-medium">Out for Delivery</span> means it is on its final leg.
                        <span class="text-foreground font-medium">Delivered</span> confirms receipt.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold">Missing updates or wrong status?</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Tracking scans can take up to 24 hours to appear, especially on weekends and holidays.
                        If a status looks incorrect or your shipment has not moved for an unusual length of time,
                        contact us with your tracking number and we will investigate.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold">Customs and international delays</h3>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Cross-border shipments may show <span class="text-foreground font-medium">Customs Inspection</span> or
                        <span class="text-foreground font-medium">Customs Clearance</span> while authorities review the package.
                        These steps are normal and can add 1–3 business days depending on the destination country.
                    </p>
                </div>
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="/contact" class="rounded bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Contact support</a>
                <a href="/" class="rounded border border-border px-5 py-3 text-sm font-semibold hover:bg-surface">Back to home</a>
            </div>
        </div>
    </section>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
