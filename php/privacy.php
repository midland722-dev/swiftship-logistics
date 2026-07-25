<?php
$page_title       = 'Privacy Notice — American Shipping & Logistics';
$page_description = 'How American Shipping & Logistics collects, uses, stores, and protects your personal data.';
$canonical        = '/privacy.php';
$active_nav       = '';

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x py-16 md:py-24">
    <h1 class="font-display text-4xl font-bold md:text-5xl">Privacy Notice</h1>
    <p class="mt-4 max-w-3xl text-muted-foreground">
        Last updated: July 2026. This notice explains how American Shipping &amp; Logistics ("we", "us", "our") collects,
        uses, stores, and protects your personal data when you use our websites, tracking tools, booking
        systems, and related services.
    </p>

    <div class="mt-10 space-y-10">
        <section>
            <h2 class="font-display text-2xl font-bold">1. What we collect</h2>
            <p class="mt-4 text-muted-foreground">
                Contact details, shipment data, billing information, and technical logs required to deliver our services.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">2. How we use it</h2>
            <p class="mt-4 text-muted-foreground">
                To ship your parcels, prevent fraud, comply with customs and export regulations, and improve our services.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">3. Who we share it with</h2>
            <p class="mt-4 text-muted-foreground">
                Carriers, customs authorities, and vetted subprocessors — only as needed to complete your shipments.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">4. Your rights</h2>
            <p class="mt-4 text-muted-foreground">
                You can access, correct, export, or delete your data at any time. Email <a href="mailto:info@ascl-logistics.com" class="text-brand hover:underline">info@ascl-logistics.com</a>.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">How we use your data</h2>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-muted-foreground">
                <li>To provide, operate, and improve our logistics services.</li>
                <li>To process bookings, payments, and customer support requests.</li>
                <li>To send service updates, invoices, and legal notices.</li>
                <li>To comply with customs, security, and regulatory obligations.</li>
            </ul>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">Data sharing</h2>
            <p class="mt-4 text-muted-foreground">
                We share data with carriers, customs authorities, and service providers bound by
                confidentiality. We do not sell personal data.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">Your rights</h2>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-muted-foreground">
                <li>Access, correction, or deletion of your personal data.</li>
                <li>Restriction or objection to certain processing.</li>
                <li>Data portability where applicable.</li>
                <li>Right to lodge a complaint with a supervisory authority.</li>
            </ul>
            <p class="mt-4 text-muted-foreground">
                To exercise these rights, contact us at privacy@voltra.example.
            </p>
        </section>

        <section>
            <h2 class="font-display text-2xl font-bold">Retention</h2>
            <p class="mt-4 text-muted-foreground">
                We retain personal data only as long as necessary for the purposes outlined above,
                including legal, tax, and regulatory retention requirements.
            </p>
        </section>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

