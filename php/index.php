<?php
$page_description = 'Ship, track, and quote parcels and freight to 220+ countries. American Shipping & Logistics — global logistics, simply delivered.';
$canonical        = '/index.php';
$active_nav       = '';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="relative overflow-hidden bg-brand">
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-brand via-brand/85 to-brand/30"></div>
    <div class="container-x relative grid gap-10 pb-14 pt-14 md:pt-20 lg:grid-cols-[1.2fr_1fr] lg:gap-12 lg:pb-20">
        <div class="flex flex-col justify-center text-brand-foreground">
            <h1 class="font-display text-5xl font-bold leading-[1.02] tracking-tight md:text-6xl lg:text-7xl">
                Excellence.<br>
                Simply delivered.
            </h1>
            <p class="mt-6 max-w-lg text-lg md:text-xl text-brand-foreground/80">
                Ship, track, and quote parcels and freight to 220+ countries. Global logistics, simply delivered.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="track.php" class="rounded bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Track shipment</a>
                <a href="quote.php" class="rounded border-2 border-brand-foreground px-6 py-3 text-sm font-bold uppercase tracking-wider text-brand-foreground hover:bg-brand-foreground hover:text-brand">Get a quote</a>
            </div>
        </div>
        <div class="flex items-center justify-center lg:justify-end">
            <div class="rounded-2xl border border-brand-foreground/20 bg-white/10 p-6 backdrop-blur-sm">
                <form action="track.php" method="get" class="flex gap-2">
                    <input type="text" name="id" placeholder="Enter tracking number" required autocomplete="off"
                           class="flex-1 rounded-lg border border-brand-foreground/30 bg-white/90 px-4 py-3 text-sm outline-none placeholder:text-brand-foreground/60 focus:border-brand-foreground">
                    <button type="submit" class="rounded bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Track</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="container-x py-16 md:py-24">
    <div class="grid gap-10 md:grid-cols-3">
        <div class="rounded-2xl border border-border bg-surface/60 p-6">
            <h2 class="font-display text-xl font-bold">Express Shipping</h2>
            <p class="mt-2 text-sm text-muted-foreground">Time-definite international delivery with next-business-day options to 60+ major hubs.</p>
            <a href="services.php" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">Learn more <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
        </div>
        <div class="rounded-2xl border border-border bg-surface/60 p-6">
            <h2 class="font-display text-xl font-bold">Freight Services</h2>
            <p class="mt-2 text-sm text-muted-foreground">Road freight, LTL, and FTL across North America, Europe, and Asia with real-time visibility.</p>
            <a href="services.php" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">Learn more <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
        </div>
        <div class="rounded-2xl border border-border bg-surface/60 p-6">
            <h2 class="font-display text-xl font-bold">Supply Chain</h2>
            <p class="mt-2 text-sm text-muted-foreground">Warehousing, distribution, and consulting for global supply chain optimization.</p>
            <a href="services.php" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">Learn more <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
