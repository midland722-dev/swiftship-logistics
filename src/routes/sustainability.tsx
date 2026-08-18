import { createFileRoute, Link } from "@tanstack/react-router";
import { Leaf, Zap, Recycle, Wind } from "lucide-react";
import sustainabilityHero from "@/assets/sustainability-hero.jpg";

export const Route = createFileRoute("/sustainability")({
  head: () => ({
    meta: [
      { title: "Sustainability — American Shipping & Logistics" },
      { name: "description", content: "Net-zero by 2050. American Shipping & Logistics's roadmap to greener logistics — electric fleets, sustainable fuels, and carbon-neutral shipping." },
      { property: "og:title", content: "Sustainability at American Shipping & Logistics" },
      { property: "og:description", content: "Our path to net-zero logistics by 2050." },
      { property: "og:url", content: "/sustainability" },
    ],
    links: [{ rel: "canonical", href: "/sustainability" }],
  }),
  component: SustainabilityPage,
});

const pillars = [
  { icon: Zap, title: "Electric fleet", desc: "60% of last-mile vehicles electric by 2030. Already 27,000 EVs on the road today." },
  { icon: Wind, title: "Sustainable aviation fuel", desc: "GoGreen Plus lets shippers cut air-freight emissions with certified SAF." },
  { icon: Recycle, title: "Circular packaging", desc: "Reusable totes and 100% recyclable poly-mailers across our eCommerce network." },
  { icon: Leaf, title: "Carbon insetting", desc: "Direct emission reductions in your own supply chain — not offsets on a spreadsheet." },
];

function SustainabilityPage() {
  return (
    <>
      <section className="relative overflow-hidden">
        <img
          src={sustainabilityHero}
          alt=""
          width={1600}
          height={700}
          loading="eager"
          fetchPriority="high"
          decoding="async"
          sizes="100vw"
          srcSet={`${sustainabilityHero} 1600w`}
          className="pointer-events-none absolute inset-0 h-full w-full object-cover"
        />
        <div className="pointer-events-none absolute inset-0 bg-linear-to-r from-background via-background/90 to-background/40" />
        <div className="container-x relative pt-16 pb-16 md:pt-24 md:pb-24">
          <p className="font-mono text-xs uppercase tracking-widest text-brand">Sustainability</p>
          <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
            Net-zero logistics by <span className="text-brand">2050.</span>
          </h1>
          <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
            We're investing €7 billion by 2030 in clean fuels, electrified fleets, and
            climate-neutral buildings. Every American Shipping & Logistics shipment is measured, reported, and
            reducible — because you can't decarbonise what you don't count.
          </p>
        </div>
      </section>

      <section className="container-x grid gap-6 py-16 md:grid-cols-3">
        {[
          { n: "27,000", label: "Electric vehicles deployed" },
          { n: "€7B", label: "Green investment by 2030" },
          { n: "38%", label: "Scope 1&2 emissions reduced since 2020" },
        ].map((s) => (
          <div key={s.label} className="rounded-2xl border border-border bg-background p-8">
            <div className="font-display text-4xl font-bold text-brand md:text-5xl">{s.n}</div>
            <div className="mt-2 text-sm text-muted-foreground">{s.label}</div>
          </div>
        ))}
      </section>

      <section className="container-x grid gap-4 pb-16 md:grid-cols-2">
        {pillars.map(({ icon: Icon, title, desc }) => (
          <div key={title} className="rounded-2xl border border-border bg-surface/60 p-6">
            <div className="grid h-11 w-11 place-items-center rounded-xl bg-background text-brand">
              <Icon className="h-5 w-5" />
            </div>
            <h2 className="mt-5 text-lg font-semibold">{title}</h2>
            <p className="mt-2 text-sm text-muted-foreground">{desc}</p>
          </div>
        ))}
      </section>

      <section className="container-x pb-24">
        <div className="rounded-3xl border border-brand/30 bg-linear-to-b from-brand/10 to-surface p-10 md:p-16">
          <h2 className="font-display text-3xl font-bold md:text-4xl">Ship greener today</h2>
          <p className="mt-3 max-w-2xl text-muted-foreground">
            Add GoGreen Plus to any American Shipping & Logistics shipment and reduce your Scope 3 emissions with
            certified sustainable fuel — auditable, additional, and reported.
          </p>
          <Link to="/quote" className="mt-6 inline-block rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
            Quote a green shipment
          </Link>
        </div>
      </section>
    </>
  );
}
