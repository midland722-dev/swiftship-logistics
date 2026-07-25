import { createFileRoute } from "@tanstack/react-router";
<<<<<<< HEAD
import { useState, useEffect } from "react";
=======
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
import { Mail, Phone, MapPin, MessageSquare } from "lucide-react";

export const Route = createFileRoute("/contact")({
  head: () => ({
    meta: [
<<<<<<< HEAD
      { title: "Contact — American Shipping & Logistics" },
      { name: "description", content: "Get in touch with American Shipping & Logistics sales, support, or press. We reply within one business hour." },
      { property: "og:title", content: "Contact — American Shipping & Logistics" },
      { property: "og:description", content: "Talk to American Shipping & Logistics team." },
=======
      { title: "Contact — Voltra Logistics" },
      { name: "description", content: "Get in touch with Voltra sales, support, or press. We reply within one business hour." },
      { property: "og:title", content: "Contact — Voltra" },
      { property: "og:description", content: "Talk to Voltra's logistics team." },
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
    ],
    links: [{ rel: "canonical", href: "/contact" }],
  }),
  component: ContactPage,
});

<<<<<<< HEAD
type Feedback = { type: "success" | "error"; message: string } | null;

function ContactPage() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [company, setCompany] = useState("");
  const [phone, setPhone] = useState("");
  const [subject, setSubject] = useState("");
  const [message, setMessage] = useState("");
  const [category, setCategory] = useState("general");
  const [csrfToken, setCsrfToken] = useState("");
  const [feedback, setFeedback] = useState<Feedback>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    fetch("/php/contact.php")
      .then((r) => r.text())
      .then((html) => {
        const m = html.match(/name="csrf_token"\s+value="([^"]+)"/);
        if (m) setCsrfToken(m[1]);
      })
      .catch(() => {
        setFeedback({ type: "error", message: "Unable to load security token. Please refresh." });
      });
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFeedback(null);

    if (!name.trim() || !email.trim() || !subject.trim() || !message.trim()) {
      setFeedback({ type: "error", message: "Please fill in all required fields." });
      return;
    }

    setSubmitting(true);
    try {
      const form = new FormData();
      form.set("name", name.trim());
      form.set("email", email.trim());
      form.set("company", company.trim());
      form.set("phone", phone.trim());
      form.set("subject", subject.trim());
      form.set("message", message.trim());
      form.set("category", category);
      form.set("csrf_token", csrfToken);

      const res = await fetch("/php/process/contact_submit.php", {
        method: "POST",
        body: form,
        headers: { Accept: "text/html" },
      });

      if (res.redirected || res.status >= 300 && res.status < 400) {
        window.location.href = res.url || "/contact.php?sent=1";
        return;
      }

      const text = await res.text();
      if (res.ok && text.includes("sent=1")) {
        setFeedback({ type: "success", message: "Your message has been sent. We'll reply within one business hour." });
        setName("");
        setEmail("");
        setCompany("");
        setPhone("");
        setSubject("");
        setMessage("");
        setCategory("general");
      } else {
        const errorMatch = text.match(/error=([^&]+)/);
        setFeedback({ type: "error", message: errorMatch ? decodeURIComponent(errorMatch[1]) : "Something went wrong. Please try again." });
      }
    } catch {
      setFeedback({ type: "error", message: "Network error. Please try again." });
    } finally {
      setSubmitting(false);
    }
  };

=======
function ContactPage() {
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
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
<<<<<<< HEAD
             <ContactRow icon={Mail} label="Email" value="info@ascl-logistics.com" />
             <ContactRow icon={Phone} label="Phone" value="+1 (215) 815-9791" />
             <ContactRow icon={MessageSquare} label="Live chat" value="Weekdays, 07:00 – 22:00 UTC" />
             <ContactRow icon={MapPin} label="HQ" value="United States" />
           </ul>
        </div>

        <div>
          {feedback && (
            <div className={`mb-6 rounded-lg border p-4 text-sm ${feedback.type === "success" ? "border-green-200 bg-green-50 text-green-800" : "border-red-200 bg-red-50 text-red-800"}`}>
              {feedback.type === "success" ? "✅ " : "⚠️ "}
              {feedback.message}
            </div>
          )}

          <form onSubmit={handleSubmit} className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
            <input type="hidden" name="csrf_token" value={csrfToken} readOnly />

            <div className="grid gap-4 md:grid-cols-2">
              <Field label="Name" placeholder="Alex Rivera" value={name} onChange={setName} required />
              <Field label="Email" placeholder="you@company.com" value={email} onChange={setEmail} type="email" required />
              <Field label="Company" placeholder="Acme Inc" value={company} onChange={setCompany} />
              <div className="block">
                <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">Subject</span>
                <select
                  value={category}
                  onChange={(e) => setCategory(e.target.value)}
                  className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
                >
                  <option value="general">General inquiry</option>
                  <option value="shipment-issue">Shipment issue</option>
                  <option value="billing">Billing</option>
                  <option value="technical">Technical</option>
                  <option value="customs">Customs</option>
                  <option value="feedback">Feedback</option>
                  <option value="complaint">Complaint</option>
                  <option value="partnership">Partnership</option>
                </select>
              </div>
            </div>

            <label className="mt-4 block">
              <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                How can we help?
              </span>
              <textarea
                rows={5}
                placeholder="Tell us about what you ship and where…"
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                required
                className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
              />
            </label>

            <button
              type="submit"
              disabled={submitting || !csrfToken}
              className="mt-6 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 md:w-auto md:px-8 disabled:opacity-60"
            >
              {submitting ? "Sending…" : "Send message"}
            </button>
            <p className="mt-3 text-xs text-muted-foreground">We reply within one business hour.</p>
          </form>
        </div>
=======
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
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
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

<<<<<<< HEAD
function Field({ label, value, onChange, placeholder, type = "text", required }: { label: string; value: string; onChange: (v: string) => void; placeholder?: string; type?: string; required?: boolean }) {
=======
function Field({ label, placeholder, type = "text" }: { label: string; placeholder?: string; type?: string }) {
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
  return (
    <label className="block">
      <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
      <input
        type={type}
<<<<<<< HEAD
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        required={required}
=======
        placeholder={placeholder}
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
        className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
      />
    </label>
  );
}
