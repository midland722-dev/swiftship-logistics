import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { z } from "zod";
import { Package, MapPin, CheckCircle2, Circle, Truck } from "lucide-react";
import { supabase } from "@/integrations/supabase/client";

const searchSchema = z.object({ id: z.string().optional() });

export const Route = createFileRoute("/track")({
  validateSearch: searchSchema,
  head: () => ({
    meta: [
      { title: "Track shipment — Voltra" },
      { name: "description", content: "Live tracking for Voltra parcels and freight worldwide." },
    ],
    links: [{ rel: "canonical", href: "/track" }],
  }),
  component: TrackPage,
});

function TrackPage() {
  const { id } = Route.useSearch();
  const navigate = useNavigate();
  const [input, setInput] = useState(id ?? "");
  const [shipment, setShipment] = useState<any | null>(null);
  const [events, setEvents] = useState<any[]>([]);
  const [notFound, setNotFound] = useState(false);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!id) {
      setShipment(null);
      setNotFound(false);
      return;
    }
    setInput(id);
    setLoading(true);
    (async () => {
      const { data: s } = await supabase
        .from("shipments")
        .select("*")
        .eq("tracking_code", id)
        .maybeSingle();
      if (!s) {
        setShipment(null);
        setNotFound(true);
        setLoading(false);
        return;
      }
      setShipment(s);
      setNotFound(false);
      const { data: ev } = await supabase
        .from("shipment_events")
        .select("*")
        .eq("shipment_id", (s as any).id)
        .order("occurred_at", { ascending: true });
      setEvents(ev ?? []);
      setLoading(false);
    })();
  }, [id]);

  const progress: Record<string, number> = {
    booked: 5,
    picked_up: 25,
    in_transit: 60,
    out_for_delivery: 85,
    delivered: 100,
    exception: 50,
    cancelled: 0,
  };

  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Live tracking</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Where's my shipment?</h1>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          if (input.trim()) navigate({ to: "/track", search: { id: input.trim() } });
        }}
        className="mt-8 flex max-w-2xl flex-col gap-3 rounded-2xl border border-border bg-surface/60 p-4 sm:flex-row"
      >
        <input
          value={input}
          onChange={(e) => setInput(e.target.value)}
          placeholder="Enter tracking number, e.g. VLT-1234567"
          className="flex-1 rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none focus:border-brand"
        />
        <button className="rounded-sm bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
          Track
        </button>
      </form>

      {!id && (
        <p className="mt-6 text-sm text-muted-foreground">Enter a tracking number to view live status.</p>
      )}
      {id && loading && <p className="mt-6 text-sm text-muted-foreground">Looking up {id}…</p>}
      {id && !loading && notFound && (
        <p className="mt-6 text-sm text-muted-foreground">
          No shipment found for <span className="font-mono">{id}</span>.
        </p>
      )}

      {shipment && (
        <div className="mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
          <div className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
            <div className="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-4">
              <div className="min-w-0">
                <div className="font-mono text-xs text-muted-foreground">{shipment.tracking_code}</div>
                <h2 className="mt-1 truncate font-display text-2xl font-bold">
                  {shipment.status.replace(/_/g, " ")}
                  {shipment.eta && ` — arriving ${new Date(shipment.eta).toLocaleDateString()}`}
                </h2>
              </div>
              <StatusPill status={shipment.status} />
            </div>

            <div className="mt-6">
              <div className="flex items-center justify-between text-xs text-muted-foreground">
                <span>{shipment.from_location}</span>
                <span>{shipment.to_location}</span>
              </div>
              <div className="mt-2 h-2 overflow-hidden rounded-full bg-background">
                <div className="h-full rounded-full bg-brand" style={{ width: `${progress[shipment.status] ?? 0}%` }} />
              </div>
            </div>

            <ol className="mt-8 space-y-4">
              {events.length === 0 && (
                <li className="text-sm text-muted-foreground">No events yet. Check back soon.</li>
              )}
              {events.map((ev, i) => (
                <li key={ev.id} className="flex gap-4">
                  <div className="flex flex-col items-center">
                    <div className="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-brand bg-brand text-brand-foreground">
                      <CheckCircle2 className="h-4 w-4" />
                    </div>
                    {i < events.length - 1 && <div className="mt-1 h-8 w-px bg-brand/50" />}
                  </div>
                  <div className="min-w-0 flex-1 pb-2">
                    <div className="text-sm font-medium">{ev.label}</div>
                    <div className="mt-0.5 text-xs text-muted-foreground">
                      {ev.location && `${ev.location} · `}
                      {new Date(ev.occurred_at).toLocaleString()}
                    </div>
                  </div>
                </li>
              ))}
            </ol>
          </div>

          <div className="space-y-4">
            <InfoCard title="Route">
              <RouteRow icon={MapPin} label="From" value={shipment.from_location} />
              <RouteRow icon={MapPin} label="To" value={shipment.to_location} />
              {shipment.recipient_name && <RouteRow icon={Package} label="Recipient" value={shipment.recipient_name} />}
            </InfoCard>
            <InfoCard title="Details">
              <Row label="Service" value={shipment.service_speed} />
              <Row label="Weight" value={`${shipment.weight_kg} kg`} />
              <Row label="Price" value={`$${Number(shipment.price).toFixed(2)}`} />
              {shipment.insurance && <Row label="Insurance" value="Included" />}
            </InfoCard>
          </div>
        </div>
      )}
    </section>
  );
}

function StatusPill({ status }: { status: string }) {
  const cls =
    status === "delivered"
      ? "bg-emerald-500/20 text-emerald-600"
      : status === "exception" || status === "cancelled"
        ? "bg-red-500/20 text-red-500"
        : "bg-brand/20 text-brand";
  return <span className={`shrink-0 rounded-full px-3 py-1 text-xs font-semibold ${cls}`}>{status.replace(/_/g, " ")}</span>;
}
function InfoCard({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div className="rounded-2xl border border-border bg-surface/60 p-6">
      <h3 className="font-semibold">{title}</h3>
      <dl className="mt-4 space-y-3">{children}</dl>
    </div>
  );
}
function Row({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex justify-between gap-4 text-sm">
      <dt className="text-muted-foreground">{label}</dt>
      <dd className="text-right font-medium capitalize">{value}</dd>
    </div>
  );
}
function RouteRow({ icon: Icon, label, value }: { icon: typeof MapPin; label: string; value: string }) {
  return (
    <div className="flex items-center gap-3 text-sm">
      <Icon className="h-4 w-4 text-brand" />
      <div className="min-w-0 flex-1">
        <div className="text-xs text-muted-foreground">{label}</div>
        <div className="truncate font-medium">{value}</div>
      </div>
    </div>
  );
}
