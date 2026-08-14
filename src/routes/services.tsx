import { createFileRoute, Link } from "@tanstack/react-router";
import { Plane, Truck, Ship, Warehouse, Package, Boxes, Factory, ShoppingBag, CheckCircle2 } from "lucide-react";
import servicesHero from "@/assets/services-hero.jpg";
import warehouseOps from "@/assets/warehouse-ops.jpg";

export const Route = createFileRoute("/services")({
  head: () => ({
    meta: [
      { title: "Services — American Shipping & Logistics" },
      { name: "description", content: "Express shipping, freight, eCommerce logistics, and supply chain solutions from American Shipping & Logistics." },
      { property: "og:title", content: "Services — American Shipping & Logistics" },
      { property: "og:description", content: "Everything American Shipping & Logistics ships, from letters to full truckloads." },
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
      <section className="relative overflow-hidden bg-brand">
        <img
          src={servicesHero}
          alt=""
          width={1600}
          height={700}
          loading="eager"
          fetchPriority="high"
          decoding="async"
          sizes="100vw"
          srcSet={`${servicesHero} 1600w`}
          className="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-40 mix-blend-multiply"
        />
        <div className="pointer-events-none absolute inset-0 bg-gradient-to-r from-brand via-brand/85 to-brand/40" />
        <div className="container-x relative pb-16 pt-16 text-brand-foreground md:pt-24">
          <p className="font-mono text-xs uppercase tracking-widest">Services</p>
          <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
            Whatever you're shipping, we route it.
          </h1>
          <p className="mt-5 max-w-2xl text-lg text-brand-foreground/85">
            Eight service lines built on one global network — from same-day metro drops to
            transcontinental ocean freight. Every service is backed by end-to-end tracking,
            proactive alerts, and a 24/7 operations desk in your local language.
          </p>
        </div>
      </section>

      <section className="container-x grid gap-8 py-16 md:grid-cols-2 md:items-center">
        <div>
          <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
            One network. Every mode.
          </p>
          <h2 className="mt-2 font-display text-3xl font-bold md:text-4xl">
            Built for shippers who can't afford to guess.
          </h2>
          <p className="mt-4 text-muted-foreground">
            American Shipping & Logistics operates its own aircraft, its own trucks, its own sortation hubs and its own
            last-mile fleets. That's why we can guarantee times other carriers can only estimate —
            and why our on-time performance leads the industry across every mode we offer.
          </p>
          <ul className="mt-6 space-y-3 text-sm">
            {[
              "Real-time visibility from pickup to proof-of-delivery",
              "Dedicated account manager on business accounts",
              "API and pre-built integrations for every major platform",
              "Sustainable-fuel options on every international shipment",
            ].map((f) => (
              <li key={f} className="flex items-start gap-3">
                <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-accent" />
                <span>{f}</span>
              </li>
            ))}
          </ul>
        </div>
        <div className="relative overflow-hidden rounded-sm border border-border">
          <img
            src={warehouseOps}
            alt="American Shipping & Logistics sortation hub with automated forklifts"
            width={1600}
            height={900}
            loading="lazy"
            decoding="async"
            sizes="(min-width: 768px) 50vw, 100vw"
            srcSet={`${warehouseOps} 1600w`}
            className="h-full w-full object-cover"
          />
        </div>
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
