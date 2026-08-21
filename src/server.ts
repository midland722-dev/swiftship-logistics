import "./lib/error-capture";

import { consumeLastCapturedError } from "./lib/error-capture";
import { renderErrorPage } from "./lib/error-page";

type ServerEntry = {
  fetch: (request: Request, env: unknown, ctx: unknown) => Promise<Response> | Response;
};

let serverEntryPromise: Promise<ServerEntry> | undefined;

async function getServerEntry(): Promise<ServerEntry> {
  if (!serverEntryPromise) {
    serverEntryPromise = import("@tanstack/react-start/server-entry").then(
      (m) => (m.default ?? m) as ServerEntry,
    );
  }
  return serverEntryPromise;
}

// A client that navigates away / cancels mid-render surfaces as ECONNRESET or an
// "aborted" error. That is not an app failure — never show the error page for it.
function isClientAbort(error: unknown): boolean {
  if (!error || typeof error !== "object") return false;
  const e = error as { code?: unknown; message?: unknown; name?: unknown; cause?: unknown };
  if (e.code === "ECONNRESET" || e.code === "ABORT_ERR") return true;
  if (e.name === "AbortError") return true;
  if (typeof e.message === "string" && /aborted|ECONNRESET|socket hang up/i.test(e.message)) {
    return true;
  }
  return e.cause ? isClientAbort(e.cause) : false;
}

// h3 swallows in-handler throws into a normal 500 Response with body
// {"unhandled":true,"message":"HTTPError"} — try/catch alone never fires for those.
async function normalizeCatastrophicSsrResponse(
  response: Response,
  request: Request,
): Promise<Response> {
  if (response.status < 500) return response;
  const contentType = response.headers.get("content-type") ?? "";
  if (!contentType.includes("application/json")) return response;

  const body = await response.clone().text();
  if (!isH3SwallowedErrorBody(body)) return response;

  const captured = consumeLastCapturedError();
  if (isClientAbort(captured) || request.signal?.aborted) {
    // Client went away mid-render; nothing to render for.
    return new Response(null, { status: 499 });
  }

  console.error(captured ?? new Error(`h3 swallowed SSR error: ${body}`));
  return new Response(renderErrorPage(), {
    status: 500,
    headers: { "content-type": "text/html; charset=utf-8" },
  });
}


function isH3SwallowedErrorBody(body: string): boolean {
  try {
    const payload = JSON.parse(body) as { unhandled?: unknown; message?: unknown };
    return payload.unhandled === true && payload.message === "HTTPError";
  } catch {
    return false;
  }
}


// Paths handled by vercel.json redirects to the external PHP panel. If one of
// these ever reaches the app, the redirect rule did not fire and the request
// fell through to SSR — that is the exact point where a 502 usually appears.
const PANEL_PREFIXES = ["/deprixa", "/php"];

function logRequest(
  request: Request,
  status: number,
  startedAt: number,
  note?: string,
) {
  const url = new URL(request.url);
  const isPanel = PANEL_PREFIXES.some(
    (p) => url.pathname === p || url.pathname.startsWith(`${p}/`),
  );
  const parts = [
    "[req]",
    request.method,
    url.pathname + url.search,
    `status=${status}`,
    `dur=${Math.round(performance.now() - startedAt)}ms`,
    `host=${url.host}`,
    `ref=${request.headers.get("referer") ?? "-"}`,
    isPanel ? "route=panel-fallthrough(redirect-miss)" : "route=app",
  ];
  if (note) parts.push(`note=${note}`);
  const line = parts.join(" ");
  if (status >= 500 || isPanel) console.error(line);
  else console.log(line);
}

export default {
  async fetch(request: Request, env: unknown, ctx: unknown) {
    const startedAt = performance.now();
    try {
      const handler = await getServerEntry();
      const response = await handler.fetch(request, env, ctx);
      const normalized = await normalizeCatastrophicSsrResponse(response, request);
      logRequest(request, normalized.status, startedAt);
      return normalized;
    } catch (error) {
      if (isClientAbort(error) || request.signal?.aborted) {
        logRequest(request, 499, startedAt, "client-abort");
        return new Response(null, { status: 499 });
      }
      console.error(error);
      logRequest(request, 500, startedAt, "ssr-throw");

      return new Response(renderErrorPage(), {
        status: 500,
        headers: { "content-type": "text/html; charset=utf-8" },
      });
    }
  },
};
