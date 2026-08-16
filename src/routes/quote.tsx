import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useMemo, useState } from "react";
import { Zap, Truck, Ship, Shield, CheckCircle2 } from "lucide-react";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/use-auth";
import { calcQuote, DEFAULT_RULES, type PricingRules, type ShipSpeed } from "@/lib/pricing";
import { toast } from "sonner";

export const Route = createFileRoute("/quote")({
  head: () => ({
    meta: [
      { title: "Get a shipping quote — American Shipping & Logistics" },
      { name: "description", content: "Live shipping cost calculator with transparent breakdown by weight, dimensions, speed and insurance." },
      { property: "og:title", content: "Instant shipping quote — American Shipping & Logistics" },
      { property: "og:description", content: "Dynamic pricing calculator with live per-parcel estimates." },
    ],
    links: [{ rel: "canonical", href: "/quote" }],
  }),
  component: QuotePage,
});

const speedConfig: Record<ShipSpeed, { icon: typeof Zap; label: string; days: string }> = {
  standard: { icon: Ship, label: "Standard", days: "5–8 business days" },
  express: { icon: Truck, label: "Express", days: "2–3 business days" },
  priority: { icon: Zap, label: "Priority", days: "Next business day" },
};

function QuotePage() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [rules, setRules] = useState<PricingRules>(DEFAULT_RULES);
  const [from, setFrom] = useState("Berlin, DE");
  const [to, setTo] = useState("Tokyo, JP");
  const [weight, setWeight] = useState(2.4);
  const [length, setLength] = useState(30);
  const [width, setWidth] = useState(20);
  const [height, setHeight] = useState(15);
  const [speed, setSpeed] = useState<ShipSpeed>("express");
  const [insurance, setInsurance] = useState(true);
  const [declared, setDeclared] = useState(500);
  const [booking, setBooking] = useState(false);

  useEffect(() => {
    supabase
      .from("pricing_rules")
      .select("*")
      .eq("is_active", true)
      .maybeSingle()
      .then(({ data }) => {
        if (data) setRules(data as PricingRules);
      });
  }, []);

  const breakdown = useMemo(
    () =>
      calcQuote(rules, {
        weight_kg: weight,
        length_cm: length,
        width_cm: width,
        height_cm: height,
        speed,
        insurance,
        declared_value: declared,
      }),
    [rules, weight, length, width, height, speed, insurance, declared],
  );

  const saveQuote = async () => {
    if (!user) {
      navigate({ to: "/admin", search: { next: "/quote" } });
      return;
    }
    const { error } = await supabase.from("quotes").insert({
      owner_id: user.id,
      from_location: from,
      to_location: to,
      weight_kg: weight,
      length_cm: length,
      width_cm: width,
      height_cm: height,
      service_speed: speed,
      insurance,
      price: breakdown.total,
      currency: breakdown.currency,
    });
    if (error) toast.error(error.message);
    else toast.success("Quote saved to your dashboard");
  };

  const book = async () => {
    if (!user) {
      navigate({ to: "/admin", search: { next: "/quote" } });
      return;
    }
    setBooking(true);
    const eta = new Date(Date.now() + (speed === "priority" ? 1 : speed === "express" ? 3 : 6) * 24 * 3600 * 1000);
    const { data, error } = await supabase
      .from("shipments")
      .insert({
        owner_id: user.id,
        from_location: from,
        to_location: to,
        weight_kg: weight,
        length_cm: length,
        width_cm: width,
        height_cm: height,
        service_speed: speed,
        insurance,
        declared_value: declared,
        price: breakdown.total,
        currency: breakdown.currency,
        eta: eta.toISOString(),
      })
      .select("id, tracking_number")
      .single();
    setBooking(false);
    if (error || !data) {
      toast.error(error?.message ?? "Failed to book");
      return;
    }
    await supabase.from("shipment_events").insert({
      shipment_id: data.id,
      label: "Shipment booked",
      location: from,
    });
    toast.success(`Booked ${data.tracking_number}`);
    navigate({ to: "/track", search: { id: data.tracking_number } });
  };

  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Dynamic pricing</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">
        Instant quotes. <span className="text-brand">Live from our rate engine.</span>
      </h1>
      <p className="mt-4 max-w-xl text-muted-foreground">
        Every value updates in real time as you change parcel, speed, or insurance.
      </p>

      <div className="mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <div className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
          <div className="grid gap-4 md:grid-cols-2">
            <Field label="From" value={from} onChange={setFrom} />
            <Field label="To" value={to} onChange={setTo} />
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
            {(Object.keys(speedConfig) as ShipSpeed[]).map((s) => {
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

          <label className="mt-6 flex items-start gap-3 rounded-xl border border-border bg-background/40 p-4">
            <input
              type="checkbox"
              checked={insurance}
              onChange={(e) => setInsurance(e.target.checked)}
              className="mt-1 h-4 w-4 accent-[var(--brand)]"
            />
            <div className="flex-1">
              <div className="flex items-center gap-2 text-sm font-medium">
                <Shield className="h-4 w-4 text-brand" /> Add insurance
              </div>
              <div className="mt-1 text-xs text-muted-foreground">
                Flat ${rules.insurance_fee.toFixed(2)} + {(rules.insurance_pct * 100).toFixed(1)}% of declared value.
              </div>
              {insurance && (
                <div className="mt-3">
                  <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">Declared value ($)</span>
                  <input
                    type="number"
                    min={0}
                    value={declared}
                    onChange={(e) => setDeclared(Number(e.target.value))}
                    className="mt-1 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-brand"
                  />
                </div>
              )}
            </div>
          </label>
        </div>

        <aside className="lg:sticky lg:top-24 lg:self-start">
          <div className="rounded-2xl border border-brand/40 bg-gradient-to-b from-brand/15 to-surface p-6 md:p-8">
            <div className="text-xs font-mono uppercase tracking-widest text-brand">Estimated price</div>
            <div className="mt-2 font-display text-5xl font-bold">${breakdown.total.toFixed(2)}</div>
            <div className="mt-1 text-sm text-muted-foreground">{speedConfig[speed].days}</div>

            <div className="mt-6 rounded-xl border border-border bg-background/40 p-4 text-sm">
              <h3 className="font-semibold">Breakdown</h3>
              <ul className="mt-3 space-y-1.5 text-xs">
                <BreakdownRow label={`Billable weight`} value={`${breakdown.billableWeight} kg`} muted />
                <BreakdownRow label="Base" value={`$${breakdown.base.toFixed(2)}`} />
                <BreakdownRow label="Weight charge" value={`$${breakdown.weightCharge.toFixed(2)}`} />
                <BreakdownRow label={`${speedConfig[speed].label} multiplier`} value={`$${breakdown.speedSubtotal.toFixed(2)}`} />
                <BreakdownRow label="Fuel surcharge" value={`$${breakdown.fuel.toFixed(2)}`} />
                <BreakdownRow label="Insurance" value={`$${breakdown.insurance.toFixed(2)}`} />
                <li className="mt-2 flex justify-between border-t border-border pt-2 font-semibold">
                  <span>Total</span>
                  <span>${breakdown.total.toFixed(2)}</span>
                </li>
              </ul>
            </div>

            <button
              onClick={book}
              disabled={booking}
              className="mt-6 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 disabled:opacity-60"
            >
              <CheckCircle2 className="mr-2 inline h-4 w-4" />
              {booking ? "Booking…" : "Book this shipment"}
            </button>
            <button
              onClick={saveQuote}
              className="mt-2 w-full rounded-sm border border-border py-3 text-sm font-semibold hover:bg-surface"
            >
              Save quote
            </button>
            {!user && <p className="mt-3 text-center text-xs text-muted-foreground">Sign in to book or save quotes.</p>}
          </div>
        </aside>
      </div>
    </section>
  );
}

function BreakdownRow({ label, value, muted }: { label: string; value: string; muted?: boolean }) {
  return (
    <li className={`flex justify-between ${muted ? "text-muted-foreground" : ""}`}>
      <span>{label}</span>
      <span>{value}</span>
    </li>
  );
}

function Field({ label, value, onChange }: { label: string; value: string; onChange: (v: string) => void }) {
  return (
    <label className="block">
      <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
      <input
        value={value}
        onChange={(e) => onChange(e.target.value)}
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
