import { createFileRoute, Link } from "@tanstack/react-router";
import { Calendar } from "lucide-react";
import newsHero from "@/assets/news-hero.jpg";

export const Route = createFileRoute("/news")({
  head: () => ({
    meta: [
      { title: "Newsroom — American Shipping & Logistics" },
      { name: "description", content: "Latest press releases, network updates, and service bulletins from American Shipping & Logistics." },
      { property: "og:title", content: "Newsroom — American Shipping & Logistics" },
      { property: "og:description", content: "Press releases and service bulletins." },
      { property: "og:url", content: "/news" },
    ],
    links: [{ rel: "canonical", href: "/news" }],
  }),
  component: NewsPage,
});

const posts = [
  { date: "Jan 12, 2026", title: "American Shipping & Logistics opens new automated hub in Leipzig", tag: "Network" },
  { date: "Dec 04, 2025", title: "1,200 additional electric vans deployed across EU cities", tag: "Sustainability" },
  { date: "Nov 18, 2025", title: "American Shipping & Logistics Q3 results: 8.7% year-over-year revenue growth", tag: "Investors" },
  { date: "Oct 02, 2025", title: "New Trans-Pacific express route: LAX ↔ HKG in 18 hours", tag: "Service" },
  { date: "Sep 15, 2025", title: "GoGreen Plus expanded to all international parcels", tag: "Sustainability" },
];

function NewsPage() {
  return (
    <>
      <section className="container-x pt-16 pb-10 md:pt-24">
        <p className="font-mono text-xs uppercase tracking-widest text-brand">Newsroom</p>
        <h1 className="mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl">
          The latest from American Shipping & Logistics.
        </h1>
        <p className="mt-5 max-w-2xl text-lg text-muted-foreground">
          Press releases, service bulletins, and behind-the-scenes stories from the world's
          largest logistics network.
        </p>
      </section>

      <section className="container-x pb-14">
        <div className="overflow-hidden rounded-2xl border border-border">
          <img
            src={newsHero}
            alt="American Shipping & Logistics cargo plane being loaded at dawn"
            width={1600}
            height={700}
            loading="lazy"
            decoding="async"
            sizes="100vw"
            srcSet={`${newsHero} 1600w`}
            className="h-full w-full object-cover"
          />
        </div>
      </section>

      <section className="container-x pb-24">
        <div className="divide-y divide-border rounded-2xl border border-border bg-surface/60">
          {posts.map((p) => (
            <article key={p.title} className="flex flex-col gap-3 p-6 md:flex-row md:items-center md:justify-between">
              <div>
                <div className="flex items-center gap-3 text-xs text-muted-foreground">
                  <span className="inline-flex items-center gap-1.5"><Calendar className="h-3.5 w-3.5" />{p.date}</span>
                  <span className="rounded-full bg-brand/15 px-2 py-0.5 font-semibold text-brand">{p.tag}</span>
                </div>
                <h2 className="mt-2 text-lg font-semibold">{p.title}</h2>
              </div>
              <Link to="/contact" className="text-xs font-bold uppercase tracking-wider text-brand hover:underline">
                Read more →
              </Link>
            </article>
          ))}
        </div>
      </section>
    </>
  );
}
