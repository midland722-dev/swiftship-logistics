import { createFileRoute, useNavigate } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/use-auth";
import { Check, User, Bell, Package, Building2 } from "lucide-react";
import { toast } from "sonner";

export const Route = createFileRoute("/_authenticated/onboarding")({
  head: () => ({ meta: [{ title: "Get started — American Shipping & Logistics" }] }),
  component: Onboarding,
});

const STEPS = ["Profile", "Team role", "Alerts", "Template"] as const;

function Onboarding() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [step, setStep] = useState(0);
  const [fullName, setFullName] = useState("");
  const [company, setCompany] = useState("");
  const [phone, setPhone] = useState("");
  const [accountType, setAccountType] = useState<"individual" | "business" | "enterprise">("individual");
  const [emailAlerts, setEmailAlerts] = useState(true);
  const [smsAlerts, setSmsAlerts] = useState(false);
  const [pushAlerts, setPushAlerts] = useState(false);
  const [template, setTemplate] = useState<"documents" | "small_parcel" | "pallet">("small_parcel");
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!user) return;
    supabase
      .from("profiles")
      .select("full_name, company, phone, account_type")
      .eq("id", user.id)
      .maybeSingle()
      .then(({ data }) => {
        if (data) {
          setFullName(data.full_name ?? "");
          setCompany(data.company ?? "");
          setPhone(data.phone ?? "");
          setAccountType((data.account_type as "individual" | "business" | "enterprise") ?? "individual");
        }
      });
  }, [user]);

  const finish = async () => {
    if (!user) return;
    setBusy(true);
    try {
      await supabase.from("profiles").upsert({
        id: user.id,
        full_name: fullName,
        company: company || null,
        phone: phone || null,
        account_type: accountType,
        onboarded_at: new Date().toISOString(),
      });
      await supabase.from("shipment_alert_prefs").upsert({
        user_id: user.id,
        email_enabled: emailAlerts,
        sms_enabled: smsAlerts,
        push_enabled: pushAlerts,
        phone_e164: phone || null,
      });
      const templates = {
        documents: { name: "Documents envelope", weight_kg: 0.2, length_cm: 30, width_cm: 22, height_cm: 2, service_speed: "express" },
        small_parcel: { name: "Small parcel", weight_kg: 2, length_cm: 30, width_cm: 20, height_cm: 15, service_speed: "express" },
        pallet: { name: "Pallet freight", weight_kg: 250, length_cm: 120, width_cm: 100, height_cm: 120, service_speed: "standard" },
      };
      await supabase.from("shipment_templates").insert({
        owner_id: user.id,
        ...templates[template],
      });
      toast.success("You're all set");
      navigate({ to: "/dashboard" });
    } catch (err: any) {
      toast.error(err.message ?? "Something went wrong");
    } finally {
      setBusy(false);
    }
  };

  const next = () => setStep((s) => Math.min(s + 1, STEPS.length - 1));
  const back = () => setStep((s) => Math.max(s - 1, 0));

  return (
    <section className="container-x py-16">
      <p className="font-mono text-xs uppercase tracking-widest text-brand">Get started</p>
      <h1 className="mt-2 font-display text-4xl font-bold">Set up your American Shipping & Logistics account</h1>

      <div className="mt-6 flex gap-2">
        {STEPS.map((s, i) => (
          <div key={s} className="flex flex-1 items-center gap-2">
            <div
              className={`grid h-8 w-8 place-items-center rounded-full text-xs font-bold ${
                i <= step ? "bg-accent text-accent-foreground" : "bg-surface text-muted-foreground"
              }`}
            >
              {i < step ? <Check className="h-4 w-4" /> : i + 1}
            </div>
            <div className={`text-xs ${i === step ? "font-semibold" : "text-muted-foreground"}`}>{s}</div>
            {i < STEPS.length - 1 && <div className="mx-2 h-px flex-1 bg-border" />}
          </div>
        ))}
      </div>

      <div className="mt-10 rounded-2xl border border-border bg-surface/60 p-8">
        {step === 0 && (
          <div>
            <div className="mb-4 flex items-center gap-2 text-brand"><User className="h-4 w-4" /><span className="text-sm font-semibold">Your profile</span></div>
            <div className="grid gap-4 md:grid-cols-2">
              <Field label="Full name" value={fullName} onChange={setFullName} />
              <Field label="Phone" value={phone} onChange={setPhone} placeholder="+15551234567" />
              <Field label="Company (optional)" value={company} onChange={setCompany} />
            </div>
          </div>
        )}
        {step === 1 && (
          <div>
            <div className="mb-4 flex items-center gap-2 text-brand"><Building2 className="h-4 w-4" /><span className="text-sm font-semibold">How do you plan to use American Shipping & Logistics?</span></div>
            <div className="grid gap-3 md:grid-cols-3">
              {(["individual", "business", "enterprise"] as const).map((t) => (
                <button
                  key={t}
                  onClick={() => setAccountType(t)}
                  className={`rounded-xl border p-4 text-left ${accountType === t ? "border-brand bg-brand/10" : "border-border bg-background/40"}`}
                >
                  <div className="font-semibold capitalize">{t}</div>
                  <div className="mt-1 text-xs text-muted-foreground">
                    {t === "individual" && "Occasional shipments and gifts."}
                    {t === "business" && "Regular parcels for a small business."}
                    {t === "enterprise" && "High-volume freight with SLAs."}
                  </div>
                </button>
              ))}
            </div>
          </div>
        )}
        {step === 2 && (
          <div>
            <div className="mb-4 flex items-center gap-2 text-brand"><Bell className="h-4 w-4" /><span className="text-sm font-semibold">Get notified</span></div>
            <div className="space-y-3">
              <Toggle label="Email me at every status change" checked={emailAlerts} onChange={setEmailAlerts} />
              <Toggle label="SMS to my phone" checked={smsAlerts} onChange={setSmsAlerts} />
              <Toggle label="Browser push notifications" checked={pushAlerts} onChange={setPushAlerts} />
            </div>
          </div>
        )}
        {step === 3 && (
          <div>
            <div className="mb-4 flex items-center gap-2 text-brand"><Package className="h-4 w-4" /><span className="text-sm font-semibold">Starter template</span></div>
            <div className="grid gap-3 md:grid-cols-3">
              {(["documents", "small_parcel", "pallet"] as const).map((t) => (
                <button
                  key={t}
                  onClick={() => setTemplate(t)}
                  className={`rounded-xl border p-4 text-left ${template === t ? "border-brand bg-brand/10" : "border-border bg-background/40"}`}
                >
                  <div className="font-semibold capitalize">{t.replace("_", " ")}</div>
                  <div className="mt-1 text-xs text-muted-foreground">
                    {t === "documents" && "Envelope up to 500g"}
                    {t === "small_parcel" && "Box up to 5kg"}
                    {t === "pallet" && "Freight pallet, 250kg"}
                  </div>
                </button>
              ))}
            </div>
          </div>
        )}

        <div className="mt-8 flex items-center justify-between">
          <button onClick={back} disabled={step === 0} className="text-sm text-muted-foreground disabled:opacity-40">Back</button>
          {step < STEPS.length - 1 ? (
            <button onClick={next} className="rounded-sm bg-accent px-6 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90">Continue</button>
          ) : (
            <button onClick={finish} disabled={busy} className="rounded-sm bg-accent px-6 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 disabled:opacity-60">
              {busy ? "Finishing…" : "Finish setup"}
            </button>
          )}
        </div>
      </div>
    </section>
  );
}

function Field({ label, value, onChange, placeholder }: { label: string; value: string; onChange: (v: string) => void; placeholder?: string }) {
  return (
    <label className="block">
      <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</span>
      <input
        value={value}
        placeholder={placeholder}
        onChange={(e) => onChange(e.target.value)}
        className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
      />
    </label>
  );
}

function Toggle({ label, checked, onChange }: { label: string; checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <label className="flex items-center justify-between rounded-lg border border-border bg-background/40 px-4 py-3 text-sm">
      <span>{label}</span>
      <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} className="h-4 w-4 accent-[var(--brand)]" />
    </label>
  );
}
