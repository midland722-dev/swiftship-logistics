import { createFileRoute, Link } from "@tanstack/react-router";
import { Check } from "lucide-react";

export const Route = createFileRoute("/pricing")({
  head: () => ({
    meta: [
      { title: "Pricing — Voltra Logistics" },
      { name: "description", content: "Transparent shipping plans for individuals, small businesses, and global enterprises." },
      { property: "og:title", content: "Pricing — Voltra" },
      { property: "og:description", content: "Simple, transparent logistics pricing." },
    ],
    links: [{ rel: "canonical", href: "/pricing" }],
  }),
  component: PricingPage,
});

const tiers = [
  {
    name: "Send",
    price: "Pay per ship",
    tagline: "For occasional shipments and one-off parcels.",
    features: ["Instant quotes", "Global tracking", "Drop-off at 4,500+ points", "Basic insurance up to $100"],
    cta: "Get a quote",
    to: "/quote",
    highlight: false,
  },
  {
    name: "Business",
    price: "$49/mo",
    tagline: "For growing shops shipping 50+ parcels a month.",
    features: ["15% off standard rates", "Bulk shipment upload", "Branded tracking pages", "Priority support", "API access"],
    cta: "Start business plan",
    to: "/contact",
    highlight: true,
  },
  {
    name: "Enterprise",
    price: "Custom",
    tagline: "For global operations and dedicated logistics teams.",
    features: ["Custom pricing tiers", "Dedicated account manager", "SLA guarantees", "SSO & audit logs", "Full API + webhooks"],
    cta: "Talk to sales",
    to: "/contact",
    highlight: false,
  },
];

function PricingPage() {
  return (
    <>
      <section className="container-x pt-16 text-center md:pt-24">
        <p className="font-mono text-xs uppercase tracking-widest text-brand">Pricing</p>
        <h1 className="mt-2 font-display text-5xl font-bold md:text-6xl">
          Priced by the parcel. <span className="text-brand">Never by surprise.</span>
        </h1>
        <p className="mx-auto mt-5 max-w-xl text-lg text-muted-foreground">
          Start with pay-as-you-ship, then scale into volume discounts when you're ready.
        </p>
      </section>

      <section className="container-x grid gap-6 py-16 md:grid-cols-3">
        {tiers.map((t) => (
          <div
            key={t.name}
            className={`relative flex flex-col rounded-2xl border p-8 ${
              t.highlight
                ? "border-brand bg-gradient-to-b from-brand/10 to-surface"
                : "border-border bg-surface/60"
            }`}
          >
            {t.highlight && (
              <span className="absolute right-6 top-6 rounded-full bg-brand px-2.5 py-1 text-xs font-semibold text-brand-foreground">
                Most popular
              </span>
            )}
            <h2 className="font-display text-2xl font-bold">{t.name}</h2>
            <div className="mt-3 font-display text-4xl font-bold">{t.price}</div>
            <p className="mt-2 text-sm text-muted-foreground">{t.tagline}</p>
            <ul className="mt-6 space-y-3 text-sm">
              {t.features.map((f) => (
                <li key={f} className="flex items-start gap-2">
                  <Check className="mt-0.5 h-4 w-4 shrink-0 text-brand" />
                  <span>{f}</span>
                </li>
              ))}
            </ul>
            <Link
              to={t.to}
              className={`mt-8 rounded-md py-3 text-center text-sm font-semibold ${
                t.highlight
                  ? "bg-brand text-brand-foreground hover:opacity-90"
                  : "border border-border hover:bg-surface"
              }`}
            >
              {t.cta}
            </Link>
          </div>
        ))}
      </section>

      <section className="container-x pb-24">
        <div className="rounded-2xl border border-border bg-surface/60 p-8">
          <h2 className="font-display text-xl font-semibold">Rate examples (0.5 kg parcel)</h2>
          <div className="mt-6 overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="text-left text-xs uppercase tracking-wider text-muted-foreground">
                <tr>
                  <th className="pb-3 pr-4">Route</th>
                  <th className="pb-3 pr-4">Standard</th>
                  <th className="pb-3 pr-4">Express</th>
                  <th className="pb-3">Same-day</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border">
                {[
                  ["Berlin → Paris", "$12.40", "$24.90", "—"],
                  ["London → New York", "$28.10", "$59.90", "—"],
                  ["Tokyo → Singapore", "$18.50", "$42.00", "—"],
                  ["Metro same-city", "$6.00", "—", "$14.90"],
                ].map((row) => (
                  <tr key={row[0]}>
                    {row.map((cell, i) => (
                      <td key={i} className={`py-3 pr-4 ${i === 0 ? "font-medium" : "text-muted-foreground"}`}>
                        {cell}
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </>
  );
}
