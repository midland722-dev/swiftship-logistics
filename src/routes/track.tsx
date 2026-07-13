import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useState } from "react";
import { z } from "zod";
import { Package, MapPin, CheckCircle2, Circle, Truck, Plane } from "lucide-react";

const searchSchema = z.object({
  id: z.string().optional(),
});

export const Route = createFileRoute("/track")({
  validateSearch: searchSchema,
  head: () => ({
    meta: [
      { title: "Track shipment — Voltra" },
      { name: "description", content: "Live tracking for Voltra parcels and freight worldwide." },
      { property: "og:title", content: "Track shipment — Voltra" },
      { property: "og:description", content: "Real-time shipment status, ETA, and proof of delivery." },
    ],
    links: [{ rel: "canonical", href: "/track" }],
  }),
  component: TrackPage,
});

type Event = { time: string; date: string; label: string; location: string; done: boolean; icon: typeof CheckCircle2 };

function buildMockShipment(id: string) {
  return {
    id,
    from: "Berlin, DE",
    to: "Tokyo, JP",
    service: "Voltra Express International",
    weight: "2.4 kg",
    eta: "Wed, 3:40 PM local",
    progress: 68,
    recipient: "K. Tanaka",
    events: [
      { time: "16:42", date: "Mon 12 Aug", label: "Shipment booked", location: "Berlin, DE", done: true, icon: CheckCircle2 },
      { time: "09:15", date: "Tue 13 Aug", label: "Picked up", location: "Berlin depot, DE", done: true, icon: Truck },
      { time: "22:04", date: "Tue 13 Aug", label: "Departed origin hub", location: "BER airport, DE", done: true, icon: Plane },
      { time: "07:20", date: "Wed 14 Aug", label: "Arrived transit hub", location: "Dubai, AE", done: true, icon: Plane },
      { time: "—", date: "Wed 14 Aug", label: "Out for delivery", location: "Tokyo, JP", done: false, icon: Truck },
      { time: "—", date: "Wed 14 Aug", label: "Delivered", location: "Tokyo, JP", done: false, icon: CheckCircle2 },
    ] as Event[],
  };
}

function TrackPage() {
  const { id } = Route.useSearch();
  const navigate = useNavigate();
  const [input, setInput] = useState(id ?? "");

  const shipment = id ? buildMockShipment(id) : null;

  return (
    <section className="container-x py-16 md:py-20">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Live tracking</p>
      <h1 className="mt-2 font-display text-4xl font-bold md:text-5xl">Where's my shipment?</h1>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          navigate({ to: "/track", search: { id: input || "VLT-4820193" } });
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

      {!shipment ? (
        <p className="mt-6 text-sm text-muted-foreground">
          Enter a tracking number to see live status. Try <button onClick={() => { setInput("VLT-4820193"); navigate({ to: "/track", search: { id: "VLT-4820193" } }); }} className="text-brand underline underline-offset-2">VLT-4820193</button>.
        </p>
      ) : (
        <div className="mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
          <div className="rounded-2xl border border-border bg-surface/60 p-6 md:p-8">
            <div className="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-4">
              <div className="min-w-0">
                <div className="font-mono text-xs text-muted-foreground">{shipment.id}</div>
                <h2 className="mt-1 truncate font-display text-2xl font-bold">In transit — arriving {shipment.eta}</h2>
              </div>
              <span className="shrink-0 rounded-full bg-brand/20 px-3 py-1 text-xs font-semibold text-brand">
                On time
              </span>
            </div>

            <div className="mt-6">
              <div className="flex items-center justify-between text-xs text-muted-foreground">
                <span>{shipment.from}</span>
                <span>{shipment.to}</span>
              </div>
              <div className="mt-2 h-2 overflow-hidden rounded-full bg-background">
                <div className="h-full rounded-full bg-brand" style={{ width: `${shipment.progress}%` }} />
              </div>
              <div className="mt-1 text-right font-mono text-xs text-brand">{shipment.progress}%</div>
            </div>

            <ol className="mt-8 space-y-4">
              {shipment.events.map((ev, i) => {
                const Icon = ev.icon;
                return (
                  <li key={i} className="flex gap-4">
                    <div className="flex flex-col items-center">
                      <div className={`grid h-9 w-9 shrink-0 place-items-center rounded-full border ${ev.done ? "border-brand bg-brand text-brand-foreground" : "border-border bg-background text-muted-foreground"}`}>
                        {ev.done ? <Icon className="h-4 w-4" /> : <Circle className="h-3 w-3" />}
                      </div>
                      {i < shipment.events.length - 1 && (
                        <div className={`mt-1 h-8 w-px ${ev.done ? "bg-brand/50" : "bg-border"}`} />
                      )}
                    </div>
                    <div className="min-w-0 flex-1 pb-2">
                      <div className={`text-sm font-medium ${ev.done ? "text-foreground" : "text-muted-foreground"}`}>
                        {ev.label}
                      </div>
                      <div className="mt-0.5 text-xs text-muted-foreground">
                        {ev.location} · {ev.date} {ev.time !== "—" && `· ${ev.time}`}
                      </div>
                    </div>
                  </li>
                );
              })}
            </ol>
          </div>

          <div className="space-y-4">
            <InfoCard title="Route">
              <RouteRow icon={MapPin} label="From" value={shipment.from} />
              <RouteRow icon={MapPin} label="To" value={shipment.to} />
              <RouteRow icon={Package} label="Recipient" value={shipment.recipient} />
            </InfoCard>
            <InfoCard title="Details">
              <Row label="Service" value={shipment.service} />
              <Row label="Weight" value={shipment.weight} />
              <Row label="Estimated delivery" value={shipment.eta} />
              <Row label="Insurance" value="$500 included" />
            </InfoCard>
            <div className="rounded-2xl border border-border bg-surface/60 p-6">
              <h3 className="font-semibold">Delivery preferences</h3>
              <p className="mt-2 text-sm text-muted-foreground">Reschedule, redirect, or leave delivery instructions for this shipment.</p>
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
