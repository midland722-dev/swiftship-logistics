import { AlertTriangle, X } from "lucide-react";
import { useState } from "react";
import { usePanelHealth } from "@/hooks/use-panel-health";

/** Rolling ETA: next quarter-hour after the last check. */
function etaFrom(checkedAt: string) {
  const base = new Date(checkedAt);
  if (Number.isNaN(base.getTime())) return null;
  const eta = new Date(base.getTime() + 30 * 60_000);
  return eta.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

export function PanelStatusBanner() {
  const { data } = usePanelHealth();
  const [dismissed, setDismissed] = useState(false);

  if (!data || data.healthy || dismissed) return null;

  const eta = etaFrom(data.checkedAt);

  return (
    <div className="border-b border-accent/30 bg-accent/10">
      <div className="container-x flex items-start gap-3 py-2.5 text-sm">
        <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0 text-accent" aria-hidden="true" />
        <p className="flex-1 text-foreground">
          <span className="font-semibold">Customer &amp; staff panel maintenance.</span>{" "}
          Panel sign-in and legacy panel links are temporarily unavailable. Tracking, quotes and
          the main site are unaffected.
          {eta ? <> Estimated restore by <span className="font-semibold">{eta}</span>.</> : null}
        </p>
        <button
          onClick={() => setDismissed(true)}
          aria-label="Dismiss maintenance notice"
          className="grid h-6 w-6 shrink-0 place-items-center rounded-sm hover:bg-surface"
        >
          <X className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}

/**
 * Renders a link to the legacy PHP panel, disabled while panel-health reports 503.
 */
export function PanelLink({
  href,
  children,
  className = "",
}: {
  href: string;
  children: React.ReactNode;
  className?: string;
}) {
  const { data } = usePanelHealth();
  const down = data ? !data.healthy : false;

  if (down) {
    return (
      <span
        aria-disabled="true"
        title="Panel temporarily unavailable"
        className={`cursor-not-allowed opacity-50 ${className}`}
      >
        {children}
      </span>
    );
  }

  return (
    <a href={href} className={className}>
      {children}
    </a>
  );
}
