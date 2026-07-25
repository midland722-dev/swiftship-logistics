import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState, useEffect } from "react";
import { z } from "zod";
import { Package, MapPin, CheckCircle2, Circle, Truck, Plane, Search } from "lucide-react";
import { fetchTracking, type TrackingShipment, type TrackingHistoryEvent } from "../lib/track-api";

const searchSchema = z.object({
  id: z.string().optional(),
});

const STATUS_LABELS: Record<string, { label: string; color: string }> = {
  pending: { label: "Pending", color: "#6b7280" },
  processing: { label: "Processing", color: "#3b82f6" },
  picked_up: { label: "Picked Up", color: "#8b5cf6" },
  in_transit: { label: "In Transit", color: "#f59e0b" },
  at_hub: { label: "At Hub", color: "#f59e0b" },
  out_for_delivery: { label: "Out for Delivery", color: "#10b981" },
  delivered: { label: "Delivered", color: "#059669" },
  customs_inspection: { label: "Customs Inspection", color: "#ef4444" },
  customs_clearance: { label: "Customs Clearance", color: "#f59e0b" },
  customs_delayed: { label: "Customs Delayed", color: "#ef4444" },
  held: { label: "On Hold", color: "#ef4444" },
  returned: { label: "Returned", color: "#6b7280" },
  cancelled: { label: "Cancelled", color: "#6b7280" },
  Booked: { label: "Booked", color: "#3b82f6" },
  Approved: { label: "Approved", color: "#10b981" },
  Delivered: { label: "Delivered", color: "#059669" },
};

const PROGRESS_MAP: Record<string, number> = {
  pending: 5,
  processing: 10,
  picked_up: 20,
  at_warehouse: 30,
  in_transit: 50,
  at_hub: 60,
  customs_inspection: 55,
  customs_clearance: 65,
  out_for_delivery: 80,
  delivered: 100,
  returned: 100,
  cancelled: 100,
  Booked: 10,
  Approved: 25,
  Delivered: 100,
};

export const Route = createFileRoute("/track")({
  validateSearch: searchSchema,
  head: () => ({
    meta: [
      { title: "Track shipment — American Shipping & Logistics" },
      {
        name: "description",
        content: "Live tracking for American Shipping & Logistics parcels and freight worldwide.",
      },
      { property: "og:title", content: "Track shipment — American Shipping & Logistics" },
      {
        property: "og:description",
        content: "Real-time shipment status, ETA, and proof of delivery.",
      },
    ],
    links: [{ rel: "canonical", href: "/track" }],
  }),
  component: TrackPage,
});

function statusInfo(status: string) {
  return (
    STATUS_LABELS[status] ?? {
      label: status.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase()),
      color: "#6b7280",
    }
  );
}

function formatDateTime(value: string | null | undefined): string {
  if (!value) return "—";
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function TrackPage() {
  const { id } = Route.useSearch();
  const navigate = useNavigate();
  const [input, setInput] = useState(id ?? "");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [data, setData] = useState<{
    shipment: TrackingShipment;
    history: TrackingHistoryEvent[];
  } | null>(null);

  useEffect(() => {
    if (!id) return;
    let cancelled = false;
    setLoading(true);
    setError(null);
    setData(null);

    fetchTracking(id)
      .then((res) => {
        if (cancelled) return;
        if (!res.found || !res.shipment) {
          setError(res.message ?? "Shipment not found.");
          return;
        }
        setData({ shipment: res.shipment, history: res.history });
      })
      .catch((err) => {
        if (cancelled) return;
        setError(err instanceof Error ? err.message : "Something went wrong.");
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, [id]);

  const si = data ? statusInfo(data.shipment.status) : null;
  const progress = data ? (PROGRESS_MAP[data.shipment.status] ?? 40) : 40;

  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Live tracking</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Where's my shipment?</h1>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          navigate({ to: "/track", search: { id: input || "VLT-0000000" } });
        }}
        className="mt-8 flex max-w-2xl flex-col gap-3 rounded-2xl border border-border bg-surface/60 p-4 sm:flex-row"
      >
        <input
          value={input}
          onChange={(e) => setInput(e.target.value)}
          placeholder="Enter tracking number, e.g. VLT-4820193"
          className="flex-1 rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none placeholder:text-muted-foreground/70 focus:border-brand"
        />
        <button className="rounded-sm bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
          Track
        </button>
      </form>

      {!id && (
        <p className="mt-6 text-sm text-muted-foreground">
          Enter a tracking number to see live status. Try{" "}
          <button
            onClick={() => {
              setInput("VLT-4820193");
              navigate({ to: "/track", search: { id: "VLT-4820193" } });
            }}
            className="text-brand underline underline-offset-2"
          >
            VLT-4820193
          </button>
          .
        </p>
      )}

      {loading && (
        <div className="mt-10 rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
          <p className="text-sm text-muted-foreground">Loading tracking information…</p>
        </div>
      )}

      {error && (
        <div className="mt-8 rounded-lg border border-red-200 bg-red-50 p-6 text-sm text-red-800">
          {error}
        </div>
      )}

      {data && si && (
        <div className="mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
          <div className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
            <div className="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-4">
              <div className="min-w-0">
                <div className="font-mono text-xs text-muted-foreground">
                  {data.shipment.tracking_number}
                </div>
                <h2 className="mt-1 truncate font-display text-2xl font-bold">
                  {si.label}
                  {data.shipment.estimated_delivery
                    ? ` — arriving ${formatDateTime(data.shipment.estimated_delivery)}`
                    : ""}
                </h2>
              </div>
              <span
                className="shrink-0 rounded-full px-3 py-1 text-xs font-semibold text-white"
                style={{ background: si.color }}
              >
                {si.label}
              </span>
            </div>

            <div className="mt-6">
              <div className="flex items-center justify-between text-xs text-muted-foreground">
                <span>{data.shipment.origin_city ?? "Origin"}</span>
                <span>{data.shipment.destination_city ?? "Destination"}</span>
              </div>
              <div className="mt-2 h-2 overflow-hidden rounded-full bg-background">
                <div className="h-full rounded-full bg-brand" style={{ width: `${progress}%` }} />
              </div>
              <div className="mt-1 text-right font-mono text-xs text-brand">{progress}%</div>
            </div>

            <ol className="mt-8 space-y-4">
              {data.history.map((ev, i) => {
                const time = (() => {
                  const d = new Date(ev.occurred_at);
                  if (Number.isNaN(d.getTime())) return "—";
                  return d.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
                })();
                const date = (() => {
                  const d = new Date(ev.occurred_at);
                  if (Number.isNaN(d.getTime())) return "—";
                  return d.toLocaleDateString("en-US", { month: "short", day: "numeric" });
                })();
                const isLast = i === data.history.length - 1;
                const isDone = i < data.history.length - 1;

                return (
                  <li key={i} className="flex gap-4">
                    <div className="flex flex-col items-center">
                      <div
                        className={`grid h-9 w-9 shrink-0 place-items-center rounded-full border ${
                          isDone
                            ? "border-brand bg-brand text-brand-foreground"
                            : "border-border bg-background text-muted-foreground"
                        }`}
                      >
                        {isDone ? (
                          <CheckCircle2 className="h-4 w-4" />
                        ) : (
                          <Circle className="h-3 w-3" />
                        )}
                      </div>
                      {!isLast && (
                        <div className={`mt-1 h-8 w-px ${isDone ? "bg-brand/50" : "bg-border"}`} />
                      )}
                    </div>
                    <div className="min-w-0 flex-1 pb-2">
                      <div
                        className={`text-sm font-medium ${isDone ? "text-foreground" : "text-muted-foreground"}`}
                      >
                        {ev.description ?? ev.status.replace(/_/g, " ")}
                      </div>
                      <div className="mt-0.5 text-xs text-muted-foreground">
                        {[ev.location, ev.transit_location].filter(Boolean).join(" · ") || "—"} ·{" "}
                        {date} {time !== "—" && `· ${time}`}
                      </div>
                    </div>
                  </li>
                );
              })}
            </ol>
          </div>

          <div className="space-y-4">
            <InfoCard title="Route">
              <RouteRow
                icon={MapPin}
                label="From"
                value={
                  `${data.shipment.origin_city ?? ""} ${data.shipment.origin_country ?? ""}`.trim() ||
                  "—"
                }
              />
              <RouteRow
                icon={MapPin}
                label="To"
                value={
                  `${data.shipment.destination_city ?? ""} ${data.shipment.destination_country ?? ""}`.trim() ||
                  "—"
                }
              />
            </InfoCard>
            <InfoCard title="Details">
              <Row label="Service" value={data.shipment.service_type ?? "—"} />
              <Row
                label="Weight"
                value={data.shipment.total_weight ? `${data.shipment.total_weight} kg` : "—"}
              />
              <Row
                label="Estimated delivery"
                value={formatDateTime(data.shipment.estimated_delivery)}
              />
              <Row label="Status" value={si.label} />
            </InfoCard>
            <div className="rounded-2xl border border-border bg-surface/60 p-6">
              <h3 className="font-semibold">Delivery preferences</h3>
              <p className="mt-2 text-sm text-muted-foreground">
                Reschedule, redirect, or leave delivery instructions for this shipment.
              </p>
              <button className="mt-4 w-full rounded-md border border-border py-2.5 text-sm font-medium hover:bg-surface">
                Manage delivery
              </button>
            </div>
          </div>
        </div>
      )}
    </section>
  );
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
      <dd className="text-right font-medium">{value}</dd>
    </div>
  );
}

function RouteRow({
  icon: Icon,
  label,
  value,
}: {
  icon: typeof MapPin;
  label: string;
  value: string;
}) {
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
