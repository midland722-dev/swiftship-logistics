import { Link } from "@tanstack/react-router";
import { Package } from "lucide-react";

export function SiteFooter() {
  return (
    <footer className="mt-24 border-t border-border/60 bg-surface/40">
      <div className="container-x grid gap-10 py-14 md:grid-cols-4">
        <div className="space-y-3">
          <div className="flex items-center gap-2 font-display text-lg font-bold">
            <span className="grid h-8 w-8 place-items-center rounded-lg bg-brand text-brand-foreground">
              <Package className="h-4 w-4" strokeWidth={2.5} />
            </span>
            Voltra
          </div>
          <p className="text-sm text-muted-foreground">
            Global logistics, on-demand. Ship parcels and freight to 220+ countries.
          </p>
        </div>
        <FooterCol
          title="Ship"
          links={[
            ["Get a quote", "/quote"],
            ["Track a shipment", "/track"],
            ["Services", "/services"],
            ["Pricing", "/pricing"],
          ]}
        />
        <FooterCol
          title="Company"
          links={[
            ["About", "/services"],
            ["Contact", "/contact"],
            ["Careers", "/contact"],
            ["Press", "/contact"],
          ]}
        />
        <FooterCol
          title="Support"
          links={[
            ["Help center", "/contact"],
            ["Delivery preferences", "/track"],
            ["API docs", "/services"],
            ["Status", "/contact"],
          ]}
        />
      </div>
      <div className="border-t border-border/60">
        <div className="container-x flex flex-col items-start justify-between gap-2 py-6 text-xs text-muted-foreground sm:flex-row sm:items-center">
          <p>© {new Date().getFullYear()} Voltra Logistics. All rights reserved.</p>
          <p>Built for a world that moves fast.</p>
        </div>
      </div>
    </footer>
  );
}

function FooterCol({ title, links }: { title: string; links: [string, string][] }) {
  return (
    <div>
      <h4 className="mb-3 text-sm font-semibold text-foreground">{title}</h4>
      <ul className="space-y-2 text-sm text-muted-foreground">
        {links.map(([label, href]) => (
          <li key={label}>
            <Link to={href} className="hover:text-brand">
              {label}
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
