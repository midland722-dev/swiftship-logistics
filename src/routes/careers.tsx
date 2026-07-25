import { createFileRoute, Link } from "@tanstack/react-router";
import { MapPin, Briefcase, Heart, GraduationCap, Globe } from "lucide-react";
import careersHero from "@/assets/careers-hero.jpg";

export const Route = createFileRoute("/careers")({
  head: () => ({
    meta: [
      { title: "Careers — Voltra Logistics" },
      { name: "description", content: "Join 128,000 Voltra colleagues in 220+ countries. Explore open roles in operations, engineering, and corporate teams." },
      { property: "og:title", content: "Careers at Voltra" },
      { property: "og:description", content: "Open roles across engineering, operations, and corporate teams worldwide." },
      { property: "og:url", content: "/careers" },
    ],
    links: [{ rel: "canonical", href: "/careers" }],
  }),
  component: CareersPage,
});

const roles = [
  { title: "Senior Backend Engineer, Tracking Platform", team: "Engineering", location: "Berlin, DE" },
  { title: "Operations Manager, Air Freight", team: "Operations", location: "Singapore, SG" },
  { title: "Product Designer, Shipper Experience", team: "Design", location: "Remote (EMEA)" },
  { title: "Data Scientist, Route Optimization", team: "Data", location: "Hamburg, DE" },
  { title: "Warehouse Team Lead", team: "Operations", location: "Dallas, TX" },
  { title: "Sustainability Program Manager", team: "Corporate", location: "London, UK" },
];

function CareersPage() {
  return (
    <>
      <section className="container-x pt-16 pb-14 md:pt-24">
        <p className="font-mono text-xs uppercase tracking-widest text-brand">Careers</p>
        <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
          Move the world with us.
        </h1>
        <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
          From couriers to coders, 128,000 Voltra colleagues keep global trade moving. Find
          your role — and grow a career that spans continents.
        </p>
      </section>

      <section className="container-x pb-24">
        <h2 className="font-display text-2xl font-bold">Open roles</h2>
        <div className="mt-6 divide-y divide-border rounded-2xl border border-border bg-surface/60">
          {roles.map((r) => (
            <div key={r.title} className="flex flex-col gap-3 p-5 md:flex-row md:items-center md:justify-between">
              <div>
                <div className="font-semibold">{r.title}</div>
                <div className="mt-1 flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
                  <span className="inline-flex items-center gap-1.5"><Briefcase className="h-3.5 w-3.5" />{r.team}</span>
                  <span className="inline-flex items-center gap-1.5"><MapPin className="h-3.5 w-3.5" />{r.location}</span>
                </div>
              </div>
              <Link to="/contact" className="rounded-sm border border-border px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-background">
                Apply
              </Link>
            </div>
          ))}
        </div>
      </section>
    </>
  );
}
