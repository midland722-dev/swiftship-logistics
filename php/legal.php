<?php
$page_title       = 'Legal Notice — American Shipping & Logistics';
$page_description = 'Legal notice, corporate information, and regulatory disclosures for American Shipping & Logistics.';
$canonical        = '/legal.php';
$active_nav       = '';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x py-16 md:py-24">
    <h1 class="font-display text-4xl font-bold md:text-5xl">Legal Notice</h1>

    <div class="mt-10 grid gap-10 md:grid-cols-[1fr_2fr]">
        <nav class="space-y-2 text-sm">
            <a href="#company" class="block rounded-lg px-3 py-2 bg-surface font-medium">Company information</a>
            <a href="#terms" class="block rounded-lg px-3 py-2 text-muted-foreground hover:text-foreground">Terms of Use</a>
            <a href="#privacy" class="block rounded-lg px-3 py-2 text-muted-foreground hover:text-foreground">Privacy Notice</a>
            <a href="#cookies" class="block rounded-lg px-3 py-2 text-muted-foreground hover:text-foreground">Cookie policy</a>
            <a href="#disputes" class="block rounded-lg px-3 py-2 text-muted-foreground hover:text-foreground">Disputes & governing law</a>
        </nav>

        <div class="space-y-10">
            <section id="company">
                <h2 class="font-display text-2xl font-bold">Company information</h2>
                <p class="mt-4 text-muted-foreground">
                    American Shipping &amp; Logistics Inc.<br>
                    United States of America<br>
                    Phone: +1 (215) 815-9791<br>
                    Email: info@ascl-logistics.com
                </p>
            </section>

            <section id="terms">
                <h2 class="font-display text-2xl font-bold">Terms of Use</h2>
                <p class="mt-4 text-muted-foreground">
                    By using this site, you agree to American Shipping &amp; Logistics Terms of Use. All content is the property of
                    American Shipping &amp; Logistics or its licensors and is protected by copyright and other intellectual
                    property laws.
                </p>
            </section>

            <section id="privacy">
                <h2 class="font-display text-2xl font-bold">Privacy Notice</h2>
                <p class="mt-4 text-muted-foreground">
                    We collect and process personal data in accordance with our Privacy Notice and applicable
                    data protection laws, including GDPR where relevant.
                </p>
                <a href="privacy.php" class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-brand hover:underline">Read full privacy notice →</a>
            </section>

            <section id="cookies">
                <h2 class="font-display text-2xl font-bold">Cookie policy</h2>
                <p class="mt-4 text-muted-foreground">
                    We use essential cookies to operate the site and optional analytics cookies to improve
                    performance. You can manage preferences in your browser settings.
                </p>
            </section>

            <section id="disputes">
                <h2 class="font-display text-2xl font-bold">Disputes & governing law</h2>
                <p class="mt-4 text-muted-foreground">
                    These terms are governed by the laws of the Federal Republic of Germany. The courts of
                    Hamburg shall have exclusive jurisdiction for all disputes arising from or in connection
                    with these terms.
                </p>
            </section>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

