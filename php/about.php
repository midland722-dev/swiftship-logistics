<?php
$page_title       = 'About — American Shipping & Logistics';
$page_description = 'American Shipping & Logistics connects people and businesses across 220+ countries with reliable logistics, courier, and freight services.';
$canonical        = '/about.php';
$active_nav       = 'About';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-16 pb-14 md:pt-24">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">About Us</p>
    <h1 class="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
        Excellence. Simply delivered.
    </h1>
    <p class="mt-5 max-w-2xl text-lg text-muted-foreground">
        For over five decades American Shipping &amp; Logistics has connected people, businesses and communities. From
        the first international courier flight to today's AI-optimized global routing, we
        keep supply chains moving — reliably, sustainably, everywhere.
    </p>
</section>

<section class="container-x grid gap-4 pb-16 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
        <div class="mt-4 font-display text-3xl font-bold">220+</div>
        <div class="text-sm text-muted-foreground">Countries served</div>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <div class="mt-4 font-display text-3xl font-bold">128k</div>
        <div class="text-sm text-muted-foreground">Team members</div>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
        <div class="mt-4 font-display text-3xl font-bold">1969</div>
        <div class="text-sm text-muted-foreground">Founded</div>
    </div>
    <div class="rounded-2xl border border-border bg-surface/60 p-6">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-brand"><path d="M4.5 16.5c-1.5 1.26-2 2.5-2 2.5s.74-2.5 3.5-2.5c3 0 4.5 2 6 2s3-2 6-2 3.5 2.5 3.5 2.5-.5-2.5-2-2.5"/><path d="M12 15V3"/><path d="M8 7l4-4 4 4"/></svg>
        <div class="mt-4 font-display text-3xl font-bold">1.9B</div>
        <div class="text-sm text-muted-foreground">Shipments per year</div>
    </div>
</section>

<section class="container-x grid gap-10 pb-24 md:grid-cols-2">
    <div>
        <h2 class="font-display text-3xl font-bold">Our purpose</h2>
        <p class="mt-4 text-muted-foreground">
            Connecting people. Improving lives. We believe global trade is a force for good —
            and that logistics done well makes the world smaller, fairer, and more resilient.
        </p>
    </div>
    <div>
        <h2 class="font-display text-3xl font-bold">Our promise</h2>
        <p class="mt-4 text-muted-foreground">
            On time. In full. Every time. We measure ourselves against the highest standard in
            the industry, and publish our on-time performance every quarter.
        </p>
    </div>
</section>

<section class="container-x pb-24">
    <div class="rounded-3xl border border-border bg-surface/60 p-10 text-center md:p-16">
        <h2 class="font-display text-3xl font-bold md:text-4xl">Work with our team</h2>
        <p class="mx-auto mt-3 max-w-xl text-muted-foreground">
            From same-day couriers to multi-modal freight, our specialists design the right
            solution for your business.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="contact.php" class="rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Contact sales</a>
            <a href="careers.php" class="rounded-sm border border-border px-5 py-3 text-sm font-semibold hover:bg-surface">See open roles</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

