import { createFileRoute } from "@tanstack/react-router";
import { Mail, Phone, MapPin, MessageSquare } from "lucide-react";

export const Route = createFileRoute("/contact")({
  head: () => ({
    meta: [
      { title: "Contact — American Shipping & Logistics" },
      { name: "description", content: "Get in touch with American Shipping & Logistics sales, support, or press. We reply within one business hour." },
      { property: "og:title", content: "Contact — American Shipping & Logistics" },
      { property: "og:description", content: "Talk to American Shipping & Logistics's logistics team." },
    ],
    links: [{ rel: "canonical", href: "/contact" }],
  }),
  component: ContactPage,
});

function ContactPage() {
  return (
    <section className="container-x py-16 md:py-20">
      <div className="grid gap-14 lg:grid-cols-[1fr_1.2fr]">
        <div>
          <p className="font-mono text-xs uppercase tracking-widest text-brand">Contact</p>
          <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Let's move something.</h1>
          <p className="mt-4 text-muted-foreground">
            Whether you ship a parcel a week or a container a day, our team is here to help you
            find the right service.
          </p>

          <ul className="mt-10 space-y-5">
            <ContactRow icon={Mail} label="Email" value="info@ascl-logistics.com" />
            <ContactRow icon={Phone} label="Phone" value="+1 (202) 594-7566" />
            <ContactRow icon={MessageSquare} label="Live chat" value="Weekdays, 07:00 – 22:00 UTC" />
            <ContactRow icon={MapPin} label="HQ" value="Hamburg · Singapore · New York" />
          </ul>
        </div>

        <form
          onSubmit={(e) => e.preventDefault()}
          className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8"
        >
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="Name" placeholder="Alex Rivera" />
            <Field label="Email" placeholder="you@company.com" type="email" />
            <Field label="Company" placeholder="Acme Inc" />
            <Field label="Monthly shipments" placeholder="50–200" />
          </div>
          <label className="mt-4 block">
            <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">
              How can we help?
            </span>
            <textarea
              rows={5}
              placeholder="Tell us about what you ship and where…"
              className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
            />
          </label>
          <button className="mt-6 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 md:w-auto md:px-8">
            Send message
          </button>
          <p className="mt-3 text-xs text-muted-foreground">We reply within one business hour.</p>
        </form>
      </div>
    </section>
  );
}

function ContactRow({ icon: Icon, label, value }: { icon: typeof Mail; label: string; value: string }) {
  return (
    <li className="flex items-start gap-4">
      <div className="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-background text-brand">
        <Icon className="h-4 w-4" />
      </div>
      <div>
        <div className="text-xs uppercase tracking-wider text-muted-foreground">{label}</div>
        <div className="mt-0.5 font-medium">{value}</div>
      </div>
    </li>
  );
}

function Field({ label, placeholder, type = "text" }: { label: string; placeholder?: string; type?: string }) {
  return (
    <label className="block">
      <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
      <input
        type={type}
        placeholder={placeholder}
        className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
      />
    </label>
  );
}
