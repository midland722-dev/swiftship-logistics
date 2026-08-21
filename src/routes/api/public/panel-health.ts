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

// Transient Railway/proxy failures (502/503/504/522/524) and network errors are
// retried with exponential backoff + jitter before a check is marked down.
const MAX_ATTEMPTS = 3;
const BASE_DELAY_MS = 400;
const TRANSIENT_STATUSES = new Set([408, 425, 429, 500, 502, 503, 504, 520, 521, 522, 523, 524]);

const sleep = (ms: number) => new Promise((r) => setTimeout(r, ms));

function backoffDelay(attempt: number) {
  const exponential = BASE_DELAY_MS * 2 ** (attempt - 1);
  return Math.round(exponential + Math.random() * BASE_DELAY_MS);
}

async function probe(path: string, label: string) {
  const startedAt = Date.now();
  let lastError: string | undefined;
  let lastStatus = 0;
  let lastLocation: string | null = null;

  for (let attempt = 1; attempt <= MAX_ATTEMPTS; attempt++) {
    try {
      const res = await fetch(`${PANEL_ORIGIN}${path}`, {
        method: "GET",
        redirect: "manual",
        headers: { "user-agent": "ascl-panel-healthcheck" },
        signal: AbortSignal.timeout(8000),
      });
      lastStatus = res.status;
      lastLocation = res.headers.get("location");
      lastError = undefined;

      const transient = TRANSIENT_STATUSES.has(res.status);
      if (!transient) {
        return {
          path,
          label,
          ok: res.status < 500,
          status: res.status,
          location: lastLocation,
          attempts: attempt,
          ms: Date.now() - startedAt,
        };
      }
    } catch (error) {
      lastStatus = 0;
      lastError = error instanceof Error ? error.message : String(error);
    }

    if (attempt < MAX_ATTEMPTS) await sleep(backoffDelay(attempt));
  }

  return {
    path,
    label,
    ok: lastStatus > 0 && lastStatus < 500,
    status: lastStatus,
    location: lastLocation,
    ...(lastError ? { error: lastError } : {}),
    attempts: MAX_ATTEMPTS,
    ms: Date.now() - startedAt,
  };
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
