import { Link } from "@tanstack/react-router";
import { Package, Facebook, Twitter, Linkedin, Youtube, Instagram, Globe } from "lucide-react";

export function SiteFooter() {
  return (
    <footer className="mt-20">
      {/* Yellow strip — country / brand */}
      <div className="bg-brand text-brand-foreground">
        <div className="container-x flex flex-col gap-3 py-4 text-sm sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-2 font-semibold">
            <Globe className="h-4 w-4" />
            You are in <span className="font-bold">United States of America</span>
            <span className="mx-2 opacity-40">·</span>
            <Link to="/contact" className="underline underline-offset-4 hover:text-accent">
              Select a different country
            </Link>
          </div>
          <div className="flex items-center gap-4 text-brand-foreground/80">
            <span className="text-xs font-semibold uppercase tracking-wider">Follow us</span>
            {[Facebook, Twitter, Linkedin, Youtube, Instagram].map((I, i) => (
              <a key={i} href="#" aria-label="social" className="hover:text-accent">
                <I className="h-4 w-4" />
              </a>
            ))}
          </div>
        </div>
      </div>

      {/* Main dark footer */}
      <div className="bg-[oklch(0.2_0.02_250)] text-white">
        <div className="container-x grid gap-10 py-14 md:grid-cols-5">
          <div className="md:col-span-1">
            <div className="flex items-center gap-2 font-display text-lg font-bold">
              <span className="grid h-8 w-8 place-items-center rounded-sm bg-brand text-accent">
                <Package className="h-4 w-4" strokeWidth={2.75} />
              </span>
              Voltra
            </div>
            <p className="mt-4 text-sm text-white/60">
              Excellence. Simply delivered. Global logistics and courier services in 220+ countries.
            </p>
          </div>

          <FooterCol
            title="About Voltra"
            links={[
              ["Company portrait", "/services"],
              ["Press", "/contact"],
              ["Investors", "/contact"],
              ["Sustainability", "/services"],
              ["Innovation", "/services"],
            ]}
          />
          <FooterCol
            title="Business Divisions"
            links={[
              ["Voltra Express", "/services"],
              ["Voltra eCommerce", "/services"],
              ["Global Forwarding", "/services"],
              ["Supply Chain", "/services"],
              ["Parcel & Same-day", "/services"],
            ]}
          />
          <FooterCol
            title="Customer Service"
            links={[
              ["Track a shipment", "/track"],
              ["Get a quote", "/quote"],
              ["Ship now", "/quote"],
              ["Delivery preferences", "/track"],
              ["Help center", "/contact"],
            ]}
          />
          <FooterCol
            title="Careers & More"
            links={[
              ["Careers", "/contact"],
              ["Newsroom", "/contact"],
              ["API & Developers", "/services"],
              ["Fair & responsible logistics", "/services"],
              ["Contact & locations", "/contact"],
            ]}
          />
        </div>

        {/* Legal bar */}
        <div className="border-t border-white/10">
          <div className="container-x flex flex-col gap-3 py-5 text-xs text-white/60 md:flex-row md:items-center md:justify-between">
            <p>© {new Date().getFullYear()} Voltra Logistics. All rights reserved.</p>
            <ul className="flex flex-wrap gap-x-5 gap-y-2">
              {[
                "Legal Notice",
                "Terms of Use",
                "Privacy Notice",
                "Cookie Settings",
                "Fair & Responsible Logistics",
              ].map((label) => (
                <li key={label}>
                  <a href="#" className="hover:text-brand">{label}</a>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </footer>
  );
}

function FooterCol({ title, links }: { title: string; links: [string, string][] }) {
  return (
    <div>
      <h4 className="mb-4 text-sm font-bold uppercase tracking-wider text-white">{title}</h4>
      <ul className="space-y-2.5 text-sm text-white/70">
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
