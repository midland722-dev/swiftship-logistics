import { createFileRoute } from "@tanstack/react-router";
import { useMemo, useState } from "react";
import { Zap, Truck, Ship, Shield, CheckCircle2 } from "lucide-react";

export const Route = createFileRoute("/quote")({
  head: () => ({
    meta: [
      { title: "Get a shipping quote — Voltra" },
      { name: "description", content: "Calculate shipping costs by origin, destination, weight, and speed with Voltra's instant quote tool." },
      { property: "og:title", content: "Get a shipping quote — Voltra" },
      { property: "og:description", content: "Instant shipping quotes in seconds." },
    ],
    links: [{ rel: "canonical", href: "/quote" }],
  }),
  component: QuotePage,
});

type Speed = "standard" | "express" | "priority";

const speedConfig: Record<Speed, { icon: typeof Zap; label: string; days: string; mult: number }> = {
  standard: { icon: Ship, label: "Standard", days: "5–8 business days", mult: 1 },
  express: { icon: Truck, label: "Express", days: "2–3 business days", mult: 1.9 },
  priority: { icon: Zap, label: "Priority", days: "Next business day", mult: 3.2 },
};

function QuotePage() {
  const [from, setFrom] = useState("Berlin, DE");
  const [to, setTo] = useState("Tokyo, JP");
  const [weight, setWeight] = useState(2.4);
  const [length, setLength] = useState(30);
  const [width, setWidth] = useState(20);
  const [height, setHeight] = useState(15);
  const [speed, setSpeed] = useState<Speed>("express");
  const [insurance, setInsurance] = useState(true);

  const price = useMemo(() => {
    const volumetric = (length * width * height) / 5000;
    const billable = Math.max(weight, volumetric);
    const base = 8 + billable * 4.2;
    const withSpeed = base * speedConfig[speed].mult;
    return (withSpeed + (insurance ? 4.5 : 0)).toFixed(2);
  }, [weight, length, width, height, speed, insurance]);

  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Shipping quote</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">
        Instant quotes. <span className="text-brand">No account needed.</span>
      </h1>
      <p className="mt-4 max-w-xl text-muted-foreground">
        Enter your parcel details and we'll price every service tier in seconds.
      </p>

      <div className="mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <div className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="From" value={from} onChange={setFrom} placeholder="City, Country" />
            <Field label="To" value={to} onChange={setTo} placeholder="City, Country" />
          </div>

          <h3 className="mt-8 text-sm font-semibold uppercase tracking-wider text-muted-foreground">Parcel</h3>
          <div className="mt-3 grid gap-4 md:grid-cols-4">
            <NumField label="Weight (kg)" value={weight} onChange={setWeight} step={0.1} />
            <NumField label="Length (cm)" value={length} onChange={setLength} />
            <NumField label="Width (cm)" value={width} onChange={setWidth} />
            <NumField label="Height (cm)" value={height} onChange={setHeight} />
          </div>

          <h3 className="mt-8 text-sm font-semibold uppercase tracking-wider text-muted-foreground">Delivery speed</h3>
          <div className="mt-3 grid gap-3 md:grid-cols-3">
            {(Object.keys(speedConfig) as Speed[]).map((s) => {
              const cfg = speedConfig[s];
              const Icon = cfg.icon;
              const selected = speed === s;
              return (
                <button
                  key={s}
                  onClick={() => setSpeed(s)}
                  className={`flex flex-col items-start rounded-xl border p-4 text-left transition ${
                    selected ? "border-brand bg-brand/10" : "border-border bg-background/40 hover:bg-surface"
                  }`}
                >
                  <Icon className={`h-5 w-5 ${selected ? "text-brand" : "text-muted-foreground"}`} />
                  <div className="mt-3 font-semibold">{cfg.label}</div>
                  <div className="text-xs text-muted-foreground">{cfg.days}</div>
                </button>
              );
            })}
          </div>

          <label className="mt-6 flex items-center gap-3 rounded-xl border border-border bg-background/40 p-4">
            <input
              type="checkbox"
              checked={insurance}
              onChange={(e) => setInsurance(e.target.checked)}
              className="h-4 w-4 accent-[var(--brand)]"
            />
            <div className="flex-1">
              <div className="flex items-center gap-2 text-sm font-medium">
                <Shield className="h-4 w-4 text-brand" />
                Add insurance ($4.50)
              </div>
              <div className="text-xs text-muted-foreground">Cover contents up to $2,000 in case of loss or damage.</div>
            </div>
          </label>
        </div>

        <aside className="lg:sticky lg:top-24 lg:self-start">
          <div className="rounded-2xl border border-brand/40 bg-gradient-to-b from-brand/15 to-surface p-6 md:p-8">
            <div className="text-xs font-mono uppercase tracking-widest text-brand">Estimated price</div>
            <div className="mt-2 font-display text-5xl font-bold">${price}</div>
            <div className="mt-1 text-sm text-muted-foreground">{speedConfig[speed].days}</div>

            <ul className="mt-6 space-y-2 text-sm">
              {[
                `${from} → ${to}`,
                `${weight} kg parcel`,
                `${length}×${width}×${height} cm`,
                `${speedConfig[speed].label} shipping`,
                insurance ? "Insurance included" : "No insurance",
              ].map((line) => (
                <li key={line} className="flex items-start gap-2 text-muted-foreground">
                  <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-brand" />
                  {line}
                </li>
              ))}
            </ul>

            <button className="mt-6 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
              Book this shipment
            </button>
            <button className="mt-2 w-full rounded-sm border border-border py-3 text-sm font-semibold hover:bg-surface">
              Save quote
            </button>
          </div>
        </aside>
      </div>
    </section>
  );
}

function Field({ label, value, onChange, placeholder }: { label: string; value: string; onChange: (v: string) => void; placeholder?: string }) {
  return (
    <label className="block">
      <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
      <input
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
      />
    </label>
  );
}

function NumField({ label, value, onChange, step = 1 }: { label: string; value: number; onChange: (v: number) => void; step?: number }) {
  return (
    <label className="block">
      <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
      <input
        type="number"
        value={value}
        step={step}
        min={0}
        onChange={(e) => onChange(Number(e.target.value))}
        className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
      />
    </label>
  );
}
