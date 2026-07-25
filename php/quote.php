<?php
$page_title       = 'Get a shipping quote — American Shipping & Logistics';
$page_description = 'Calculate shipping costs by origin, destination, weight, and speed with American Shipping & Logistics instant quote tool.';
$canonical        = '/quote.php';
$active_nav       = 'Get a quote';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x py-16 md:py-20">
    <p class="font-mono text-xs uppercase tracking-widest text-brand">Shipping quote</p>
    <h1 class="mt-2 font-display text-4xl font-bold md:text-5xl">
        Instant quotes. <span class="text-brand">No account needed.</span>
    </h1>
    <p class="mt-4 max-w-xl text-muted-foreground">
        Enter your parcel details and we will price every service tier in seconds.
    </p>

    <div id="quote-error" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800"></div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <div class="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
            <form id="quote-form" class="grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">From</span>
                    <input type="text" name="from" value="Berlin, DE" placeholder="City, Country"
                           class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                </label>
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">To</span>
                    <input type="text" name="to" value="Tokyo, JP" placeholder="City, Country"
                           class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                </label>

                <h3 class="mt-8 text-sm font-semibold uppercase tracking-wider text-muted-foreground md:col-span-2">Parcel</h3>
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Weight (kg)</span>
                    <input type="number" name="weight" value="2.4" step="0.1" min="0.1"
                           class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                </label>
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Length (cm)</span>
                    <input type="number" name="length" value="30" min="1"
                           class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                </label>
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Width (cm)</span>
                    <input type="number" name="width" value="20" min="1"
                           class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                </label>
                <label class="block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Height (cm)</span>
                    <input type="number" name="height" value="15" min="1"
                           class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                </label>

                <h3 class="mt-8 text-sm font-semibold uppercase tracking-wider text-muted-foreground md:col-span-2">Delivery speed</h3>
                <div class="md:col-span-2 grid gap-3 md:grid-cols-3">
                    <label class="cursor-pointer flex flex-col rounded-xl border border-border bg-background/40 p-4 has-[:checked]:border-brand has-[:checked]:bg-brand/10">
                        <input type="radio" name="speed" value="standard" class="sr-only" checked>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M17.5 3H19a2 2 0 0 1 2 2v3.5"/><path d="M6.5 3H5a2 2 0 0 0-2 2v3.5"/><path d="M6.5 21H5a2 2 0 0 1-2-2v-3.5"/><path d="M17.5 21H19a2 2 0 0 0 2-2v-3.5"/><path d="M12 3v18"/><path d="M3 12h18"/></svg>
                        <div class="mt-3 font-semibold">Standard</div>
                        <div class="text-xs text-muted-foreground">5–8 business days</div>
                    </label>
                    <label class="cursor-pointer flex flex-col rounded-xl border border-border bg-background/40 p-4 has-[:checked]:border-brand has-[:checked]:bg-brand/10">
                        <input type="radio" name="speed" value="express" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        <div class="mt-3 font-semibold">Express</div>
                        <div class="text-xs text-muted-foreground">2–3 business days</div>
                    </label>
                    <label class="cursor-pointer flex flex-col rounded-xl border border-border bg-background/40 p-4 has-[:checked]:border-brand has-[:checked]:bg-brand/10">
                        <input type="radio" name="speed" value="priority" class="sr-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M12 2v4"/><path d="m6.8 14-3.5 2.5"/><path d="M20.7 16.5 17.2 14"/><path d="M6.8 10 3.3 7.5"/><path d="M20.7 7.5 17.2 10"/><circle cx="12" cy="12" r="4"/></svg>
                        <div class="mt-3 font-semibold">Priority</div>
                        <div class="text-xs text-muted-foreground">Next business day</div>
                    </label>
                </div>

                <label class="mt-6 flex items-center gap-3 rounded-xl border border-border bg-background/40 p-4 md:col-span-2">
                    <input type="checkbox" name="insurance" checked class="h-4 w-4 accent-[var(--brand)]">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 text-sm font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Add insurance
                        </div>
                        <div class="text-xs text-muted-foreground">Cover contents up to $2,000 in case of loss or damage.</div>
                    </div>
                </label>
            </form>
        </div>

        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-2xl border border-brand/40 bg-gradient-to-b from-brand/15 to-surface p-6 md:p-8">
                <div class="text-xs font-mono uppercase tracking-widest text-brand">Estimated price</div>
                <div id="quote-price" class="mt-2 font-display text-5xl font-bold">$—</div>
                <div id="quote-speed" class="mt-1 text-sm text-muted-foreground">Express shipping</div>

                <ul id="quote-breakdown" class="mt-6 space-y-2 text-sm hidden">
                    <li class="flex items-start gap-2 text-muted-foreground"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-brand"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg> Route</li>
                </ul>

                <button type="button" class="mt-6 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">
                    Book this shipment
                </button>
                <button type="button" class="mt-2 w-full rounded-sm border border-border py-3 text-sm font-semibold hover:bg-surface">
                    Save quote
                </button>
            </div>
        </aside>
    </div>
</section>

<section class="container-x pb-16 md:pb-24">
    <div class="rounded-3xl border border-border bg-surface/60 p-8 md:p-12">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Sample quotes</h2>
        <p class="mt-3 max-w-3xl text-muted-foreground">
            Below are real-world example quotes from our most popular routes and weights.
            Your actual price may vary based on exact origin, destination, dimensions, and any
            additional services such as insurance or special handling.
        </p>

        <div class="mt-8 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 pr-4 font-semibold">Route</th>
                        <th class="py-3 pr-4 font-semibold">Weight</th>
                        <th class="py-3 pr-4 font-semibold">Dimensions</th>
                        <th class="py-3 pr-4 font-semibold">Standard</th>
                        <th class="py-3 pr-4 font-semibold">Express</th>
                        <th class="py-3 font-semibold">Priority</th>
                    </tr>
                </thead>
                <tbody class="text-muted-foreground">
                    <tr class="border-b border-border/60">
                        <td class="py-3 pr-4">New York → London</td>
                        <td class="py-3 pr-4">2 kg</td>
                        <td class="py-3 pr-4">30 × 20 × 15 cm</td>
                        <td class="py-3 pr-4">$24.50</td>
                        <td class="py-3 pr-4">$36.75</td>
                        <td class="py-3">$82.40</td>
                    </tr>
                    <tr class="border-b border-border/60">
                        <td class="py-3 pr-4">Los Angeles → Tokyo</td>
                        <td class="py-3 pr-4">5 kg</td>
                        <td class="py-3 pr-4">40 × 30 × 25 cm</td>
                        <td class="py-3 pr-4">$43.00</td>
                        <td class="py-3 pr-4">$64.50</td>
                        <td class="py-3">$144.80</td>
                    </tr>
                    <tr class="border-b border-border/60">
                        <td class="py-3 pr-4">Miami → São Paulo</td>
                        <td class="py-3 pr-4">10 kg</td>
                        <td class="py-3 pr-4">50 × 40 × 35 cm</td>
                        <td class="py-3 pr-4">$68.00</td>
                        <td class="py-3 pr-4">$102.00</td>
                        <td class="py-3">$228.80</td>
                    </tr>
                    <tr class="border-b border-border/60">
                        <td class="py-3 pr-4">Chicago → Paris</td>
                        <td class="py-3 pr-4">0.5 kg</td>
                        <td class="py-3 pr-4">20 × 15 × 10 cm</td>
                        <td class="py-3 pr-4">$17.50</td>
                        <td class="py-3 pr-4">$26.25</td>
                        <td class="py-3">$58.80</td>
                    </tr>
                    <tr>
                        <td class="py-3 pr-4">Houston → Dubai</td>
                        <td class="py-3 pr-4">20 kg</td>
                        <td class="py-3 pr-4">60 × 50 × 45 cm</td>
                        <td class="py-3 pr-4">$108.00</td>
                        <td class="py-3 pr-4">$162.00</td>
                        <td class="py-3">$363.20</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-xs text-muted-foreground">
            * Sample quotes are illustrative and based on typical volumetric-weight conversions and published rate cards.
            Final pricing is confirmed at booking.
        </p>
    </div>
</section>

<section class="container-x pb-16 md:pb-24">
    <div class="rounded-3xl border border-border bg-surface/60 p-8 md:p-12">
        <h2 class="font-display text-2xl font-bold md:text-3xl">How quoting works</h2>
        <p class="mt-3 max-w-3xl text-muted-foreground">
            Our instant quote engine uses live rate cards, volumetric-weight conversion, and service multipliers
            to give you a transparent estimate in seconds.
        </p>
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <div>
                <h3 class="font-semibold">1. Enter origin and destination</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Use the format <span class="font-mono text-xs">City, Country</span> — for example,
                    <span class="font-mono text-xs">New York, US</span> or <span class="font-mono text-xs">Berlin, DE</span>.
                    The engine does not currently validate city names, so any readable pair is accepted for estimation.
                </p>
            </div>
            <div>
                <h3 class="font-semibold">2. Add parcel dimensions and weight</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    We compare actual weight against volumetric weight
                    (<span class="font-mono text-xs">L × W × H / 5000</span>) and charge whichever is greater.
                    This ensures bulky but lightweight items are priced fairly.
                </p>
            </div>
            <div>
                <h3 class="font-semibold">3. Choose a service tier</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    <span class="text-foreground font-medium">Standard</span> is economy ground/sea.
                    <span class="text-foreground font-medium">Express</span> is air freight at a multiplier of the base rate.
                    <span class="text-foreground font-medium">Priority</span> is next-business-day service with premium handling.
                </p>
            </div>
            <div>
                <h3 class="font-semibold">4. Review and book</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    The estimated price updates live as you edit the form. When you are happy with the quote,
                    use <span class="text-foreground font-medium">Book this shipment</span> to start the booking flow,
                    or <span class="text-foreground font-medium">Save quote</span> to email it to yourself.
                </p>
            </div>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/contact" class="rounded bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90">Talk to an expert</a>
            <a href="/services" class="rounded border border-border px-5 py-3 text-sm font-semibold hover:bg-surface">Explore services</a>
        </div>
    </div>
</section>

<section class="container-x pb-16 md:pb-24">
    <div class="rounded-3xl border border-border bg-surface/60 p-8 md:p-12">
        <h2 class="font-display text-2xl font-bold md:text-3xl">Quote FAQ</h2>
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <div>
                <h3 class="font-semibold">Is the quote final?</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    The quote is an estimate. The final price is confirmed when you complete the booking and we
                    have verified the exact weight, dimensions, and any customs or handling requirements.
                </p>
            </div>
            <div>
                <h3 class="font-semibold">What affects the price?</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Price is driven by billable weight (actual vs volumetric), origin/destination pair,
                    service speed, and optional add-ons such as insurance or signature confirmation.
                </p>
            </div>
            <div>
                <h3 class="font-semibold">Do you offer business rates?</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Yes. If you ship regularly, contact our sales team for a custom rate card based on
                    monthly volume, lane mix, and service requirements.
                </p>
            </div>
            <div>
                <h3 class="font-semibold">How long is a quote valid?</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Quotes are valid for 7 days. Rates can change due to currency fluctuations, fuel surcharges,
                    or carrier rate updates. We recommend booking within the validity window to lock the price.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
