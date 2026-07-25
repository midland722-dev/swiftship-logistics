import { createFileRoute, useNavigate } from "@tanstack/react-router";
<<<<<<< HEAD
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
=======
import { useEffect, useState } from "react";
import { z } from "zod";
import { Package, MapPin, CheckCircle2 } from "lucide-react";
import { supabase } from "@/integrations/supabase/client";

const searchSchema = z.object({ id: z.string().optional() });
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432

export const Route = createFileRoute("/track")({
  validateSearch: searchSchema,
  head: () => ({
    meta: [
<<<<<<< HEAD
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
=======
      { title: "Track shipment — Voltra" },
      { name: "description", content: "Live tracking for Voltra parcels and freight worldwide." },
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
    ],
    links: [{ rel: "canonical", href: "/track" }],
  }),
  component: TrackPage,
});

<<<<<<< HEAD
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

=======
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
function TrackPage() {
  const { id } = Route.useSearch();
  const navigate = useNavigate();
  const [input, setInput] = useState(id ?? "");
<<<<<<< HEAD
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
=======
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
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432

  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Live tracking</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Where's my shipment?</h1>

      <form
        onSubmit={(e) => {
          e.preventDefault();
<<<<<<< HEAD
          navigate({ to: "/track", search: { id: input || "VLT-0000000" } });
=======
          if (input.trim()) navigate({ to: "/track", search: { id: input.trim() } });
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
        }}
        className="mt-8 flex max-w-2xl flex-col gap-3 rounded-2xl border border-border bg-surface/60 p-4 sm:flex-row"
      >
        <input
          value={input}
          onChange={(e) => setInput(e.target.value)}
<<<<<<< HEAD
          placeholder="Enter tracking number, e.g. VLT-4820193"
          className="flex-1 rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none placeholder:text-muted-foreground/70 focus:border-brand"
=======
          placeholder="Enter tracking number, e.g. VLT-1234567"
          className="flex-1 rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none focus:border-brand"
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
        />
        <button className="rounded-sm bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
          Track
        </button>
      </form>

      {!id && (
<<<<<<< HEAD
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
=======
        <p className="mt-6 text-sm text-muted-foreground">Enter a tracking number to view live status.</p>
      )}
      {id && loading && <p className="mt-6 text-sm text-muted-foreground">Looking up {id}…</p>}
      {id && !loading && notFound && (
        <p className="mt-6 text-sm text-muted-foreground">
          No shipment found for <span className="font-mono">{id}</span>.
        </p>
      )}

      {shipment && (
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
        <div className="mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
          <div className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
            <div className="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-4">
              <div className="min-w-0">
<<<<<<< HEAD
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
=======
                <div className="font-mono text-xs text-muted-foreground">{shipment.tracking_code}</div>
                <h2 className="mt-1 truncate font-display text-2xl font-bold">
                  {shipment.status.replace(/_/g, " ")}
                  {shipment.eta && ` — arriving ${new Date(shipment.eta).toLocaleDateString()}`}
                </h2>
              </div>
              <StatusPill status={shipment.status} />
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
            </div>

            <div className="mt-6">
              <div className="flex items-center justify-between text-xs text-muted-foreground">
<<<<<<< HEAD
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
=======
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
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
            </ol>
          </div>

          <div className="space-y-4">
            <InfoCard title="Route">
<<<<<<< HEAD
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
=======
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
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
          </div>
        </div>
      )}
    </section>
  );
}

<<<<<<< HEAD
=======
function StatusPill({ status }: { status: string }) {
  const cls =
    status === "delivered"
      ? "bg-emerald-500/20 text-emerald-600"
      : status === "exception" || status === "cancelled"
        ? "bg-red-500/20 text-red-500"
        : "bg-brand/20 text-brand";
  return <span className={`shrink-0 rounded-full px-3 py-1 text-xs font-semibold ${cls}`}>{status.replace(/_/g, " ")}</span>;
}
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
function InfoCard({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div className="rounded-2xl border border-border bg-surface/60 p-6">
      <h3 className="font-semibold">{title}</h3>
      <dl className="mt-4 space-y-3">{children}</dl>
    </div>
  );
}
<<<<<<< HEAD

=======
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
function Row({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex justify-between gap-4 text-sm">
      <dt className="text-muted-foreground">{label}</dt>
<<<<<<< HEAD
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
=======
      <dd className="text-right font-medium capitalize">{value}</dd>
    </div>
  );
}
function RouteRow({ icon: Icon, label, value }: { icon: typeof MapPin; label: string; value: string }) {
>>>>>>> d6566b98f07a254d41597cb77ffaa074e06a4432
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
