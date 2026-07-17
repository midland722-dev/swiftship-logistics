import { Link } from "@tanstack/react-router";
import { useState } from "react";
import { Menu, X, Package } from "lucide-react";

const nav = [
  { to: "/services", label: "Services" },
  { to: "/track", label: "Track" },
  { to: "/quote", label: "Get a quote" },
  { to: "/pricing", label: "Pricing" },
  { to: "/about", label: "About" },
  { to: "/help", label: "Help" },
  { to: "/contact", label: "Contact" },
] as const;


export function SiteHeader() {
  const [open, setOpen] = useState(false);

  return (
    <header className="sticky top-0 z-40 border-b border-border/60 bg-background/70 backdrop-blur-xl">
      <div className="container-x flex h-16 items-center justify-between gap-6">
        <Link to="/" className="flex items-center gap-2 font-display text-lg font-bold tracking-tight">
          <span className="grid h-8 w-8 place-items-center rounded-sm bg-brand text-accent">
            <Package className="h-4 w-4" strokeWidth={2.75} />
          </span>
          <span>Voltra</span>
        </Link>


        <nav className="hidden items-center gap-1 md:flex">
          {nav.map((n) => (
            <Link
              key={n.to}
              to={n.to}
              className="rounded-md px-3 py-2 text-sm text-muted-foreground transition hover:bg-surface hover:text-foreground"
              activeProps={{ className: "text-foreground bg-surface" }}
            >
              {n.label}
            </Link>
          ))}
        </nav>

        <div className="hidden items-center gap-2 md:flex">
          <Link
            to="/track"
            className="rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground transition hover:opacity-90"
          >
            Track
          </Link>
        </div>


        <button
          onClick={() => setOpen((v) => !v)}
          className="grid h-10 w-10 place-items-center rounded-md md:hidden"
          aria-label={open ? "Close menu" : "Open menu"}
        >
          {open ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
        </button>
      </div>

      {open && (
        <div className="border-t border-border/60 bg-background md:hidden">
          <nav className="container-x flex flex-col gap-1 py-3">
            {nav.map((n) => (
              <Link
                key={n.to}
                to={n.to}
                onClick={() => setOpen(false)}
                className="rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-surface hover:text-foreground"
                activeProps={{ className: "text-foreground bg-surface" }}
              >
                {n.label}
              </Link>
            ))}
          </nav>
        </div>
      )}

    </header>
  );
}
