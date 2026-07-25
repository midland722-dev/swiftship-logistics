import { Link } from "@tanstack/react-router";
import { useState } from "react";
import { Menu, X, LogOut, LayoutDashboard, Shield, Mail, Phone, Plane, Ship, Truck, PackageCheck, Globe2, Zap } from "lucide-react";
import { useAuth } from "@/hooks/use-auth";

const news = [
  { icon: Plane, text: "Air freight capacity up 12% across trans-Pacific lanes this week" },
  { icon: Ship, text: "Port of LA clears backlog — ocean transit times improve by 3 days" },
  { icon: Truck, text: "New same-day courier routes launched in 14 metro areas" },
  { icon: PackageCheck, text: "99.4% on-time delivery rate reported for Q2 shipments" },
  { icon: Globe2, text: "Expanded customs pre-clearance now live for EU & UK parcels" },
  { icon: Zap, text: "Real-time GPS tracking now available on all express services" },
] as const;

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
  const { session, isAdmin, signOut } = useAuth();

  return (
    <header className="sticky top-0 z-40 border-b border-border/60 bg-background/70 backdrop-blur-xl">
      <div className="hidden border-b border-border/60 bg-primary text-primary-foreground md:block">
        <div className="container-x flex h-9 items-center gap-6 text-xs">
          <span className="inline-flex shrink-0 items-center gap-1.5 rounded-sm bg-accent px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wider text-accent-foreground">
            <Zap className="h-3 w-3" />
            Live
          </span>

          <div className="marquee-track relative min-w-0 flex-1 overflow-hidden">
            <div className="marquee-mask flex w-max animate-marquee items-center">
              {[...news, ...news].map((item, i) => {
                const Icon = item.icon;
                return (
                  <span key={i} className="flex items-center gap-1.5 whitespace-nowrap px-5">
                    <Icon className="h-3.5 w-3.5 text-brand" />
                    <span className="text-primary-foreground/85">{item.text}</span>
                    <span className="ml-3 text-brand" aria-hidden="true">
                      •
                    </span>
                  </span>
                );
              })}
            </div>
          </div>

          <div className="flex shrink-0 items-center gap-5">
            <a
              href="mailto:info@ascl-logistics.com"
              className="inline-flex items-center gap-1.5 text-primary-foreground/85 transition hover:text-brand"
            >
              <Mail className="h-3.5 w-3.5" />
              info@ascl-logistics.com
            </a>
            <a
              href="tel:+12025947566"
              className="inline-flex items-center gap-1.5 text-primary-foreground/85 transition hover:text-brand"
            >
              <Phone className="h-3.5 w-3.5" />
              +1 (202) 594-7566
            </a>
          </div>
        </div>
      </div>
      <div className="container-x flex h-16 items-center justify-between gap-6">
        <Link to="/" className="flex items-center gap-2 font-display text-lg font-bold tracking-tight" aria-label="Americans Shipping & Courier Logistics — Home">
          <img
            src="/logo.png"
            alt="Americans Shipping & Courier Logistics"
            className="h-10 w-auto"
          />
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
          {session ? (
            <>
              {isAdmin && (
                <Link
                  to="/admin"
                  className="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-surface hover:text-foreground"
                >
                  <Shield className="h-4 w-4" /> Admin
                </Link>
              )}
              <Link
                to="/dashboard"
                className="inline-flex items-center gap-1 rounded-sm border border-border px-3 py-2 text-sm font-semibold hover:bg-surface"
              >
                <LayoutDashboard className="h-4 w-4" /> Dashboard
              </Link>
              <button
                onClick={() => signOut()}
                aria-label="Sign out"
                className="grid h-9 w-9 place-items-center rounded-sm border border-border hover:bg-surface"
              >
                <LogOut className="h-4 w-4" />
              </button>
            </>
          ) : (
            <>
              <Link
                to="/auth"
                className="rounded-sm border border-border px-4 py-2 text-sm font-semibold hover:bg-surface"
              >
                Sign in
              </Link>
              <Link
                to="/quote"
                className="rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground transition hover:opacity-90"
              >
                Get a quote
              </Link>
            </>
          )}
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
            <div className="mt-2 border-t border-border/60 pt-2">
              {session ? (
                <>
                  <Link to="/dashboard" onClick={() => setOpen(false)} className="block rounded-md px-3 py-2 text-sm">Dashboard</Link>
                  {isAdmin && <Link to="/admin" onClick={() => setOpen(false)} className="block rounded-md px-3 py-2 text-sm">Admin</Link>}
                  <button onClick={() => { signOut(); setOpen(false); }} className="block w-full rounded-md px-3 py-2 text-left text-sm">Sign out</button>
                </>
              ) : (
                <Link to="/auth" onClick={() => setOpen(false)} className="block rounded-md px-3 py-2 text-sm">Sign in</Link>
              )}
            </div>
          </nav>
        </div>
      )}
    </header>
  );
}
