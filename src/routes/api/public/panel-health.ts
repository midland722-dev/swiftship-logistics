import { createFileRoute } from "@tanstack/react-router";

// Upstream PHP panel that /deprixa/* and /php/* redirect to (see vercel.json).
const PANEL_ORIGIN = "https://php-app-production-7ce0.up.railway.app";

const CHECKS: { path: string; label: string }[] = [
  { path: "/healthz", label: "Panel health probe" },
  { path: "/", label: "Panel root" },
  { path: "/deprixa/login.php", label: "Staff panel login" },
  { path: "/deprixa/index.php", label: "Staff panel dashboard" },
  { path: "/php/index.php", label: "Legacy site index" },
  { path: "/php/api/tracking.php", label: "Legacy tracking API" },
];

async function probe(path: string, label: string) {
  const startedAt = Date.now();
  try {
    const res = await fetch(`${PANEL_ORIGIN}${path}`, {
      method: "GET",
      redirect: "manual",
      headers: { "user-agent": "ascl-panel-healthcheck" },
      signal: AbortSignal.timeout(8000),
    });
    return {
      path,
      label,
      ok: res.status < 500,
      status: res.status,
      location: res.headers.get("location"),
      ms: Date.now() - startedAt,
    };
  } catch (error) {
    return {
      path,
      label,
      ok: false,
      status: 0,
      location: null,
      error: error instanceof Error ? error.message : String(error),
      ms: Date.now() - startedAt,
    };
  }
}

export const Route = createFileRoute("/api/public/panel-health")({
  server: {
    handlers: {
      GET: async () => {
        const checks = await Promise.all(CHECKS.map((c) => probe(c.path, c.label)));
        const healthy = checks.every((c) => c.ok);
        const payload = {
          panelOrigin: PANEL_ORIGIN,
          healthy,
          degraded: !healthy && checks.some((c) => c.ok),
          checkedAt: new Date().toISOString(),
          checks,
        };
        console.error(`[panel-health] ${JSON.stringify(payload)}`);
        return new Response(JSON.stringify(payload, null, 2), {
          status: healthy ? 200 : 503,
          headers: {
            "content-type": "application/json; charset=utf-8",
            "cache-control": "no-store",
          },
        });
      },
    },
  },
});
