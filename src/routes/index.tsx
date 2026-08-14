import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import {
  ArrowRight,
  Search,
  Plane,
  Truck,
  Ship,
  Warehouse,
  Package,
  FileText,
  Building2,
  Leaf,
  Lightbulb,
  Newspaper,
  Quote,
  Star,
  TrendingUp,
  Clock,
  Globe2,
} from "lucide-react";
import heroCourier from "@/assets/hero-courier.jpg";
import expressHandoff from "@/assets/express-handoff.jpg";
import cargoPort from "@/assets/cargo-port.jpg";
import sustainabilityVan from "@/assets/sustainability-van.jpg";
import innovationData from "@/assets/innovation-data.jpg";
import globalPlanes from "@/assets/global-planes.jpg";
import warehouseOps from "@/assets/warehouse-ops.jpg";
import teamPortrait from "@/assets/team-portrait.jpg";
import testimonialCustomer from "@/assets/testimonial-customer.jpg";

export const Route = createFileRoute("/")({
  head: () => ({
    meta: [
      { title: "American Shipping & Logistics — Excellence. Simply delivered." },
      {
        name: "description",
        content:
          "Ship, track, and quote parcels and freight to 220+ countries. American Shipping & Logistics — global logistics, simply delivered.",
      },
      { property: "og:title", content: "American Shipping & Logistics — Excellence. Simply delivered." },
      { property: "og:description", content: "Global logistics, simply delivered." },
    ],
    links: [{ rel: "canonical", href: "/" }],
  }),
  component: HomePage,
});

function HomePage() {
  return (
    <>
      <Hero />
      <QuickActions />
      <Bulletin />
      <Divisions />
      <BusinessSplit />
      <NetworkStrip />
      <Testimonials />
      <CaseStudies />
      <Updates />
      <Highlights />
      <FinalCTA />
    </>
  );
}

/* ---------- Hero: yellow band with tracking search ---------- */
function Hero() {
  const navigate = useNavigate();
  const [tracking, setTracking] = useState("");

  return (
    <section className="relative overflow-hidden bg-brand">
      <img
        src={heroCourier}
        alt=""
        width={1600}
        height={900}
        loading="eager"
        decoding="async"
        fetchPriority="high"
        sizes="100vw"
        srcSet={`${heroCourier} 1600w`}
        className="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-70 mix-blend-multiply"
      />
      <div className="pointer-events-none absolute inset-0 bg-gradient-to-r from-brand via-brand/85 to-brand/30" />
      <div className="pointer-events-none absolute inset-0 grid-lines opacity-30" />
      <div className="container-x relative grid gap-10 pb-14 pt-14 md:pt-20 lg:grid-cols-[1.2fr_1fr] lg:gap-12 lg:pb-20">
        <div className="flex flex-col justify-center text-brand-foreground">
          <h1 className="font-display text-5xl font-bold leading-[1.02] tracking-tight md:text-6xl lg:text-7xl">
            Excellence.
            <br />
            Simply <span className="text-accent">delivered.</span>
          </h1>
          <p className="mt-5 max-w-xl text-lg text-brand-foreground/80">
            Track a shipment, get an instant quote, or book a pickup — all in one place.
          </p>
        </div>

        {/* Tracking card */}
        <div className="relative">
          <div className="rounded-sm border-2 border-brand-foreground/10 bg-background p-6 shadow-2xl md:p-7">
            <h2 className="font-display text-xl font-bold">Track Your Shipment</h2>
            <p className="mt-1 text-sm text-muted-foreground">Enter your tracking number(s)</p>
            <form
              onSubmit={(e) => {
                e.preventDefault();
                navigate({ to: "/track", search: { id: tracking || "VLT-0000000" } });
              }}
              className="mt-4"
            >
              <div className="flex items-center gap-2 rounded-sm border-2 border-border bg-background px-3 focus-within:border-accent">
                <Search className="h-4 w-4 text-muted-foreground" />
                <input
                  value={tracking}
                  onChange={(e) => setTracking(e.target.value)}
                  placeholder="e.g. VLT-4820193"
                  className="w-full bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground/70"
                />
              </div>
              <button
                type="submit"
                className="mt-3 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90"
              >
                Track
              </button>
              <p className="mt-3 text-xs text-muted-foreground">
                Try{" "}
                <button
                  type="button"
                  onClick={() => setTracking("VLT-4820193")}
                  className="font-semibold text-accent underline underline-offset-2"
                >
                  VLT-4820193
                </button>{" "}
                for a live example.
              </p>
            </form>
          </div>
        </div>
      </div>
    </section>
  );
}

/* ---------- Quick actions: 3 tiles ---------- */
function QuickActions() {
  const actions = [
    {
      icon: Package,
      title: "Ship Now",
      desc: "Find the right service for your parcel.",
      to: "/quote",
    },
    {
      icon: FileText,
      title: "Get a Quote",
      desc: "Estimate cost, share and compare.",
      to: "/quote",
    },
    {
      icon: Building2,
      title: "Request a Business Account",
      desc: "Shipping regularly? Unlock volume discounts.",
      to: "/contact",
    },
  ];
  return (
    <section className="border-b border-border bg-background">
      <div className="container-x grid gap-0 divide-y divide-border md:grid-cols-3 md:divide-x md:divide-y-0">
        {actions.map(({ icon: Icon, title, desc, to }) => (
          <Link
            key={title}
            to={to}
            className="group flex items-start gap-4 p-6 transition hover:bg-surface md:p-8"
          >
            <div className="grid h-12 w-12 shrink-0 place-items-center rounded-sm bg-brand text-brand-foreground">
              <Icon className="h-5 w-5" strokeWidth={2.5} />
            </div>
            <div className="min-w-0">
              <div className="flex items-center gap-2 font-display text-lg font-bold group-hover:text-accent">
                {title}
                <ArrowRight className="h-4 w-4 -translate-x-1 opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100" />
              </div>
              <p className="mt-1 text-sm text-muted-foreground">{desc}</p>
            </div>
          </Link>
        ))}
      </div>
    </section>
  );
}

/* ---------- Bulletin banner ---------- */
function Bulletin() {
  return (
    <section className="bg-surface">
      <div className="container-x grid gap-6 py-14 md:grid-cols-[1.4fr_1fr] md:items-center md:py-16">
        <div>
          <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
            Global trade update
          </p>
          <h2 className="mt-2 font-display text-3xl font-bold md:text-4xl">
            Navigating the latest tariff developments.
          </h2>
          <p className="mt-3 max-w-xl text-muted-foreground">
            Global trade is becoming increasingly complex as new tariffs and reciprocal measures
            emerge across countries and industries. American Shipping & Logistics is committed to helping you navigate.
          </p>
          <Link
            to="/services"
            className="mt-6 inline-flex items-center gap-2 rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90"
          >
            Explore our solutions <ArrowRight className="h-4 w-4" />
          </Link>
        </div>
        <div className="relative hidden overflow-hidden rounded-sm border border-border md:block">
          <img src={sustainabilityVan} alt="American Shipping & Logistics delivery driver in a yellow van" width={1000} height={800} loading="lazy" decoding="async" sizes="(min-width: 768px) 40vw, 100vw" srcSet={`${sustainabilityVan} 1000w`} className="h-full w-full object-cover" />
        </div>
      </div>
    </section>
  );
}

/* ---------- Document & Parcel Shipping — divisions ---------- */
function Divisions() {
  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
        Document and Parcel Shipping
      </p>
      <h2 className="mt-2 max-w-2xl font-display text-3xl font-bold md:text-4xl">
        For all shippers.
      </h2>
      <p className="mt-3 max-w-2xl text-muted-foreground">
        Learn about American Shipping & Logistics Express — the undisputed global leader in international express shipping.
      </p>

      <div className="mt-10 grid gap-6 md:grid-cols-3">
        <div className="relative overflow-hidden rounded-sm border border-border md:col-span-1">
          <img src={expressHandoff} alt="American Shipping & Logistics Express package handoff" width={1200} height={900} loading="lazy" decoding="async" sizes="(min-width: 768px) 33vw, 100vw" srcSet={`${expressHandoff} 1200w`} className="h-full w-full object-cover" />
        </div>

        <div className="flex flex-col gap-5 rounded-sm border border-border bg-surface p-8">
          <div className="grid h-12 w-12 place-items-center rounded-sm bg-brand text-brand-foreground">
            <Plane className="h-6 w-6" strokeWidth={2.5} />
          </div>
          <h3 className="font-display text-2xl font-bold">American Shipping & Logistics Express</h3>
          <ul className="grid grid-cols-2 gap-y-2 text-sm text-muted-foreground">
            {[
              "Next possible business day",
              "Flexible import/export",
              "Tailored business solutions",
              "Wide variety of options",
            ].map((f) => (
              <li key={f} className="flex items-start gap-2">
                <span className="mt-1.5 h-1 w-1 rounded-full bg-accent" />
                {f}
              </li>
            ))}
          </ul>
          <Link
            to="/services"
            className="mt-auto inline-flex w-fit items-center gap-2 rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90"
          >
            Explore American Shipping & Logistics Express <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        <div className="flex flex-col justify-center gap-4 rounded-sm bg-brand p-8 text-brand-foreground">
          <div className="text-xs font-bold uppercase tracking-widest">On-time performance</div>
          <div className="font-display text-6xl font-bold">99.2%</div>
          <p className="max-w-sm text-brand-foreground/80">
            of American Shipping & Logistics Express shipments arrive on or before their promised time — measured across
            220+ countries, every day.
          </p>
          <div className="mt-4 grid grid-cols-3 gap-4 border-t border-brand-foreground/20 pt-4 text-sm">
            <Stat n="6.1M" label="Parcels / day" />
            <Stat n="4,500" label="Service points" />
            <Stat n="47k" label="Vehicles" />
          </div>
        </div>
      </div>
    </section>
  );
}

function Stat({ n, label }: { n: string; label: string }) {
  return (
    <div>
      <div className="font-display text-2xl font-bold">{n}</div>
      <div className="text-xs text-brand-foreground/70">{label}</div>
    </div>
  );
}

/* ---------- Business shipping split ---------- */
function BusinessSplit() {
  const rows = [
    {
      tag: "Retailer or Volume Shipping",
      title: "Business only.",
      desc: "Two divisions offering reliable business shipping for e-commerce, supplier and manufacturing.",
      image: expressHandoff,
      alt: "Business shipping partners exchanging a parcel",
      items: [
        { icon: Truck, title: "American Shipping & Logistics eCommerce", desc: "Domestic and international residential delivery and returns." },
        { icon: Plane, title: "American Shipping & Logistics Express", desc: "Fast, door-to-door, courier delivered to 220+ countries." },
      ],
    },
    {
      tag: "Cargo Shipping",
      title: "Global Forwarding.",
      desc: "Discover shipping and logistics service options from American Shipping & Logistics Global Forwarding.",
      image: cargoPort,
      alt: "Freight workers at a cargo shipping port",
      items: [
        { icon: Plane, title: "Air Freight", desc: "Charter, consolidated and time-critical air cargo." },
        { icon: Ship, title: "Ocean Freight", desc: "FCL, LCL, and specialised container services." },
      ],
    },
    {
      tag: "Enterprise Logistics Services",
      title: "American Shipping & Logistics Supply Chain.",
      desc: "Find out how American Shipping & Logistics Supply Chain can revolutionize your business as a 3PL provider.",
      image: heroCourier,
      alt: "Warehouse operations",
      items: [
        { icon: Warehouse, title: "Warehousing", desc: "Flexible storage, pick, pack, and kitting." },
        { icon: Truck, title: "Transport & Packaging", desc: "Distribution, service logistics and more." },
      ],
    },
  ];

  return (
    <section className="bg-surface">
      <div className="container-x space-y-10 py-16 md:py-20">
        {rows.map((row, idx) => (
          <div
            key={row.tag}
            className={`grid overflow-hidden rounded-sm border border-border bg-background lg:grid-cols-2 ${
              idx % 2 === 1 ? "lg:grid-flow-dense" : ""
            }`}
          >
            <div className={`relative min-h-[240px] ${idx % 2 === 1 ? "lg:col-start-2" : ""}`}>
              <img src={row.image} alt={row.alt} width={1200} height={900} loading="lazy" decoding="async" sizes="(min-width: 1024px) 50vw, 100vw" srcSet={`${row.image} 1200w`} className="absolute inset-0 h-full w-full object-cover" />
            </div>
            <div className="p-8 md:p-10">
              <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
                {row.tag}
              </p>
              <h3 className="mt-2 font-display text-2xl font-bold md:text-3xl">{row.title}</h3>
              <p className="mt-3 max-w-md text-muted-foreground">{row.desc}</p>

              <div className="mt-6 grid gap-3 sm:grid-cols-2">
                {row.items.map(({ icon: Icon, title, desc }) => (
                  <div key={title} className="rounded-sm border-l-4 border-brand bg-surface p-4">
                    <Icon className="h-5 w-5 text-accent" strokeWidth={2} />
                    <div className="mt-2 font-display text-base font-bold">{title}</div>
                    <p className="mt-1 text-xs text-muted-foreground">{desc}</p>
                  </div>
                ))}
              </div>

              <Link
                to="/services"
                className="mt-6 inline-flex items-center gap-2 rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90"
              >
                Explore <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}

/* ---------- Important service updates ---------- */
function Updates() {
  const updates = [
    "American Shipping & Logistics Express will implement weekly fuel surcharge updates",
    "New customs rules for shipments under €150 from outside the EU",
    "Operational update: Middle East corridor",
    "Peak-season capacity now available across all lanes",
  ];
  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
        Important Service Updates
      </p>
      <h2 className="mt-2 font-display text-3xl font-bold md:text-4xl">
        Service bulletins.
      </h2>
      <p className="mt-2 text-muted-foreground">Keep up to date with news and alerts.</p>

      <ul className="mt-8 divide-y divide-border border-y border-border">
        {updates.map((u) => (
          <li key={u}>
            <a
              href="#"
              className="group flex items-center justify-between gap-4 py-5 hover:bg-surface"
            >
              <span className="font-medium">{u}</span>
              <ArrowRight className="h-4 w-4 shrink-0 text-accent transition group-hover:translate-x-1" />
            </a>
          </li>
        ))}
      </ul>
    </section>
  );
}

/* ---------- Highlight tiles ---------- */
function Highlights() {
  const tiles = [
    {
      icon: Leaf,
      title: "Sustainability",
      desc: "Low-carbon supply chains, GoGreen Plus, and net-zero operations by 2050.",
      image: sustainabilityVan,
      alt: "American Shipping & Logistics electric delivery van driver",
    },
    {
      icon: Lightbulb,
      title: "Innovation",
      desc: "Customer-centric innovation, trend research and next-generation solutions.",
      image: innovationData,
      alt: "Global data connectivity visualization",
    },
    {
      icon: Newspaper,
      title: "Global Connectedness",
      desc: "The American Shipping & Logistics 2026 report — the most comprehensive view of globalization available.",
      image: globalPlanes,
      alt: "Cargo planes at sunrise",
    },
  ];
  return (
    <section className="container-x pb-24">
      <div className="grid gap-6 md:grid-cols-3">
        {tiles.map(({ icon: Icon, title, desc, image, alt }) => (
          <a
            key={title}
            href="#"
            className="group flex flex-col overflow-hidden rounded-sm border border-border bg-background transition hover:border-accent"
          >
            <div className="relative aspect-[4/3] overflow-hidden">
              <img src={image} alt={alt} width={800} height={600} loading="lazy" decoding="async" sizes="(min-width: 768px) 33vw, 100vw" srcSet={`${image} 800w`} className="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
              <div className="absolute left-4 top-4 grid h-10 w-10 place-items-center rounded-sm bg-brand text-brand-foreground shadow-md">
                <Icon className="h-4 w-4" strokeWidth={2.5} />
              </div>
            </div>
            <div className="flex flex-1 flex-col p-6">
              <h3 className="font-display text-xl font-bold">{title}</h3>
              <p className="mt-2 flex-1 text-sm text-muted-foreground">{desc}</p>
               <span className="mt-6 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-accent">
                 Learn more about {title.toLowerCase()} <ArrowRight className="h-4 w-4 transition group-hover:translate-x-1" />
               </span>
            </div>
          </a>
        ))}
      </div>
    </section>
  );
}

/* ---------- Global network numbers strip ---------- */
function NetworkStrip() {
  const items = [
    { icon: Globe2, n: "220+", label: "Countries & territories" },
    { icon: Plane, n: "260", label: "Dedicated cargo aircraft" },
    { icon: Truck, n: "47,000", label: "Vehicles on the road" },
    { icon: Clock, n: "24/7", label: "Live shipment support" },
  ];
  return (
    <section className="border-y border-border bg-background">
      <div className="container-x grid grid-cols-2 gap-y-8 py-12 md:grid-cols-4">
        {items.map(({ icon: Icon, n, label }) => (
          <div key={label} className="flex items-start gap-3">
            <div className="grid h-11 w-11 shrink-0 place-items-center rounded-sm bg-brand text-brand-foreground">
              <Icon className="h-5 w-5" strokeWidth={2.5} />
            </div>
            <div>
              <div className="font-display text-2xl font-bold md:text-3xl">{n}</div>
              <div className="text-xs uppercase tracking-wider text-muted-foreground">{label}</div>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}

/* ---------- Testimonials ---------- */
function Testimonials() {
  const quotes = [
    {
      quote:
        "American Shipping & Logistics Express moved our seasonal launch from three-week freight into two-day delivery across 14 markets. Our customers noticed the day we switched.",
      author: "Ines Marchetti",
      role: "Head of Operations, Aera Studio",
    },
    {
      quote:
        "The visibility is what changed everything. Real-time events, proactive alerts, and a single dashboard for every carrier and every lane.",
      author: "Kenji Watanabe",
      role: "Supply Chain Director, Nomura Optics",
    },
    {
      quote:
        "We ship 40,000 orders a month. American Shipping & Logistics eCommerce handles pick, pack, ship and returns without a single missed cutoff.",
      author: "Priya Ramesh",
      role: "COO, Halo Wellness",
    },
  ];
  return (
    <section className="bg-background">
      <div className="container-x py-16 md:py-24">
        <div className="grid gap-10 lg:grid-cols-[1fr_1.4fr] lg:items-center">
          <div>
            <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
              Customer stories
            </p>
            <h2 className="mt-2 font-display text-3xl font-bold md:text-4xl">
              Trusted by 2.7 million businesses worldwide.
            </h2>
            <p className="mt-4 max-w-md text-muted-foreground">
              From independent Shopify stores to Fortune 100 manufacturers, the world's most
              demanding shippers rely on American Shipping & Logistics for on-time, in-full delivery — every day.
            </p>
            <div className="mt-6 flex items-center gap-2 text-sm">
              <div className="flex text-accent">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="h-4 w-4 fill-current" />
                ))}
              </div>
              <span className="font-semibold">4.8 / 5</span>
              <span className="text-muted-foreground">from 18,400+ verified reviews</span>
            </div>
          </div>
          <div className="grid gap-4 md:grid-cols-3">
            {quotes.map((q) => (
              <figure
                key={q.author}
                className="flex flex-col rounded-sm border border-border bg-surface/60 p-6"
              >
                <Quote className="h-6 w-6 text-brand" />
                <blockquote className="mt-4 flex-1 text-sm leading-relaxed">"{q.quote}"</blockquote>
                <figcaption className="mt-5 border-t border-border pt-4">
                  <div className="text-sm font-bold">{q.author}</div>
                  <div className="text-xs text-muted-foreground">{q.role}</div>
                </figcaption>
              </figure>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}

/* ---------- Case studies ---------- */
function CaseStudies() {
  const cases = [
    {
      tag: "eCommerce fulfilment",
      title: "Halo Wellness scales from 2 to 27 markets in 12 months",
      metric: "+312%",
      metricLabel: "cross-border order growth",
      image: warehouseOps,
      alt: "Automated fulfilment warehouse with yellow forklifts",
    },
    {
      tag: "Life sciences",
      title: "Cold-chain vaccine distribution across sub-Saharan Africa",
      metric: "99.97%",
      metricLabel: "temperature-controlled integrity",
      image: cargoPort,
      alt: "Container port operations at sunset",
    },
    {
      tag: "Automotive",
      title: "Just-in-sequence parts to 14 European assembly lines",
      metric: "0",
      metricLabel: "line stoppages in 18 months",
      image: testimonialCustomer,
      alt: "Business customer receiving a American Shipping & Logistics parcel",
    },
  ];
  return (
    <section className="bg-surface">
      <div className="container-x py-16 md:py-20">
        <div className="flex flex-wrap items-end justify-between gap-6">
          <div>
            <p className="font-mono text-xs font-bold uppercase tracking-widest text-accent">
              Case studies
            </p>
            <h2 className="mt-2 font-display text-3xl font-bold md:text-4xl">
              What partnership looks like.
            </h2>
          </div>
          <Link
            to="/services"
            className="inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-accent hover:underline"
          >
            View all case studies <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        <div className="mt-10 grid gap-6 md:grid-cols-3">
          {cases.map((c) => (
            <article
              key={c.title}
              className="group flex flex-col overflow-hidden rounded-sm border border-border bg-background transition hover:border-accent"
            >
              <div className="relative aspect-[4/3] overflow-hidden">
                <img
                  src={c.image}
                  alt={c.alt}
                  width={800}
                  height={600}
                  loading="lazy"
                  decoding="async"
                  sizes="(min-width: 768px) 33vw, 100vw"
                  srcSet={`${c.image} 800w`}
                  className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <span className="absolute left-4 top-4 rounded-sm bg-brand px-2 py-1 text-xs font-bold uppercase tracking-wider text-brand-foreground">
                  {c.tag}
                </span>
              </div>
              <div className="flex flex-1 flex-col gap-4 p-6">
                <h3 className="font-display text-lg font-bold leading-snug">{c.title}</h3>
                <div className="mt-auto flex items-end justify-between border-t border-border pt-4">
                  <div>
                    <div className="flex items-center gap-1 font-display text-3xl font-bold text-accent">
                      <TrendingUp className="h-5 w-5" />
                      {c.metric}
                    </div>
                    <div className="text-xs text-muted-foreground">{c.metricLabel}</div>
                  </div>
                  <ArrowRight className="h-5 w-5 text-brand transition group-hover:translate-x-1" />
                </div>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  );
}

/* ---------- Final CTA ---------- */
function FinalCTA() {
  return (
    <section className="bg-brand text-brand-foreground">
      <div className="container-x grid gap-8 py-14 md:grid-cols-[1.4fr_1fr] md:items-center md:py-20">
        <div>
          <h2 className="font-display text-3xl font-bold leading-tight md:text-5xl">
            Ready when you are.
          </h2>
          <p className="mt-3 max-w-xl text-brand-foreground/80">
            Open a business account in under five minutes and unlock volume pricing, dedicated
            account managers, and API access to every American Shipping & Logistics service.
          </p>
          <div className="mt-6 flex flex-wrap gap-3">
            <Link
              to="/quote"
              className="rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90"
            >
              Get a quote
            </Link>
            <Link
              to="/contact"
              className="rounded-sm border-2 border-brand-foreground px-5 py-3 text-sm font-bold uppercase tracking-wider hover:bg-brand-foreground hover:text-brand"
            >
              Talk to sales
            </Link>
          </div>
        </div>
        <div className="relative hidden overflow-hidden rounded-sm md:block">
          <img
            src={teamPortrait}
            alt="American Shipping & Logistics team members in yellow branded jackets"
            width={1200}
            height={700}
            loading="lazy"
            decoding="async"
            sizes="(min-width: 768px) 40vw, 100vw"
            srcSet={`${teamPortrait} 1200w`}
            className="h-full w-full object-cover"
          />
        </div>
      </div>
    </section>
  );
}
