import { createFileRoute, Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/use-auth";
import { useServerFn } from "@tanstack/react-start";
import { updateShipmentStatus } from "@/lib/alerts.functions";
import { toast } from "sonner";
import { Users, Package, DollarSign, Newspaper, ShieldOff } from "lucide-react";


export const Route = createFileRoute("/_authenticated/admin")({
  head: () => ({ meta: [{ title: "Admin — American Shipping & Logistics" }] }),
  component: AdminPage,
});

type Tab = "shipments" | "pricing" | "users" | "content";

function AdminPage() {
  const { isAdmin, loading } = useAuth();
  const [tab, setTab] = useState<Tab>("shipments");

  if (loading) return <div className="container-x py-16">Loading…</div>;
  if (!isAdmin) {
    return (
      <div className="container-x py-16 text-center">
        <ShieldOff className="mx-auto h-8 w-8 text-red-500" />
        <h1 className="mt-4 font-display text-2xl font-bold">Admin access required</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          You're signed in but not an admin. Ask an existing admin to grant you the role.
        </p>
        <Link to="/dashboard" className="mt-6 inline-block rounded-sm border border-border px-4 py-2 text-sm">Back to dashboard</Link>
      </div>
    );
  }

  return (
    <section className="container-x py-12">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Admin</p>
      <h1 className="mt-2 font-display text-4xl font-bold">Control center</h1>

      <div className="mt-6 flex flex-wrap gap-2 border-b border-border">
        {(
          [
            { k: "shipments", l: "Shipments", i: Package },
            { k: "pricing", l: "Pricing", i: DollarSign },
            { k: "users", l: "Users & roles", i: Users },
            { k: "content", l: "Content", i: Newspaper },
          ] as const
        ).map(({ k, l, i: Icon }) => (
          <button
            key={k}
            onClick={() => setTab(k)}
            className={`inline-flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-semibold ${
              tab === k ? "border-brand text-foreground" : "border-transparent text-muted-foreground hover:text-foreground"
            }`}
          >
            <Icon className="h-4 w-4" /> {l}
          </button>
        ))}
      </div>

      <div className="mt-8">
        {tab === "shipments" && <ShipmentsAdmin />}
        {tab === "pricing" && <PricingAdmin />}
        {tab === "users" && <UsersAdmin />}
        {tab === "content" && <ContentAdmin />}
      </div>
    </section>
  );
}

// ------------- Shipments -------------
function ShipmentsAdmin() {
  const [items, setItems] = useState<any[]>([]);
  const [busy, setBusy] = useState(false);
  const updateStatusFn = useServerFn(updateShipmentStatus);

  const load = async () => {
    const { data } = await supabase
      .from("shipments")
      .select("*")
      .order("created_at", { ascending: false })
      .limit(200);
    setItems(data ?? []);
  };
  useEffect(() => {
    load();
  }, []);

  const updateStatus = async (id: string, tracking_code: string, _from: string, _to: string, status: string) => {
    setBusy(true);
    try {
      const res = await updateStatusFn({ data: { shipment_id: id, status: status as any } });
      const pushMsg = res.channels.push.sent > 0 ? ` · ${res.channels.push.sent} push sent` : "";
      toast.success(`${tracking_code} → ${status}${pushMsg}`);
      await load();
    } catch (e: any) {
      toast.error(e?.message ?? "Update failed");
    }
    setBusy(false);
  };

  const STATUSES = ["booked", "picked_up", "in_transit", "out_for_delivery", "delivered", "exception", "cancelled"];


  return (
    <div className="rounded-2xl border border-border bg-surface/60 p-6">
      <div className="mb-4 flex items-center justify-between">
        <h2 className="font-display text-lg font-semibold">All shipments</h2>
        <span className="text-xs text-muted-foreground">{items.length} shown</span>
      </div>
      {items.length === 0 ? (
        <p className="text-sm text-muted-foreground">No shipments yet.</p>
      ) : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead className="text-left text-xs uppercase tracking-wider text-muted-foreground">
              <tr>
                <th className="pb-2 pr-4">Code</th>
                <th className="pb-2 pr-4">Route</th>
                <th className="pb-2 pr-4">Price</th>
                <th className="pb-2 pr-4">Status</th>
                <th className="pb-2">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {items.map((s) => (
                <tr key={s.id}>
                  <td className="py-2 pr-4 font-mono text-xs">{s.tracking_code}</td>
                  <td className="py-2 pr-4">{s.from_location} → {s.to_location}</td>
                  <td className="py-2 pr-4">${Number(s.price).toFixed(2)}</td>
                  <td className="py-2 pr-4">
                    <select
                      value={s.status}
                      disabled={busy}
                      onChange={(e) => updateStatus(s.id, s.tracking_code, s.from_location, s.to_location, e.target.value)}
                      className="rounded border border-border bg-background px-2 py-1 text-xs"
                    >
                      {STATUSES.map((st) => (
                        <option key={st} value={st}>{st.replace(/_/g, " ")}</option>
                      ))}
                    </select>
                  </td>
                  <td className="py-2">
                    <Link to="/track" search={{ id: s.tracking_code }} className="text-brand hover:underline">View</Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

// ------------- Pricing -------------
function PricingAdmin() {
  const [rules, setRules] = useState<any | null>(null);
  const [busy, setBusy] = useState(false);
  useEffect(() => {
    supabase
      .from("pricing_rules")
      .select("*")
      .eq("is_active", true)
      .maybeSingle()
      .then(({ data }) => setRules(data));
  }, []);
  if (!rules) return <div className="text-sm text-muted-foreground">Loading…</div>;

  const save = async () => {
    setBusy(true);
    const { error } = await supabase.from("pricing_rules").update(rules).eq("id", rules.id);
    setBusy(false);
    if (error) toast.error(error.message);
    else toast.success("Pricing updated");
  };

  const fields: [keyof typeof rules, string][] = [
    ["base_fee", "Base fee"],
    ["per_kg", "Per kg"],
    ["volumetric_divisor", "Volumetric divisor"],
    ["standard_multiplier", "Standard ×"],
    ["express_multiplier", "Express ×"],
    ["priority_multiplier", "Priority ×"],
    ["insurance_fee", "Insurance flat fee"],
    ["insurance_pct", "Insurance % of value"],
    ["fuel_surcharge_pct", "Fuel surcharge %"],
  ];

  return (
    <div className="rounded-2xl border border-border bg-surface/60 p-6">
      <h2 className="font-display text-lg font-semibold">Pricing rules</h2>
      <p className="mt-1 text-sm text-muted-foreground">These values feed the customer quote calculator in real time.</p>
      <div className="mt-6 grid gap-4 md:grid-cols-3">
        {fields.map(([k, label]) => (
          <label key={String(k)} className="block">
            <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
            <input
              type="number"
              step="0.01"
              value={rules[k] ?? 0}
              onChange={(e) => setRules({ ...rules, [k]: Number(e.target.value) })}
              className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm"
            />
          </label>
        ))}
      </div>
      <button
        onClick={save}
        disabled={busy}
        className="mt-6 rounded-sm bg-accent px-6 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 disabled:opacity-60"
      >
        {busy ? "Saving…" : "Save pricing"}
      </button>
    </div>
  );
}

// ------------- Users -------------
function UsersAdmin() {
  const [roles, setRoles] = useState<any[]>([]);
  const [email, setEmail] = useState("");
  const [busy, setBusy] = useState(false);

  const load = async () => {
    const { data } = await supabase
      .from("user_roles")
      .select("id, user_id, role, created_at")
      .order("created_at", { ascending: false });
    setRoles(data ?? []);
  };
  useEffect(() => {
    load();
  }, []);

  const grantAdmin = async () => {
    if (!email) return;
    setBusy(true);
    const { error } = await supabase.rpc("admin_grant_role", { _target_email: email, _role: "admin" });
    setBusy(false);
    if (error) toast.error(error.message);
    else {
      toast.success(`Granted admin to ${email}`);
      setEmail("");
      load();
    }
  };
  const revoke = async (user_id: string, role: string) => {
    const { error } = await supabase.rpc("admin_revoke_role", { _target_user: user_id, _role: role as "admin" });
    if (error) toast.error(error.message);
    else {
      toast.success("Role revoked");
      load();
    }
  };

  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-border bg-surface/60 p-6">
        <h2 className="font-display text-lg font-semibold">Grant role by email</h2>
        <div className="mt-4 flex flex-col gap-2 sm:flex-row">
          <input
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="user@example.com"
            className="flex-1 rounded-lg border border-border bg-background px-3 py-2 text-sm"
          />
          <button
            onClick={grantAdmin}
            disabled={busy || !email}
            className="rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 disabled:opacity-60"
          >
            {busy ? "…" : "Grant admin"}
          </button>
        </div>
        <p className="mt-2 text-xs text-muted-foreground">
          The user must have already signed up. To grant staff instead, adjust the role in your DB (or extend this UI).
        </p>
      </div>

      <div className="rounded-2xl border border-border bg-surface/60 p-6">
        <h2 className="font-display text-lg font-semibold">Role assignments</h2>
        <ul className="mt-4 divide-y divide-border text-sm">
          {roles.map((r) => (
            <li key={r.id} className="flex items-center justify-between py-2">
              <div>
                <div className="font-mono text-xs">{r.user_id}</div>
                <div className="text-xs text-muted-foreground">
                  <span className="rounded-full bg-brand/20 px-2 py-0.5 text-xs font-semibold text-brand">{r.role}</span>
                  <span className="ml-2">{new Date(r.created_at).toLocaleDateString()}</span>
                </div>
              </div>
              {r.role !== "customer" && (
                <button onClick={() => revoke(r.user_id, r.role)} className="text-xs text-red-500 hover:underline">
                  Revoke
                </button>
              )}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}

// ------------- Content -------------
function ContentAdmin() {
  const [bulletins, setBulletins] = useState<any[]>([]);
  const [title, setTitle] = useState("");
  const [body, setBody] = useState("");
  const [severity, setSeverity] = useState<"info" | "warning" | "critical">("info");
  const load = async () => {
    const { data } = await supabase.from("service_bulletins").select("*").order("created_at", { ascending: false });
    setBulletins(data ?? []);
  };
  useEffect(() => {
    load();
  }, []);
  const create = async () => {
    if (!title) return;
    const { error } = await supabase.from("service_bulletins").insert({ title, body, severity, active: true });
    if (error) toast.error(error.message);
    else {
      toast.success("Bulletin posted");
      setTitle("");
      setBody("");
      load();
    }
  };
  const toggle = async (id: string, active: boolean) => {
    await supabase.from("service_bulletins").update({ active: !active }).eq("id", id);
    load();
  };
  const remove = async (id: string) => {
    await supabase.from("service_bulletins").delete().eq("id", id);
    load();
  };

  return (
    <div className="space-y-6">
      <div className="rounded-2xl border border-border bg-surface/60 p-6">
        <h2 className="font-display text-lg font-semibold">New service bulletin</h2>
        <div className="mt-4 grid gap-3">
          <input value={title} onChange={(e) => setTitle(e.target.value)} placeholder="Title" className="rounded-lg border border-border bg-background px-3 py-2 text-sm" />
          <textarea value={body} onChange={(e) => setBody(e.target.value)} placeholder="Body (optional)" rows={3} className="rounded-lg border border-border bg-background px-3 py-2 text-sm" />
          <div className="flex items-center gap-3">
            <select value={severity} onChange={(e) => setSeverity(e.target.value as "info" | "warning" | "critical")} className="rounded-lg border border-border bg-background px-3 py-2 text-sm">
              <option value="info">Info</option>
              <option value="warning">Warning</option>
              <option value="critical">Critical</option>
            </select>
            <button onClick={create} className="rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">
              Publish
            </button>
          </div>
        </div>
      </div>

      <div className="rounded-2xl border border-border bg-surface/60 p-6">
        <h2 className="font-display text-lg font-semibold">Existing bulletins</h2>
        <ul className="mt-4 divide-y divide-border text-sm">
          {bulletins.map((b) => (
            <li key={b.id} className="flex items-start justify-between gap-4 py-3">
              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                  <span className={`rounded-full px-2 py-0.5 text-xs font-semibold ${
                    b.severity === "critical" ? "bg-red-500/20 text-red-500" :
                    b.severity === "warning" ? "bg-yellow-500/20 text-yellow-600" :
                    "bg-brand/20 text-brand"
                  }`}>{b.severity}</span>
                  <span className="font-semibold">{b.title}</span>
                  {!b.active && <span className="text-xs text-muted-foreground">(hidden)</span>}
                </div>
                {b.body && <p className="mt-1 text-xs text-muted-foreground">{b.body}</p>}
              </div>
              <div className="flex gap-2">
                <button onClick={() => toggle(b.id, b.active)} className="text-xs text-muted-foreground hover:underline">
                  {b.active ? "Hide" : "Show"}
                </button>
                <button onClick={() => remove(b.id)} className="text-xs text-red-500 hover:underline">Delete</button>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
