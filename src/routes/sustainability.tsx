import { createFileRoute, Link } from "@tanstack/react-router";
import { Leaf, Zap, Recycle, Wind } from "lucide-react";
import sustainabilityHero from "@/assets/sustainability-hero.jpg";

export const Route = createFileRoute("/sustainability")({
  head: () => ({
    meta: [
      { title: "Sustainability — Voltra Logistics" },
      { name: "description", content: "Net-zero by 2050. Voltra's roadmap to greener logistics — electric fleets, sustainable fuels, and carbon-neutral shipping." },
      { property: "og:title", content: "Sustainability at Voltra" },
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
      <section className="container-x pt-16 pb-14 md:pt-24">
        <p className="font-mono text-xs uppercase tracking-widest text-brand">Sustainability</p>
        <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
          Net-zero logistics by <span className="text-brand">2050.</span>
        </h1>
        <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
          We're investing €7 billion by 2030 in clean fuels, electrified fleets, and
          climate-neutral buildings. Here's how we get there.
        </p>
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
        <div className="rounded-3xl border border-brand/30 bg-gradient-to-b from-brand/10 to-surface p-10 md:p-16">
          <h2 className="font-display text-3xl font-bold md:text-4xl">Ship greener today</h2>
          <p className="mt-3 max-w-2xl text-muted-foreground">
            Add GoGreen Plus to any Voltra shipment and reduce your Scope 3 emissions with
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
