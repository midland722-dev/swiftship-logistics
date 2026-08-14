import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/privacy")({
  head: () => ({
    meta: [
      { title: "Privacy notice — Voltra" },
      { name: "description", content: "How Voltra collects, uses, and protects your personal data across our logistics services." },
      { property: "og:title", content: "Privacy notice — Voltra" },
      { property: "og:url", content: "/privacy" },
    ],
    links: [{ rel: "canonical", href: "/privacy" }],
  }),
  component: PrivacyPage,
});

function PrivacyPage() {
  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Privacy</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Privacy notice</h1>
      <p className="mt-3 text-sm text-muted-foreground">Last updated: January 2026</p>

      <div className="prose prose-neutral mt-8 max-w-3xl text-muted-foreground">
        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">1. What we collect</h2>
        <p>Contact details, shipment data, billing information, and technical logs required to deliver our services.</p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">2. How we use it</h2>
        <p>To ship your parcels, prevent fraud, comply with customs and export regulations, and improve our services.</p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">3. Who we share it with</h2>
        <p>Carriers, customs authorities, and vetted subprocessors — only as needed to complete your shipments.</p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">4. Your rights</h2>
        <p>You can access, correct, export, or delete your data at any time. Email <a href="mailto:privacy@voltra.example" className="text-brand hover:underline">privacy@voltra.example</a>.</p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">5. Cookies</h2>
        <p>We use essential cookies for session and security, and optional analytics cookies you can decline in cookie settings.</p>
      </div>
    </section>
  );
}
