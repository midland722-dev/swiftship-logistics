<?php
$page_title       = 'Sustainability — American Shipping & Logistics';
$page_description = 'American Shipping & Logistics sustainability report: carbon reduction, electric fleet, green fuels, and ESG targets.';
$canonical        = '/sustainability.php';
$active_nav       = '';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-16 pb-14 md:pt-24">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Sustainability</p>
    <h1 class="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
        Delivering greener logistics.
    </h1>
    <p class="mt-5 max-w-2xl text-lg text-muted-foreground">
        We are committed to net-zero emissions by 2050, with science-based targets for 2030.
    </p>
</section>

<section class="container-x grid gap-4 pb-16 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <div class="font-display text-3xl font-bold">-42%</div>
        <div class="mt-1 text-sm text-muted-foreground">Scope 1 & 2 emissions reduced since 2019</div>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <div class="font-display text-3xl font-bold">18%</div>
        <div class="mt-1 text-sm text-muted-foreground">Electric vehicles in last-mile fleet</div>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <div class="font-display text-3xl font-bold">100%</div>
        <div class="mt-1 text-sm text-muted-foreground">New hubs built to BREEAM Excellent</div>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <div class="font-display text-3xl font-bold">2050</div>
        <div class="mt-1 text-sm text-muted-foreground">Net-zero target year</div>
    </div>
</section>

<section class="container-x grid gap-10 pb-24 md:grid-cols-2">
    <div>
        <h2 class="font-display text-3xl font-bold">Green fuels & fleet</h2>
        <p class="mt-4 text-muted-foreground">
            We are transitioning to HVO, electric, and hydrogen-powered vehicles. By 2030, 50% of our
            long-haul fleet will run on sustainable fuels.
        </p>
    </div>
    <div>
        <h2 class="font-display text-3xl font-bold">Circular packaging</h2>
        <p class="mt-4 text-muted-foreground">
            Our reusable packaging program has diverted 12 million single-use parcels from landfill.
            Join GoGreen Plus to offset remaining emissions.
        </p>
    </div>
</section>

<section class="container-x pb-24">
    <div class="rounded-3xl border border-brand/40 bg-gradient-to-b from-brand/15 to-surface p-10 text-center md:p-16">
        <h2 class="font-display text-3xl font-bold md:text-4xl">Ship greener with American Shipping &amp; Logistics</h2>
        <p class="mx-auto mt-3 max-w-xl text-muted-foreground">
            Choose GoGreen Plus at checkout and we'll offset your shipment's carbon footprint.
        </p>
        <a href="quote.php" class="mt-6 inline-block rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Get a quote</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

