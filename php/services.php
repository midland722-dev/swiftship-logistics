<?php
$page_title       = 'Services — American Shipping & Logistics';
$page_description = 'Express shipping, freight, eCommerce logistics, and supply chain solutions from American Shipping & Logistics.';
$canonical        = '/services.php';
$active_nav       = 'Services';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x pb-14 pt-16 md:pt-24">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Services</p>
    <h1 class="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
        Whatever you're shipping, we route it.
    </h1>
    <p class="mt-5 max-w-2xl text-lg text-muted-foreground">
        Eight service lines built on one global network — from same-day metro drops to transcontinental ocean freight.
    </p>
</section>

<section class="container-x grid gap-4 pb-24 md:grid-cols-2 lg:grid-cols-4">
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.4-.1.9.3 1.1L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.9.5 1.3.2l.5-.3c.4-.2.6-.7.5-1.1z"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">Express Shipping</h2>
        <p class="mt-2 text-sm text-muted-foreground">Time-definite international delivery with next-business-day options to 60+ major hubs.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Next-day international</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Time-definite delivery</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Signature required</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Full insurance</li>
        </ul>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">Freight Services</h2>
        <p class="mt-2 text-sm text-muted-foreground">Road freight, LTL, and FTL across North America, Europe, and Asia with real-time visibility.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>LTL & FTL</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Temperature-controlled</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Hazmat certified</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Cross-border expertise</li>
        </ul>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">eCommerce Logistics</h2>
        <p class="mt-2 text-sm text-muted-foreground">End-to-end fulfilment for online stores — pick, pack, ship, and returns.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Shopify & WooCommerce</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Returns management</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Branded packaging</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Same-day dispatch</li>
        </ul>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">Supply Chain Solutions</h2>
        <p class="mt-2 text-sm text-muted-foreground">Warehousing, distribution, and consulting for global supply chain optimization.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>50+ warehouses</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Inventory management</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>3PL & 4PL</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Custom KPIs</li>
        </ul>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 10V2"/><path d="m8 6 4-4 4 4"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">Ocean & Air Freight</h2>
        <p class="mt-2 text-sm text-muted-foreground">Container shipping and air cargo with customs clearance included.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>FCL & LCL</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Air charter available</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Customs brokerage</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Door-to-door</li>
        </ul>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21h20"/><path d="M5 21V7l8-4 8 4v14"/><path d="M17 21v-8.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5V21"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">Industrial Logistics</h2>
        <p class="mt-2 text-sm text-muted-foreground">Heavy, oversized, and project cargo handled by specialised teams.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Project cargo</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Oversized freight</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Rigging & installation</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Route surveys</li>
        </ul>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">Warehousing</h2>
        <p class="mt-2 text-sm text-muted-foreground">Flexible storage with real-time inventory visibility.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Bonded warehousing</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Pick & pack</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Kitting & assembly</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>WMS integration</li>
        </ul>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
        <div class="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <h2 class="mt-5 text-lg font-semibold">Parcel & Same-Day</h2>
        <p class="mt-2 text-sm text-muted-foreground">On-demand parcel delivery within metro areas — under 4 hours.</p>
        <ul class="mt-4 space-y-1.5 text-xs text-muted-foreground">
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Under 4-hour delivery</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Live courier tracking</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Photo proof of delivery</li>
            <li class="flex items-center gap-2"><span class="h-1 w-1 rounded-full bg-brand"></span>Metro coverage</li>
        </ul>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

