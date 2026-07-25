<?php
$page_title       = 'Help center — American Shipping & Logistics';
$page_description = 'Answers to common questions about tracking, shipping, billing, and claims.';
$canonical        = '/help.php';
$active_nav       = 'Help';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-16 pb-14 md:pt-24">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Help center</p>
    <h1 class="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
        We're here to help.
    </h1>
    <p class="mt-5 max-w-2xl text-lg text-muted-foreground">
        Find quick answers below, or reach our support team 24/7.
    </p>
</section>

<section class="container-x grid gap-4 pb-14 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
        <h2 class="mt-4 font-semibold">Tracking</h2>
        <p class="mt-2 text-sm text-muted-foreground">Enter your tracking number on the Track page. Updates appear within minutes of each network scan.</p>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
        <h2 class="mt-4 font-semibold">Shipping</h2>
        <p class="mt-2 text-sm text-muted-foreground">Book pickups online, drop off at 4,500+ locations, or schedule recurring collections for your business.</p>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
        <h2 class="mt-4 font-semibold">Billing</h2>
        <p class="mt-2 text-sm text-muted-foreground">Invoices are issued weekly for business accounts. Log in to your account portal to download PDFs.</p>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <h2 class="mt-4 font-semibold">Claims & insurance</h2>
        <p class="mt-2 text-sm text-muted-foreground">Report loss or damage within 30 days. Standard insurance covers $100; extended cover up to $2,000.</p>
    </div>
</section>

<section class="container-x pb-24">
    <h2 class="font-display text-2xl font-bold">Frequently asked questions</h2>
    <div class="mt-6 divide-y divide-border rounded-2xl border border-border bg-surface/60">
        <details class="group p-5">
            <summary class="cursor-pointer list-none font-semibold marker:hidden">
                <span class="mr-2 text-brand">+</span>How do I track a shipment?
            </summary>
            <p class="mt-3 pl-6 text-sm text-muted-foreground">Use the tracking number from your booking confirmation on our Track page. Live status updates every few minutes.</p>
        </details>
        <details class="group p-5">
            <summary class="cursor-pointer list-none font-semibold marker:hidden">
                <span class="mr-2 text-brand">+</span>What if my parcel is delayed?
            </summary>
            <p class="mt-3 pl-6 text-sm text-muted-foreground">Delays over 24 hours past ETA are eligible for our on-time refund guarantee on Priority and Express services.</p>
        </details>
        <details class="group p-5">
            <summary class="cursor-pointer list-none font-semibold marker:hidden">
                <span class="mr-2 text-brand">+</span>Can I change the delivery address?
            </summary>
            <p class="mt-3 pl-6 text-sm text-muted-foreground">Yes — use the tracking page to request a redirect until the last-mile courier collects the parcel.</p>
        </details>
        <details class="group p-5">
            <summary class="cursor-pointer list-none font-semibold marker:hidden">
                <span class="mr-2 text-brand">+</span>How is shipping cost calculated?
            </summary>
            <p class="mt-3 pl-6 text-sm text-muted-foreground">The higher of actual weight or volumetric weight (L × W × H ÷ 5000), multiplied by service speed and zone.</p>
        </details>
    </div>

    <div class="mt-10 rounded-2xl border border-border bg-surface/60 p-6 text-center">
        <p class="text-sm text-muted-foreground">Still stuck?</p>
        <a href="contact.php" class="mt-3 inline-block rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Contact support</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

