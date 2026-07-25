import { createFileRoute, Link } from "@tanstack/react-router";
import { Globe, Users, Award, Rocket } from "lucide-react";
import teamPortrait from "@/assets/team-portrait.jpg";
import aboutHeritage from "@/assets/about-heritage.jpg";

export const Route = createFileRoute("/about")({
  head: () => ({
    meta: [
      { title: "About — Voltra Logistics" },
      { name: "description", content: "Voltra connects people and businesses across 220+ countries with reliable logistics, courier, and freight services." },
      { property: "og:title", content: "About Voltra Logistics" },
      { property: "og:description", content: "Our story, our network, and the people moving your world." },
      { property: "og:url", content: "/about" },
    ],
    links: [{ rel: "canonical", href: "/about" }],
  }),
  component: AboutPage,
});

const stats = [
  { icon: Globe, value: "220+", label: "Countries served" },
  { icon: Users, value: "128k", label: "Team members" },
  { icon: Award, value: "1969", label: "Founded" },
  { icon: Rocket, value: "1.9B", label: "Shipments per year" },
];

function AboutPage() {
  return (
    <>
      <section className="container-x pt-16 pb-14 md:pt-24">
        <p className="font-mono text-xs uppercase tracking-widest text-brand">About Voltra</p>
        <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
          Excellence. Simply delivered.
        </h1>
        <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
          For over five decades Voltra has connected people, businesses and communities. From
          the first international courier flight to today's AI-optimized global routing, we
          keep supply chains moving — reliably, sustainably, everywhere.
        </p>
      </section>

      <section className="container-x grid gap-4 pb-16 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map(({ icon: Icon, value, label }) => (
          <div key={label} className="rounded-2xl border border-border bg-surface/60 p-6">
            <Icon className="h-6 w-6 text-brand" />
            <div className="mt-4 font-display text-3xl font-bold">{value}</div>
            <div className="text-sm text-muted-foreground">{label}</div>
          </div>
        ))}
      </section>

      <section className="container-x grid gap-10 pb-24 md:grid-cols-2">
        <div>
          <h2 className="font-display text-3xl font-bold">Our purpose</h2>
          <p className="mt-4 text-muted-foreground">
            Connecting people. Improving lives. We believe global trade is a force for good —
            and that logistics done well makes the world smaller, fairer, and more resilient.
          </p>
        </div>
        <div>
          <h2 className="font-display text-3xl font-bold">Our promise</h2>
          <p className="mt-4 text-muted-foreground">
            On time. In full. Every time. We measure ourselves against the highest standard in
            the industry, and publish our on-time performance every quarter.
          </p>
        </div>
      </section>

      <section className="container-x pb-24">
        <div className="rounded-3xl border border-border bg-surface/60 p-10 text-center md:p-16">
          <h2 className="font-display text-3xl font-bold md:text-4xl">Work with our team</h2>
          <p className="mx-auto mt-3 max-w-xl text-muted-foreground">
            From same-day couriers to multi-modal freight, our specialists design the right
            solution for your business.
          </p>
          <div className="mt-6 flex flex-wrap justify-center gap-3">
            <Link to="/contact" className="rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
              Contact sales
            </Link>
            <Link to="/careers" className="rounded-sm border border-border px-5 py-3 text-sm font-semibold hover:bg-surface">
              See open roles
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
