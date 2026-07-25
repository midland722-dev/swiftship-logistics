<?php
$page_title       = 'Contact — American Shipping & Logistics';
$page_description = 'Get in touch with American Shipping & Logistics sales, support, or press. We reply within one business hour.';
$canonical        = '/contact.php';
$active_nav       = 'Contact';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$csrf_token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

$feedback = $_GET['feedback'] ?? '';
$sent = isset($_GET['sent']);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container-x py-16 md:py-20">
    <div class="grid gap-14 lg:grid-cols-[1fr_1.2fr]">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-brand">Contact</p>
            <h1 class="mt-2 font-display text-4xl font-bold md:text-5xl">Let's move something.</h1>
            <p class="mt-4 text-muted-foreground">
                Whether you ship a parcel a week or a container a day, our team is here to help you
                find the right service.
            </p>

             <ul class="mt-10 space-y-5">
                 <li class="flex items-start gap-4">
                     <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-background text-brand">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                     </div>
                     <div>
                         <div class="text-xs uppercase tracking-wider text-muted-foreground">Email</div>
                         <div class="mt-0.5 font-medium">info@ascl-logistics.com</div>
                     </div>
                 </li>
                 <li class="flex items-start gap-4">
                     <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-background text-brand">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a12 12 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                     </div>
                     <div>
                         <div class="text-xs uppercase tracking-wider text-muted-foreground">Phone</div>
                         <div class="mt-0.5 font-medium">+1 (215) 815-9791</div>
                     </div>
                 </li>
                 <li class="flex items-start gap-4">
                     <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-background text-brand">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                     </div>
                     <div>
                         <div class="text-xs uppercase tracking-wider text-muted-foreground">Live chat</div>
                         <div class="mt-0.5 font-medium">Weekdays, 07:00 – 22:00 UTC</div>
                     </div>
                 </li>
                 <li class="flex items-start gap-4">
                     <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-background text-brand">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                     </div>
                     <div>
                         <div class="text-xs uppercase tracking-wider text-muted-foreground">HQ</div>
                         <div class="mt-0.5 font-medium">United States</div>
                     </div>
                 </li>
             </ul>
        </div>

        <div>
            <?php if ($sent): ?>
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    Your message has been sent. We'll reply within one business hour.
                </div>
            <?php endif; ?>
            <?php if ($feedback): ?>
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <?= h($feedback) ?>
                </div>
            <?php endif; ?>

            <form action="process/contact_submit.php" method="post" class="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
                <input type="hidden" name="csrf_token" value="<?= h($csrf_token) ?>">

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Name</span>
                        <input type="text" name="name" placeholder="Alex Rivera" required
                               class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Email</span>
                        <input type="email" name="email" placeholder="you@company.com" required
                               class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Company</span>
                        <input type="text" name="company" placeholder="Acme Inc"
                               class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                    </label>
                    <label class="block">
                        <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Phone</span>
                        <input type="tel" name="phone" placeholder="+1 (555) 000-0000"
                               class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                    </label>
                </div>

                <label class="mt-4 block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Subject</span>
                    <select name="category"
                            class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand">
                        <option value="general">General inquiry</option>
                        <option value="shipment-issue">Shipment issue</option>
                        <option value="billing">Billing</option>
                        <option value="technical">Technical</option>
                        <option value="customs">Customs</option>
                        <option value="feedback">Feedback</option>
                        <option value="complaint">Complaint</option>
                        <option value="partnership">Partnership</option>
                    </select>
                </label>

                <label class="mt-4 block">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">How can we help?</span>
                    <textarea name="message" rows="5" placeholder="Tell us about what you ship and where…" required
                              class="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"></textarea>
                </label>

                <button type="submit"
                        class="mt-6 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90 md:w-auto md:px-8">
                    Send message
                </button>
                <p class="mt-3 text-xs text-muted-foreground">We reply within one business hour.</p>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
