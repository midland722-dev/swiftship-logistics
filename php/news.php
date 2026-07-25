<?php
$page_title       = 'Newsroom — American Shipping & Logistics';
$page_description = 'Latest press releases, network updates, and service bulletins from American Shipping & Logistics.';
$canonical        = '/news.php';
$active_nav       = '';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-16 pb-14 md:pt-24">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Newsroom</p>
    <h1 class="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
        The latest from American Shipping &amp; Logistics.
    </h1>
</section>

<section class="container-x pb-24">
    <div class="divide-y divide-border rounded-2xl border border-border bg-surface/60">
        <article class="flex flex-col gap-3 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        Jan 12, 2026
                    </span>
                    <span class="rounded-full bg-brand/15 px-2 py-0.5 font-semibold text-brand">Network</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold">American Shipping & Logistics opens new automated hub in the USA</h2>
            </div>
            <a href="contact.php" class="text-xs font-bold uppercase tracking-wider text-brand hover:underline">Read more →</a>
        </article>
        <article class="flex flex-col gap-3 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        Dec 04, 2025
                    </span>
                    <span class="rounded-full bg-brand/15 px-2 py-0.5 font-semibold text-brand">Sustainability</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold">1,200 additional electric vans deployed across US cities</h2>
            </div>
            <a href="contact.php" class="text-xs font-bold uppercase tracking-wider text-brand hover:underline">Read more →</a>
        </article>
        <article class="flex flex-col gap-3 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        Nov 18, 2025
                    </span>
                    <span class="rounded-full bg-brand/15 px-2 py-0.5 font-semibold text-brand">Investors</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold">American Shipping & Logistics Q3 results: 8.7% year-over-year revenue growth</h2>
            </div>
            <a href="contact.php" class="text-xs font-bold uppercase tracking-wider text-brand hover:underline">Read more →</a>
        </article>
        <article class="flex flex-col gap-3 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        Oct 02, 2025
                    </span>
                    <span class="rounded-full bg-brand/15 px-2 py-0.5 font-semibold text-brand">Service</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold">New Trans-Pacific express route: LAX ↔ HKG in 18 hours</h2>
            </div>
            <a href="contact.php" class="text-xs font-bold uppercase tracking-wider text-brand hover:underline">Read more →</a>
        </article>
        <article class="flex flex-col gap-3 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        Sep 15, 2025
                    </span>
                    <span class="rounded-full bg-brand/15 px-2 py-0.5 font-semibold text-brand">Sustainability</span>
                </div>
                <h2 class="mt-2 text-lg font-semibold">GoGreen Plus expanded to all international parcels</h2>
            </div>
            <a href="contact.php" class="text-xs font-bold uppercase tracking-wider text-brand hover:underline">Read more →</a>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

