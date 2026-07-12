import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import {
  ArrowRight,
  Search,
  Plane,
  Truck,
  Ship,
  Warehouse,
  Zap,
  Shield,
  Globe2,
  BarChart3,
} from "lucide-react";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "Voltra — Ship, track, and deliver anywhere" },
      {
        name: "description",
        content:
          "Instant shipping quotes, real-time tracking, and door-to-door delivery to 220+ countries. Voltra is logistics, redesigned.",
      },
      { property: "og:title", content: "Voltra — Ship, track, and deliver anywhere" },
      {
        property: "og:description",
        content: "Instant quotes, live tracking, worldwide delivery.",
      },
    ],
    links: [{ rel: "canonical", href: "/" }],
  }),
  component: HomePage,
});

function HomePage() {
  return (
    <>
      <Hero />
      <Metrics />
      <Services />
      <HowItWorks />
      <Coverage />
      <CTA />
    </>
  );
}

function Hero() {
  const navigate = useNavigate();
  const [tab, setTab] = useState<"track" | "quote">("track");
  const [tracking, setTracking] = useState("");

  return (
    <section className="relative overflow-hidden bg-brand">
      <div className="pointer-events-none absolute inset-0 grid-lines opacity-30" />
      <div className="container-x relative grid gap-12 pb-16 pt-16 md:pt-24 lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:pb-24">
        <div className="flex flex-col justify-center text-brand-foreground">
          <span className="inline-flex w-fit items-center gap-2 rounded-full border border-brand-foreground/20 bg-brand-foreground/5 px-3 py-1 text-xs font-semibold uppercase tracking-wider">
            <span className="h-1.5 w-1.5 rounded-full bg-accent" />
            Now delivering to 220+ countries
          </span>
          <h1 className="mt-5 font-display text-5xl font-bold leading-[1.02] tracking-tight md:text-6xl lg:text-7xl">
            Logistics that keeps
            <br />
            your world <span className="text-accent">moving.</span>
          </h1>
          <p className="mt-5 max-w-xl text-lg text-brand-foreground/80">
            Book a pickup, get a quote, and track every parcel — from a corner store shipment to
            transcontinental freight — in one place.
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link
              to="/quote"
              className="inline-flex items-center gap-2 rounded-md bg-accent px-5 py-3 text-sm font-semibold text-accent-foreground transition hover:opacity-90"
            >
              Get an instant quote <ArrowRight className="h-4 w-4" />
            </Link>
            <Link
              to="/services"
              className="inline-flex items-center gap-2 rounded-md border border-brand-foreground/30 bg-brand-foreground/5 px-5 py-3 text-sm font-medium hover:bg-brand-foreground/10"
            >
              Explore services
            </Link>
          </div>
        </div>


        {/* Action card */}
        <div className="relative">
          <div className="absolute -inset-3 -z-10 rounded-3xl bg-brand/10 blur-2xl" />
          <div className="rounded-2xl border border-border bg-surface/80 p-2 shadow-2xl backdrop-blur">
            <div className="flex gap-1 rounded-xl bg-background/60 p-1">
              {(["track", "quote"] as const).map((t) => (
                <button
                  key={t}
                  onClick={() => setTab(t)}
                  className={`flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition ${
                    tab === t
                      ? "bg-brand text-brand-foreground"
                      : "text-muted-foreground hover:text-foreground"
                  }`}
                >
                  {t === "track" ? "Track shipment" : "Get a quote"}
                </button>
              ))}
            </div>

            {tab === "track" ? (
              <form
                onSubmit={(e) => {
                  e.preventDefault();
                  navigate({ to: "/track", search: { id: tracking || "VLT-0000000" } });
                }}
                className="space-y-4 p-5"
              >
                <label className="block">
                  <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                    Tracking number
                  </span>
                  <div className="mt-2 flex items-center gap-2 rounded-lg border border-border bg-background px-3">
                    <Search className="h-4 w-4 text-muted-foreground" />
                    <input
                      value={tracking}
                      onChange={(e) => setTracking(e.target.value)}
                      placeholder="e.g. VLT-4820193"
                      className="w-full bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground/70"
                    />
                  </div>
                </label>
                <button
                  type="submit"
                  className="w-full rounded-lg bg-brand py-3 text-sm font-semibold text-brand-foreground hover:opacity-90"
                >
                  Track shipment
                </button>
                <p className="text-xs text-muted-foreground">
                  Try{" "}
                  <button
                    type="button"
                    onClick={() => setTracking("VLT-4820193")}
                    className="text-brand underline underline-offset-2"
                  >
                    VLT-4820193
                  </button>{" "}
                  for a live example.
                </p>
              </form>
            ) : (
              <div className="space-y-4 p-5">
                <div className="grid grid-cols-2 gap-3">
                  <MiniField label="From" placeholder="Berlin" />
                  <MiniField label="To" placeholder="Tokyo" />
                  <MiniField label="Weight (kg)" placeholder="2.4" />
                  <MiniField label="Service" placeholder="Express" />
                </div>
                <Link
                  to="/quote"
                  className="block w-full rounded-lg bg-brand py-3 text-center text-sm font-semibold text-brand-foreground hover:opacity-90"
                >
                  Calculate price
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}

function MiniField({ label, placeholder }: { label: string; placeholder: string }) {
  return (
    <label className="block">
      <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">
        {label}
      </span>
      <input
        placeholder={placeholder}
        className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none placeholder:text-muted-foreground/70 focus:border-brand"
      />
    </label>
  );
}

function Metrics() {
  const items = [
    ["220+", "Countries served"],
    ["6.1M", "Parcels delivered / day"],
    ["47k", "Vehicles on the road"],
    ["99.2%", "On-time rate"],
  ];
  return (
    <section className="border-y border-border/60 bg-surface/30">
      <div className="container-x grid grid-cols-2 gap-6 py-10 md:grid-cols-4">
        {items.map(([n, label]) => (
          <div key={label}>
            <div className="font-display text-3xl font-bold text-brand">{n}</div>
            <div className="mt-1 text-sm text-muted-foreground">{label}</div>
          </div>
        ))}
      </div>
    </section>
  );
}

function Services() {
  const services = [
    {
      icon: Plane,
      title: "Express Shipping",
      desc: "Next-day international delivery to 60+ major hubs.",
    },
    {
      icon: Truck,
      title: "Freight Services",
      desc: "Full and partial truckload, road freight across the continent.",
    },
    {
      icon: Ship,
      title: "eCommerce Logistics",
      desc: "Fulfilment, returns, and last-mile for online storefronts.",
    },
    {
      icon: Warehouse,
      title: "Supply Chain",
      desc: "Warehousing, distribution, and end-to-end supply chain design.",
    },
  ];
  return (
    <section id="services" className="container-x py-20 md:py-28">
      <div className="flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
          <p className="font-mono text-xs uppercase tracking-widest text-brand">Services</p>
          <h2 className="mt-2 font-display text-4xl font-bold md:text-5xl">
            One network. Every kind of shipment.
          </h2>
        </div>
        <Link to="/services" className="text-sm font-medium text-brand hover:underline">
          See all services →
        </Link>
      </div>
      <div className="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {services.map(({ icon: Icon, title, desc }) => (
          <div
            key={title}
            className="group relative overflow-hidden rounded-2xl border border-border bg-surface/60 p-6 transition hover:border-brand/50"
          >
            <div className="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
              <Icon className="h-5 w-5" />
            </div>
            <h3 className="mt-5 text-lg font-semibold">{title}</h3>
            <p className="mt-2 text-sm text-muted-foreground">{desc}</p>
            <div className="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-brand/50 to-transparent opacity-0 transition group-hover:opacity-100" />
          </div>
        ))}
      </div>
    </section>
  );
}

function HowItWorks() {
  const steps = [
    { icon: Zap, title: "Quote instantly", desc: "Price by weight, dimensions, and speed in seconds." },
    { icon: Shield, title: "Book with confidence", desc: "Insured labels, printable receipts, and QR pickups." },
    { icon: BarChart3, title: "Track in real time", desc: "Live location, ETA updates, and proof of delivery." },
  ];
  return (
    <section className="border-y border-border/60 bg-surface/20">
      <div className="container-x grid gap-10 py-20 md:grid-cols-3 md:py-24">
        {steps.map(({ icon: Icon, title, desc }, i) => (
          <div key={title} className="relative">
            <div className="font-mono text-xs text-muted-foreground">0{i + 1}</div>
            <div className="mt-2 flex items-center gap-3">
              <Icon className="h-6 w-6 text-brand" />
              <h3 className="font-display text-xl font-semibold">{title}</h3>
            </div>
            <p className="mt-3 text-sm text-muted-foreground">{desc}</p>
          </div>
        ))}
      </div>
    </section>
  );
}

function Coverage() {
  return (
    <section className="container-x py-20 md:py-28">
      <div className="grid gap-10 rounded-3xl border border-border bg-surface/60 p-8 md:grid-cols-2 md:p-14">
        <div>
          <Globe2 className="h-8 w-8 text-brand" />
          <h2 className="mt-4 font-display text-3xl font-bold md:text-4xl">
            A global network built for speed.
          </h2>
          <p className="mt-4 text-muted-foreground">
            220+ countries, 4,500 service points, and 47,000 vehicles working around the clock so
            your shipment moves without friction.
          </p>
          <div className="mt-6 flex gap-3">
            <Link to="/services" className="rounded-md bg-brand px-4 py-2.5 text-sm font-semibold text-brand-foreground hover:opacity-90">
              See services
            </Link>
            <Link to="/contact" className="rounded-md border border-border px-4 py-2.5 text-sm hover:bg-surface">
              Talk to sales
            </Link>
          </div>
        </div>
        <div className="grid grid-cols-2 gap-3">
          {[
            ["Europe", "1,240 hubs"],
            ["North America", "890 hubs"],
            ["Asia Pacific", "1,410 hubs"],
            ["LATAM", "560 hubs"],
            ["Africa", "220 hubs"],
            ["Middle East", "180 hubs"],
          ].map(([region, hubs]) => (
            <div key={region} className="rounded-xl border border-border bg-background/60 p-4">
              <div className="text-sm font-semibold">{region}</div>
              <div className="mt-1 text-xs text-muted-foreground">{hubs}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

function CTA() {
  return (
    <section className="container-x pb-24">
      <div className="relative overflow-hidden rounded-3xl border border-brand/40 bg-gradient-to-br from-brand/20 via-surface to-surface p-10 md:p-16">
        <div className="pointer-events-none absolute inset-0 grid-lines opacity-30" />
        <div className="relative max-w-2xl">
          <h2 className="font-display text-4xl font-bold md:text-5xl">
            Ready when you are.
          </h2>
          <p className="mt-4 text-muted-foreground">
            Open a Voltra account and get access to volume pricing, API integrations, and a
            dedicated logistics manager.
          </p>
          <div className="mt-6 flex flex-wrap gap-3">
            <Link
              to="/quote"
              className="rounded-md bg-brand px-5 py-3 text-sm font-semibold text-brand-foreground hover:opacity-90"
            >
              Ship a package
            </Link>
            <Link
              to="/contact"
              className="rounded-md border border-border bg-background/40 px-5 py-3 text-sm font-medium hover:bg-background/70"
            >
              Open a business account
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}
