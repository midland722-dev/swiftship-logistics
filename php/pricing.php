<?php
$page_title       = 'Pricing — American Shipping & Logistics';
$page_description = 'Transparent shipping plans for individuals, small businesses, and global enterprises.';
$canonical        = '/pricing.php';
$active_nav       = 'Pricing';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x pt-16 text-center md:pt-24">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Pricing</p>
    <h1 class="mt-2 font-display text-5xl font-bold md:text-6xl">
        Priced by the parcel. <span class="text-brand">Never by surprise.</span>
    </h1>
    <p class="mx-auto mt-5 max-w-xl text-lg text-muted-foreground">
        Start with pay-as-you-ship, then scale into volume discounts when you're ready.
    </p>
</section>

<section class="container-x grid gap-6 py-16 md:grid-cols-3">
    <div class="relative flex flex-col rounded-2xl border border-border bg-surface/60 p-8">
        <h2 class="font-display text-2xl font-bold">Send</h2>
        <div class="mt-3 font-display text-4xl font-bold">Pay per ship</div>
        <p class="mt-2 text-sm text-muted-foreground">For occasional shipments and one-off parcels.</p>
        <ul class="mt-6 space-y-3 text-sm">
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Instant quotes</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Global tracking</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Drop-off at 4,500+ points</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Basic insurance up to $100</li>
        </ul>
        <a href="quote.php" class="mt-8 rounded-sm border border-border py-3 text-center text-sm font-bold uppercase tracking-wider hover:bg-surface">Get a quote</a>
    </div>
    <div class="relative flex flex-col rounded-2xl border border-brand bg-gradient-to-b from-brand/10 to-surface p-8">
        <span class="absolute right-6 top-6 rounded-full bg-brand px-2.5 py-1 text-xs font-semibold text-brand-foreground">Most popular</span>
        <h2 class="font-display text-2xl font-bold">Business</h2>
        <div class="mt-3 font-display text-4xl font-bold">$49/mo</div>
        <p class="mt-2 text-sm text-muted-foreground">For growing shops shipping 50+ parcels a month.</p>
        <ul class="mt-6 space-y-3 text-sm">
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> 15% off standard rates</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Bulk shipment upload</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Branded tracking pages</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Priority support</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> API access</li>
        </ul>
        <a href="contact.php" class="mt-8 rounded-sm bg-accent py-3 text-center text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Start business plan</a>
    </div>
    <div class="relative flex flex-col rounded-2xl border border-border bg-surface/60 p-8">
        <h2 class="font-display text-2xl font-bold">Enterprise</h2>
        <div class="mt-3 font-display text-4xl font-bold">Custom</div>
        <p class="mt-2 text-sm text-muted-foreground">For global operations and dedicated logistics teams.</p>
        <ul class="mt-6 space-y-3 text-sm">
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Custom pricing tiers</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Dedicated account manager</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> SLA guarantees</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> SSO & audit logs</li>
            <li class="flex items-start gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Full API + webhooks</li>
        </ul>
        <a href="contact.php" class="mt-8 rounded-sm border border-border py-3 text-center text-sm font-bold uppercase tracking-wider hover:bg-surface">Talk to sales</a>
    </div>
</section>

<section class="container-x pb-24">
    <div class="rounded-2xl border border-border bg-surface/60 p-8">
        <h2 class="font-display text-xl font-semibold">Rate examples (0.5 kg parcel)</h2>
        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="pb-3 pr-4">Route</th>
                        <th class="pb-3 pr-4">Standard</th>
                        <th class="pb-3 pr-4">Express</th>
                        <th class="pb-3">Same-day</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr>
                        <td class="py-3 pr-4 font-medium">Berlin → Paris</td>
                        <td class="py-3 pr-4 text-muted-foreground">$12.40</td>
                        <td class="py-3 pr-4 text-muted-foreground">$24.90</td>
                        <td class="py-3 text-muted-foreground">—</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-medium">London → New York</td>
                        <td class="py-3 pr-4 text-muted-foreground">$28.10</td>
                        <td class="py-3 pr-4 text-muted-foreground">$59.90</td>
                        <td class="py-3 text-muted-foreground">—</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-medium">Tokyo → Singapore</td>
                        <td class="py-3 pr-4 text-muted-foreground">$18.50</td>
                        <td class="py-3 pr-4 text-muted-foreground">$42.00</td>
                        <td class="py-3 text-muted-foreground">—</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4 font-medium">Metro same-city</td>
                        <td class="py-3 pr-4 text-muted-foreground">$6.00</td>
                        <td class="py-3 pr-4 text-muted-foreground">—</td>
                        <td class="py-3 text-muted-foreground">$14.90</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

