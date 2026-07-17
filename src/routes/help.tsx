import { createFileRoute, Link } from "@tanstack/react-router";
import { Package, CreditCard, Truck, Shield } from "lucide-react";

export const Route = createFileRoute("/help")({
  head: () => ({
    meta: [
      { title: "Help center — Voltra" },
      { name: "description", content: "Answers to common questions about tracking, shipping, billing, and claims." },
      { property: "og:title", content: "Voltra Help Center" },
      { property: "og:description", content: "Support for tracking, shipping, billing, and claims." },
      { property: "og:url", content: "/help" },
    ],
    links: [{ rel: "canonical", href: "/help" }],
  }),
  component: HelpPage,
});

const topics = [
  { icon: Package, title: "Tracking", body: "Enter your tracking number on the Track page. Updates appear within minutes of each network scan." },
  { icon: Truck, title: "Shipping", body: "Book pickups online, drop off at 4,500+ locations, or schedule recurring collections for your business." },
  { icon: CreditCard, title: "Billing", body: "Invoices are issued weekly for business accounts. Log in to your account portal to download PDFs." },
  { icon: Shield, title: "Claims & insurance", body: "Report loss or damage within 30 days. Standard insurance covers $100; extended cover up to $2,000." },
];

const faqs = [
  { q: "How do I track a shipment?", a: "Use the tracking number from your booking confirmation on our Track page. Live status updates every few minutes." },
  { q: "What if my parcel is delayed?", a: "Delays over 24 hours past ETA are eligible for our on-time refund guarantee on Priority and Express services." },
  { q: "Can I change the delivery address?", a: "Yes — use the tracking page to request a redirect until the last-mile courier collects the parcel." },
  { q: "How is shipping cost calculated?", a: "The higher of actual weight or volumetric weight (L × W × H ÷ 5000), multiplied by service speed and zone." },
];

function HelpPage() {
  return (
    <>
      <section className="container-x pt-16 pb-14 md:pt-24">
        <p className="font-mono text-xs uppercase tracking-widest text-brand">Help center</p>
        <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
          We're here to help.
        </h1>
        <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
          Find quick answers below, or reach our support team 24/7.
        </p>
      </section>

      <section className="container-x grid gap-4 pb-14 sm:grid-cols-2 lg:grid-cols-4">
        {topics.map(({ icon: Icon, title, body }) => (
          <div key={title} className="rounded-2xl border border-border bg-surface/60 p-6">
            <Icon className="h-6 w-6 text-brand" />
            <h2 className="mt-4 font-semibold">{title}</h2>
            <p className="mt-2 text-sm text-muted-foreground">{body}</p>
          </div>
        ))}
      </section>

      <section className="container-x pb-24">
        <h2 className="font-display text-2xl font-bold">Frequently asked questions</h2>
        <div className="mt-6 divide-y divide-border rounded-2xl border border-border bg-surface/60">
          {faqs.map((f) => (
            <details key={f.q} className="group p-5">
              <summary className="cursor-pointer list-none font-semibold marker:hidden">
                <span className="mr-2 text-brand">+</span>{f.q}
              </summary>
              <p className="mt-3 pl-6 text-sm text-muted-foreground">{f.a}</p>
            </details>
          ))}
        </div>

        <div className="mt-10 rounded-2xl border border-border bg-surface/60 p-6 text-center">
          <p className="text-sm text-muted-foreground">Still stuck?</p>
          <Link to="/contact" className="mt-3 inline-block rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
            Contact support
          </Link>
        </div>
      </section>
    </>
  );
}
