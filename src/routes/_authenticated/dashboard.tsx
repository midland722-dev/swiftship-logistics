import { createFileRoute, Link, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/use-auth";
import { enablePushForCurrentUser, disablePushForCurrentUser, pushSupported } from "@/lib/push-client";
import { Package, Plus, Bell, Settings, Shield } from "lucide-react";
import { toast } from "sonner";


export const Route = createFileRoute("/_authenticated/dashboard")({
  head: () => ({ meta: [{ title: "Dashboard — American Shipping & Logistics" }] }),
  component: Dashboard,
});

interface Shipment {
  id: string;
  tracking_code: string;
  from_location: string;
  to_location: string;
  status: string;
  price: number;
  created_at: string;
  service_speed: string;
}

interface Quote {
  id: string;
  from_location: string;
  to_location: string;
  price: number;
  service_speed: string;
  created_at: string;
}

interface Prefs {
  email_enabled: boolean;
  sms_enabled: boolean;
  push_enabled: boolean;
  phone_e164: string | null;
}

function Dashboard() {
  const { user, isAdmin } = useAuth();
  const navigate = useNavigate();
  const [shipments, setShipments] = useState<Shipment[]>([]);
  const [quotes, setQuotes] = useState<Quote[]>([]);
  const [prefs, setPrefs] = useState<Prefs>({
    email_enabled: true,
    sms_enabled: false,
    push_enabled: false,
    phone_e164: "",
  });
  const [savingPrefs, setSavingPrefs] = useState(false);
  const [onboardingRedirected, setOnboardingRedirected] = useState(false);

  useEffect(() => {
    if (!user) return;
    (async () => {
      const { data: profile } = await supabase
        .from("profiles")
        .select("onboarded_at")
        .eq("id", user.id)
        .maybeSingle();
      if (profile && !profile.onboarded_at && !onboardingRedirected) {
        setOnboardingRedirected(true);
        navigate({ to: "/onboarding" });
        return;
      }
      const [{ data: s }, { data: q }, { data: p }] = await Promise.all([
        supabase
          .from("shipments")
          .select("*")
          .eq("owner_id", user.id)
          .order("created_at", { ascending: false })
          .limit(50),
        supabase
          .from("quotes")
          .select("*")
          .eq("owner_id", user.id)
          .order("created_at", { ascending: false })
          .limit(20),
        supabase
          .from("shipment_alert_prefs")
          .select("*")
          .eq("user_id", user.id)
          .maybeSingle(),
      ]);
      setShipments((s ?? []) as Shipment[]);
      setQuotes((q ?? []) as Quote[]);
      if (p) setPrefs(p as Prefs);
    })();
  }, [user, navigate, onboardingRedirected]);

  const handlePushToggle = async (v: boolean) => {
    if (v) {
      if (!pushSupported()) {
        toast.error("Web push isn't supported in this browser.");
        return;
      }
      const res = await enablePushForCurrentUser();
      if (!res.ok) {
        toast.error(res.reason ?? "Couldn't enable push");
        return;
      }
      toast.success("Web push enabled on this device");
      setPrefs((p) => ({ ...p, push_enabled: true }));
    } else {
      await disablePushForCurrentUser();
      setPrefs((p) => ({ ...p, push_enabled: false }));
    }
  };

  const savePrefs = async () => {
    if (!user) return;
    setSavingPrefs(true);
    const { error } = await supabase.from("shipment_alert_prefs").upsert({
      user_id: user.id,
      ...prefs,
    });
    setSavingPrefs(false);
    if (error) toast.error(error.message);
    else toast.success("Alert preferences saved");
  };


  return (
    <section className="container-x py-12">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <p className="font-mono text-xs uppercase tracking-widest text-brand">Dashboard</p>
          <h1 className="mt-2 font-display text-4xl font-bold">
            Welcome{user?.user_metadata?.full_name ? `, ${user.user_metadata.full_name}` : ""}
          </h1>
        </div>
        <div className="flex gap-2">
          {isAdmin && (
            <Link
              to="/admin" search={{ next: undefined }}
              className="inline-flex items-center gap-2 rounded-sm border border-border px-4 py-2 text-sm font-semibold hover:bg-surface"
            >
              <Shield className="h-4 w-4" /> Admin
            </Link>
          )}
          <Link
            to="/quote"
            className="inline-flex items-center gap-2 rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90"
          >
            <Plus className="h-4 w-4" /> New shipment
          </Link>
        </div>
      </div>

      <div className="mt-10 grid gap-6 lg:grid-cols-3">
        <div className="rounded-2xl border border-border bg-surface/60 p-6 lg:col-span-2">
          <div className="flex items-center justify-between">
            <h2 className="font-display text-xl font-semibold">Your shipments</h2>
            <span className="text-xs text-muted-foreground">{shipments.length} total</span>
          </div>
          {shipments.length === 0 ? (
            <p className="mt-6 text-sm text-muted-foreground">
              No shipments yet. <Link to="/quote" className="text-brand underline">Book your first one</Link>.
            </p>
          ) : (
            <ul className="mt-4 divide-y divide-border">
              {shipments.map((s) => (
                <li key={s.id} className="flex flex-wrap items-center justify-between gap-3 py-3">
                  <div className="min-w-0 flex-1">
                    <Link
                      to="/track"
                      search={{ id: s.tracking_code }}
                      className="font-mono text-xs text-brand hover:underline"
                    >
                      {s.tracking_code}
                    </Link>
                    <div className="text-sm font-medium">
                      {s.from_location} → {s.to_location}
                    </div>
                    <div className="text-xs text-muted-foreground">
                      {s.service_speed} · {new Date(s.created_at).toLocaleDateString()}
                    </div>
                  </div>
                  <div className="flex items-center gap-3">
                    <StatusBadge status={s.status} />
                    <span className="text-sm font-semibold">${Number(s.price).toFixed(2)}</span>
                  </div>
                </li>
              ))}
            </ul>
          )}
        </div>

        <div className="space-y-6">
          <div className="rounded-2xl border border-border bg-surface/60 p-6">
            <div className="flex items-center gap-2">
              <Bell className="h-4 w-4 text-brand" />
              <h2 className="font-display font-semibold">Alert preferences</h2>
            </div>
            <div className="mt-4 space-y-3 text-sm">
              <ToggleRow
                label="Email"
                checked={prefs.email_enabled}
                onChange={(v) => setPrefs({ ...prefs, email_enabled: v })}
              />
              <ToggleRow
                label="SMS"
                checked={prefs.sms_enabled}
                onChange={(v) => setPrefs({ ...prefs, sms_enabled: v })}
              />
              {prefs.sms_enabled && (
                <input
                  placeholder="+15551234567"
                  value={prefs.phone_e164 ?? ""}
                  onChange={(e) => setPrefs({ ...prefs, phone_e164: e.target.value })}
                  className="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm outline-none focus:border-brand"
                />
              )}
              <ToggleRow
                label="Web push"
                checked={prefs.push_enabled}
                onChange={handlePushToggle}
              />

            </div>
            <button
              onClick={savePrefs}
              disabled={savingPrefs}
              className="mt-4 w-full rounded-sm bg-accent py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 disabled:opacity-60"
            >
              {savingPrefs ? "Saving…" : "Save preferences"}
            </button>
          </div>

          <div className="rounded-2xl border border-border bg-surface/60 p-6">
            <div className="flex items-center gap-2">
              <Package className="h-4 w-4 text-brand" />
              <h2 className="font-display font-semibold">Saved quotes</h2>
            </div>
            {quotes.length === 0 ? (
              <p className="mt-3 text-sm text-muted-foreground">No saved quotes yet.</p>
            ) : (
              <ul className="mt-3 space-y-2 text-sm">
                {quotes.slice(0, 5).map((q) => (
                  <li key={q.id} className="flex items-center justify-between">
                    <span className="truncate">
                      {q.from_location} → {q.to_location}
                    </span>
                    <span className="font-semibold">${Number(q.price).toFixed(2)}</span>
                  </li>
                ))}
              </ul>
            )}
          </div>

          <Link
            to="/onboarding"
            className="inline-flex w-full items-center justify-center gap-2 rounded-sm border border-border py-2 text-sm font-semibold hover:bg-surface"
          >
            <Settings className="h-4 w-4" /> Redo onboarding
          </Link>
        </div>
      </div>
    </section>
  );
}

function ToggleRow({ label, checked, onChange }: { label: string; checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <label className="flex items-center justify-between">
      <span>{label}</span>
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(e.target.checked)}
        className="h-4 w-4 accent-[var(--brand)]"
      />
    </label>
  );
}

function StatusBadge({ status }: { status: string }) {
  const colors: Record<string, string> = {
    booked: "bg-surface text-muted-foreground",
    picked_up: "bg-blue-500/20 text-blue-500",
    in_transit: "bg-brand/20 text-brand",
    out_for_delivery: "bg-yellow-500/20 text-yellow-600",
    delivered: "bg-emerald-500/20 text-emerald-600",
    exception: "bg-red-500/20 text-red-500",
    cancelled: "bg-muted text-muted-foreground",
  };
  return (
    <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${colors[status] ?? colors.booked}`}>
      {status.replace(/_/g, " ")}
    </span>
  );
}
