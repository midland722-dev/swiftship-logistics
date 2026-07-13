import { createFileRoute, Link } from "@tanstack/react-router";
import { Plane, Truck, Ship, Warehouse, Package, Boxes, Factory, ShoppingBag } from "lucide-react";

export const Route = createFileRoute("/services")({
  head: () => ({
    meta: [
      { title: "Services — Voltra Logistics" },
      { name: "description", content: "Express shipping, freight, eCommerce logistics, and supply chain solutions from Voltra." },
      { property: "og:title", content: "Services — Voltra Logistics" },
      { property: "og:description", content: "Everything Voltra ships, from letters to full truckloads." },
    ],
    links: [{ rel: "canonical", href: "/services" }],
  }),
  component: ServicesPage,
});

const services = [
  { icon: Plane, title: "Express Shipping", desc: "Time-definite international delivery with next-business-day options to 60+ major hubs.", features: ["Next-day international", "Time-definite delivery", "Signature required", "Full insurance"] },
  { icon: Truck, title: "Freight Services", desc: "Road freight, LTL, and FTL across North America, Europe, and Asia with real-time visibility.", features: ["LTL & FTL", "Temperature-controlled", "Hazmat certified", "Cross-border expertise"] },
  { icon: ShoppingBag, title: "eCommerce Logistics", desc: "End-to-end fulfilment for online stores — pick, pack, ship, and returns.", features: ["Shopify & WooCommerce", "Returns management", "Branded packaging", "Same-day dispatch"] },
  { icon: Warehouse, title: "Supply Chain Solutions", desc: "Warehousing, distribution, and consulting for global supply chain optimization.", features: ["50+ warehouses", "Inventory management", "3PL & 4PL", "Custom KPIs"] },
  { icon: Ship, title: "Ocean & Air Freight", desc: "Container shipping and air cargo with customs clearance included.", features: ["FCL & LCL", "Air charter available", "Customs brokerage", "Door-to-door"] },
  { icon: Factory, title: "Industrial Logistics", desc: "Heavy, oversized, and project cargo handled by specialised teams.", features: ["Project cargo", "Oversized freight", "Rigging & installation", "Route surveys"] },
  { icon: Boxes, title: "Warehousing", desc: "Flexible storage with real-time inventory visibility.", features: ["Bonded warehousing", "Pick & pack", "Kitting & assembly", "WMS integration"] },
  { icon: Package, title: "Parcel & Same-Day", desc: "On-demand parcel delivery within metro areas — under 4 hours.", features: ["Under 4-hour delivery", "Live courier tracking", "Photo proof of delivery", "Metro coverage"] },
];

function ServicesPage() {
  return (
    <>
      <section className="container-x pb-14 pt-16 md:pt-24">
        <p className="font-mono text-xs uppercase tracking-widest text-brand">Services</p>
        <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
          Whatever you're shipping, we route it.
        </h1>
        <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
          Eight service lines built on one global network — from same-day metro drops to
          transcontinental ocean freight.
        </p>
      </section>

      <section className="container-x grid gap-4 pb-24 md:grid-cols-2 lg:grid-cols-4">
        {services.map(({ icon: Icon, title, desc, features }) => (
          <div key={title} className="flex flex-col rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50">
            <div className="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
              <Icon className="h-5 w-5" />
            </div>
            <h2 className="mt-5 text-lg font-semibold">{title}</h2>
            <p className="mt-2 text-sm text-muted-foreground">{desc}</p>
            <ul className="mt-4 space-y-1.5 text-xs text-muted-foreground">
              {features.map((f) => (
                <li key={f} className="flex items-center gap-2">
                  <span className="h-1 w-1 rounded-full bg-brand" />
                  {f}
                </li>
              ))}
            </ul>
          </div>
        ))}
      </section>

      <section className="container-x pb-24">
        <div className="rounded-3xl border border-border bg-surface/60 p-10 text-center md:p-16">
          <h2 className="font-display text-3xl font-bold md:text-4xl">Not sure what you need?</h2>
          <p className="mx-auto mt-3 max-w-xl text-muted-foreground">
            Tell us what you're shipping and where — we'll recommend the right service and price.
          </p>
          <div className="mt-6 flex flex-wrap justify-center gap-3">
            <Link to="/quote" className="rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
              Get a quote
            </Link>
            <Link to="/contact" className="rounded-sm border border-border px-5 py-3 text-sm font-semibold hover:bg-surface">
              Talk to an expert
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
