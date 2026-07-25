import { Link } from "@tanstack/react-router";
import { useState } from "react";
import { Menu, X, LogOut, LayoutDashboard, Shield, Mail, Phone } from "lucide-react";
import { useAuth } from "@/hooks/use-auth";

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
      <div className="hidden border-b border-border/60 bg-surface/60 md:block">
        <div className="container-x flex h-9 items-center justify-end gap-6 text-xs text-muted-foreground">
          <a
            href="mailto:info@ascl-logistics.com"
            className="inline-flex items-center gap-1.5 transition hover:text-foreground"
          >
            <Mail className="h-3.5 w-3.5" />
            info@ascl-logistics.com
          </a>
          <a
            href="tel:+12025947566"
            className="inline-flex items-center gap-1.5 transition hover:text-foreground"
          >
            <Phone className="h-3.5 w-3.5" />
            +1 (202) 594-7566
          </a>
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
