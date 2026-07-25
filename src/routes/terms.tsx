import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/terms")({
  head: () => ({
    meta: [
      { title: "Terms of use — American Shipping & Logistics" },
      { name: "description", content: "Terms and conditions governing use of American Shipping & Logistics website and services." },
      { property: "og:title", content: "Terms of use — American Shipping & Logistics" },
      { property: "og:url", content: "/terms" },
    ],
    links: [{ rel: "canonical", href: "/terms" }],
  }),
  component: TermsPage,
});

function TermsPage() {
  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Terms</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Terms of use</h1>
      <p className="mt-3 text-sm text-muted-foreground">Last updated: January 2026</p>

      <div className="prose prose-neutral mt-8 max-w-3xl text-muted-foreground">
        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Acceptance</h2>
        <p>By using this website you agree to these terms. If you do not agree, please do not use the site.</p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Services</h2>
        <p>Shipping services are governed by our standard Terms &amp; Conditions of Carriage, provided at booking.</p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Content</h2>
        <p>All content on this site is © American Shipping &amp; Logistics unless otherwise stated. You may not reproduce or redistribute without permission.</p>

        <h2 className="mt-8 font-display text-xl font-semibold text-foreground">Limitation of liability</h2>
        <p>To the maximum extent permitted by law, American Shipping &amp; Logistics excludes all warranties and liabilities in connection with this website.</p>
      </div>
    </section>
  );
}
