<?php
/**
 * Shared site footer.
 * Include at the bottom of every page before </body>.
 */
$footer_cols = [
    'About Us' => [
        ['Company',       'about.php'],
        ['Newsroom',      'news.php'],
        ['Careers',       'careers.php'],
        ['Sustainability','sustainability.php'],
        ['Investors',     'about.php'],
    ],
    'Business Divisions' => [
        ['American Shipping Express',    'services.php'],
        ['American Shipping eCommerce',  'services.php'],
        ['Global Forwarding', 'services.php'],
        ['Supply Chain',      'services.php'],
        ['Parcel & Same-day', 'services.php'],
    ],
    'Customer Service' => [
        ['Track a shipment', 'track.php'],
        ['Get a quote',      'quote.php'],
        ['Pricing',          'pricing.php'],
        ['Help center',      'help.php'],
        ['Contact us',       'contact.php'],
    ],
    'Careers & More' => [
        ['Careers',              'careers.php'],
        ['Newsroom',             'news.php'],
        ['Sustainability',       'sustainability.php'],
        ['Help center',          'help.php'],
        ['Contact & locations',  'contact.php'],
    ],
];

$legal_links = [
    ['Legal Notice',   'legal.php'],
    ['Terms of Use',   'terms.php'],
    ['Privacy Notice', 'privacy.php'],
    ['Cookie Settings','privacy.php'],
    ['Sustainability', 'sustainability.php'],
];
?>

<!-- ====== FOOTER ====== -->
<footer class="mt-20">

    <!-- Brand / country strip -->
    <div class="bg-brand text-gray-900">
        <div class="container-x flex flex-col gap-3 py-4 text-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2 font-semibold flex-wrap">
                <!-- Globe icon -->
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                    <path d="M2 12h20"/>
                </svg>
                You are in <span class="font-bold">United States of America</span>
                <span class="mx-2 opacity-40">&middot;</span>
                <a href="contact.php" class="underline underline-offset-4 hover:text-red-700">
                    Select a different country
                </a>
            </div>
            <div class="flex items-center gap-4 opacity-80">
                <span class="text-xs font-semibold uppercase tracking-wider">Follow us</span>
                <!-- Facebook -->
                <a href="#" aria-label="Facebook" class="hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                    </svg>
                </a>
                <!-- Twitter/X -->
                <a href="#" aria-label="Twitter" class="hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/>
                    </svg>
                </a>
                <!-- LinkedIn -->
                <a href="#" aria-label="LinkedIn" class="hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                        <rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>
                    </svg>
                </a>
                <!-- YouTube -->
                <a href="#" aria-label="YouTube" class="hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/>
                        <path d="m10 15 5-3-5-3z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Main dark section -->
    <div style="background-color:#1e2433;" class="text-white">
        <div class="container-x grid gap-10 py-14 md:grid-cols-5">

            <!-- Brand blurb -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-2 font-display text-lg font-bold">
                    <span class="grid h-8 w-8 place-items-center rounded bg-brand text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m7.5 4.27 9 5.15"/>
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                            <path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>
                        </svg>
                    </span>
                    American Shipping &amp; Logistics
                </div>
                <p class="mt-4 text-sm" style="color:rgba(255,255,255,.6)">
                    Excellence. Simply delivered.<br>
                    Global logistics and courier services in 220+ countries.
                </p>
            </div>

            <!-- Link columns -->
            <?php foreach ($footer_cols as $col_title => $col_links): ?>
                <div>
                    <h4 class="mb-4 text-sm font-bold uppercase tracking-wider text-white">
                        <?= h($col_title) ?>
                    </h4>
                    <ul class="space-y-2.5 text-sm" style="color:rgba(255,255,255,.7)">
                        <?php foreach ($col_links as [$label, $href]): ?>
                            <li>
                                <a href="<?= h($href) ?>"
                                   class="transition hover:text-yellow-400">
                                    <?= h($label) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

        </div>

        <!-- Legal bar -->
        <div style="border-top:1px solid rgba(255,255,255,.1)">
            <div class="container-x flex flex-col gap-3 py-5 text-xs md:flex-row md:items-center md:justify-between"
                 style="color:rgba(255,255,255,.5)">
                <p>&copy; <?= date('Y') ?> American Shipping &amp; Logistics. All rights reserved.</p>
                <ul class="flex flex-wrap gap-x-5 gap-y-2">
                    <?php foreach ($legal_links as [$label, $href]): ?>
                        <li>
                            <a href="<?= h($href) ?>" class="transition hover:text-yellow-400">
                                <?= h($label) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

</footer>
<!-- ====== END FOOTER ====== -->

</body>
</html>
