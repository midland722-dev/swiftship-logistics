import { createFileRoute } from "@tanstack/react-router";
import { RefreshCw, CheckCircle2, XCircle } from "lucide-react";
import { usePanelHealth } from "@/hooks/use-panel-health";

export const Route = createFileRoute("/status")({
  head: () => ({
    meta: [
      { title: "System Status — American Shipping & Logistics" },
      {
        name: "description",
        content:
          "Internal status dashboard with live availability and latency checks for the panel host and key /php and /deprixa endpoints.",
      },
      { property: "og:title", content: "System Status — American Shipping & Logistics" },
      {
        property: "og:description",
        content: "Live availability and latency for panel and legacy endpoints.",
      },
      { name: "robots", content: "noindex" },
    ],
  }),
  component: StatusPage,
});

function StatusPage() {
  const { data, isFetching, refetch, error } = usePanelHealth(30_000);

  const state = !data ? "unknown" : data.healthy ? "healthy" : data.degraded ? "degraded" : "down";
  const tone =
    state === "healthy"
      ? "bg-emerald-500/10 text-emerald-700 border-emerald-500/30"
      : state === "degraded"
        ? "bg-amber-500/10 text-amber-700 border-amber-500/30"
        : state === "down"
          ? "bg-red-500/10 text-red-700 border-red-500/30"
          : "bg-surface text-muted-foreground border-border";

  return (
    <div className="container-x py-12">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p className="font-mono text-xs uppercase tracking-widest text-brand">Internal</p>
          <h1 className="mt-1 font-display text-3xl font-bold">System status</h1>
          <p className="mt-2 text-sm text-muted-foreground">
            Live checks against the panel host and key /php and /deprixa endpoints.
          </p>
        </div>
        <button
          onClick={() => refetch()}
          className="inline-flex items-center gap-2 rounded-sm border border-border px-3 py-2 text-sm font-semibold hover:bg-surface"
        >
          <RefreshCw className={`h-4 w-4 ${isFetching ? "animate-spin" : ""}`} /> Refresh
        </button>
      </div>

      <div className={`mt-6 rounded-sm border px-4 py-3 text-sm font-semibold uppercase tracking-wide ${tone}`}>
        {state === "healthy" && "All panel endpoints operational"}
        {state === "degraded" && "Partial outage — some panel endpoints failing"}
        {state === "down" && "Panel host unreachable (503)"}
        {state === "unknown" && (error ? "Status check failed" : "Checking…")}
      </div>

      {data && (
        <>
          <p className="mt-3 font-mono text-xs text-muted-foreground">
            {data.panelOrigin} · last checked {new Date(data.checkedAt).toLocaleTimeString()}
          </p>

          <div className="mt-6 overflow-x-auto rounded-sm border border-border">
            <table className="w-full text-left text-sm">
              <thead className="bg-surface text-xs uppercase tracking-wider text-muted-foreground">
                <tr>
                  <th className="px-4 py-3">Endpoint</th>
                  <th className="px-4 py-3">Check</th>
                  <th className="px-4 py-3">HTTP</th>
                  <th className="px-4 py-3">Latency</th>
                  <th className="px-4 py-3">Detail</th>
                </tr>
              </thead>
              <tbody>
                {data.checks.map((c) => (
                  <tr key={c.path} className="border-t border-border/60">
                    <td className="px-4 py-3 font-mono text-xs">{c.path}</td>
                    <td className="px-4 py-3">
                      <span className="inline-flex items-center gap-1.5">
                        {c.ok ? (
                          <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                        ) : (
                          <XCircle className="h-4 w-4 text-red-600" />
                        )}
                        {c.label}
                      </span>
                    </td>
                    <td className="px-4 py-3 font-mono text-xs">{c.status || "—"}</td>
                    <td className="px-4 py-3 font-mono text-xs">{c.ms} ms</td>
                    <td className="px-4 py-3 text-xs text-muted-foreground">
                      {c.error ?? c.location ?? (c.ok ? "OK" : "Upstream error")}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  );
}
