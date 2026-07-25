<?php
$page_title       = 'Careers — American Shipping & Logistics';
$page_description = 'Join American Shipping & Logistics. Explore open roles in logistics, engineering, operations, and corporate teams worldwide.';
$canonical        = '/careers.php';
$active_nav       = 'Careers';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-16 pb-14 md:pt-24">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Careers</p>
    <h1 class="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
        Build the future of logistics.
    </h1>
    <p class="mt-5 max-w-2xl text-lg text-muted-foreground">
        We're looking for people who move fast, think big, and care about the details. Join our team.
    </p>
</section>

<section class="container-x grid gap-4 pb-16 md:grid-cols-2 lg:grid-cols-3">
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-brand">Engineering</div>
        <h2 class="mt-2 text-lg font-semibold">Senior Backend Engineer</h2>
        <p class="mt-2 text-sm text-muted-foreground">Berlin · Remote EU · Full-time</p>
        <a href="contact.php" class="mt-4 text-sm font-semibold text-brand hover:underline">Apply now →</a>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-brand">Operations</div>
        <h2 class="mt-2 text-lg font-semibold">Hub Manager, Leipzig</h2>
        <p class="mt-2 text-sm text-muted-foreground">Leipzig · On-site · Full-time</p>
        <a href="contact.php" class="mt-4 text-sm font-semibold text-brand hover:underline">Apply now →</a>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-brand">Product</div>
        <h2 class="mt-2 text-lg font-semibold">Product Designer</h2>
        <p class="mt-2 text-sm text-muted-foreground">Singapore · Hybrid · Full-time</p>
        <a href="contact.php" class="mt-4 text-sm font-semibold text-brand hover:underline">Apply now →</a>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-brand">Data</div>
        <h2 class="mt-2 text-lg font-semibold">Data Scientist, Routing</h2>
        <p class="mt-2 text-sm text-muted-foreground">New York · Hybrid · Full-time</p>
        <a href="contact.php" class="mt-4 text-sm font-semibold text-brand hover:underline">Apply now →</a>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-brand">Customer Experience</div>
        <h2 class="mt-2 text-lg font-semibold">Support Lead, APAC</h2>
        <p class="mt-2 text-sm text-muted-foreground">Singapore · Remote · Full-time</p>
        <a href="contact.php" class="mt-4 text-sm font-semibold text-brand hover:underline">Apply now →</a>
    </div>
    <div class="flex flex-col rounded-2xl border border-border bg-surface/60 p-6">
        <div class="text-xs font-semibold uppercase tracking-wider text-brand">Sustainability</div>
        <h2 class="mt-2 text-lg font-semibold">ESG Reporting Manager</h2>
        <p class="mt-2 text-sm text-muted-foreground">Hamburg · Hybrid · Full-time</p>
        <a href="contact.php" class="mt-4 text-sm font-semibold text-brand hover:underline">Apply now →</a>
    </div>
</section>

<section class="container-x pb-24">
    <div class="rounded-3xl border border-border bg-surface/60 p-10 text-center md:p-16">
        <h2 class="font-display text-3xl font-bold md:text-4xl">Don't see a fit?</h2>
        <p class="mx-auto mt-3 max-w-xl text-muted-foreground">
            We're always looking for talented people. Send us your CV and we'll reach out when there's a match.
        </p>
        <a href="contact.php" class="mt-6 inline-block rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Send speculative application</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

