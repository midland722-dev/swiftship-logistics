<?php
$page_title       = 'Terms of Use — American Shipping & Logistics';
$page_description = 'Terms of use for American Shipping & Logistics website, tracking, booking, and related services.';
$canonical        = '/terms.php';
$active_nav       = '';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x py-16 md:py-24">
    <h1 class="font-display text-4xl font-bold md:text-5xl">Terms of Use</h1>
    <p class="mt-4 max-w-3xl text-muted-foreground">
        Please read these terms carefully before using American Shipping &amp; Logistics website, tracking tools, booking systems,
        or any related services. By accessing or using our services, you agree to be bound by these terms.
    </p>

    <div class="mt-10 space-y-10">
        <section>
            <h2 class="font-display text-2xl font-bold">1. Acceptance of terms</h2>
            <p class="mt-4 text-muted-foreground">
                These Terms of Use ("Terms") govern your use of American Shipping &amp; Logistics websites, APIs, and digital services.
                If you do not agree, you must not use our services.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">2. Eligibility</h2>
            <p class="mt-4 text-muted-foreground">
                You must be at least 18 years old and capable of forming a binding contract to use our services.
                By using our services, you represent that you meet these requirements.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">3. Booking & payment</h2>
            <p class="mt-4 text-muted-foreground">
                Quotes are estimates. Final charges may vary based on actual weight, dimensions, and service
                selection. Payment is due as specified on your invoice or booking confirmation. Late payments
                may incur interest and suspension of services.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">4. Liability</h2>
            <p class="mt-4 text-muted-foreground">
                American Shipping &amp; Logistics liability is limited to the declared value of the shipment or the limits set by
                applicable international conventions (e.g., CMR, Warsaw Convention). We are not liable for
                indirect, incidental, or consequential damages.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">5. Prohibited items</h2>
            <p class="mt-4 text-muted-foreground">
                You must not ship illegal, dangerous, perishable, or prohibited items. Full prohibited-items
                lists are available on request. American Shipping &amp; Logistics reserves the right to refuse, inspect, or dispose of
                suspicious shipments.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">6. Changes to terms</h2>
            <p class="mt-4 text-muted-foreground">
                We may update these Terms from time to time. Continued use after changes constitutes
                acceptance of the updated Terms. The current version is always available on this page.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">7. Contact</h2>
            <p class="mt-4 text-muted-foreground">
                For questions about these Terms, contact info@ascl-logistics.com.
            </p>
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

