import { createServerFn } from "@tanstack/react-start";
import { requireSupabaseAuth } from "@/integrations/supabase/auth-middleware";
import { z } from "zod";

const VALID_STATUSES = [
  "booked",
  "picked_up",
  "in_transit",
  "out_for_delivery",
  "delivered",
  "exception",
  "cancelled",
] as const;

const inputSchema = z.object({
  shipment_id: z.string().uuid(),
  status: z.enum(VALID_STATUSES),
});

const STATUS_LABEL: Record<string, string> = {
  booked: "Booked",
  picked_up: "Picked up",
  in_transit: "In transit",
  out_for_delivery: "Out for delivery",
  delivered: "Delivered",
  exception: "Exception",
  cancelled: "Cancelled",
};

interface AlertResult {
  ok: boolean;
  status: string;
  channels: { push: { sent: number; failed: number }; email: { sent: number; skipped: string | null } };
}

export const updateShipmentStatus = createServerFn({ method: "POST" })
  .middleware([requireSupabaseAuth])
  .inputValidator((data: unknown) => inputSchema.parse(data))
  .handler(async ({ data, context }): Promise<AlertResult> => {
    const { supabase, userId } = context;

    // Authorize: admin, staff, or the shipment owner may update
    const { data: shipment, error: fetchErr } = await supabase
      .from("shipments")
      .select("id, tracking_code, from_location, to_location, owner_id, status")
      .eq("id", data.shipment_id)
      .maybeSingle();
    if (fetchErr || !shipment) throw new Error("Shipment not found");

    const [{ data: isAdmin }, { data: isStaff }] = await Promise.all([
      supabase.rpc("has_role", { _user_id: userId, _role: "admin" }),
      supabase.rpc("has_role", { _user_id: userId, _role: "staff" }),
    ]);
    const isOwner = shipment.owner_id === userId;
    if (!isAdmin && !isStaff && !isOwner) throw new Error("Forbidden");

    // Update + record event
    const { error: updErr } = await supabase
      .from("shipments")
      .update({ status: data.status })
      .eq("id", data.shipment_id);
    if (updErr) throw new Error(updErr.message);

    await supabase.from("shipment_events").insert({
      shipment_id: data.shipment_id,
      label: STATUS_LABEL[data.status] ?? data.status,
      location: shipment.to_location,
    });

    const result: AlertResult = {
      ok: true,
      status: data.status,
      channels: { push: { sent: 0, failed: 0 }, email: { sent: 0, skipped: "no_owner" } },
    };

    if (!shipment.owner_id) return result;

    // Load owner prefs via admin client (RLS on prefs is per-user)
    const { supabaseAdmin } = await import("@/integrations/supabase/client.server");

    const [{ data: prefs }, { data: subs }] = await Promise.all([
      supabaseAdmin
        .from("shipment_alert_prefs")
        .select("email_enabled, push_enabled")
        .eq("user_id", shipment.owner_id)
        .maybeSingle(),
      supabaseAdmin
        .from("push_subscriptions")
        .select("id, endpoint, p256dh, auth_key")
        .eq("user_id", shipment.owner_id),
    ]);

    const title = `${shipment.tracking_code}: ${STATUS_LABEL[data.status] ?? data.status}`;
    const body = `${shipment.from_location} → ${shipment.to_location}`;
    const url = `/track?id=${encodeURIComponent(shipment.tracking_code)}`;

    // ---- Web push ----
    if ((prefs?.push_enabled ?? false) && subs && subs.length > 0) {
      const publicKey = process.env.VAPID_PUBLIC_KEY;
      const privateKey = process.env.VAPID_PRIVATE_KEY;
      const subject = process.env.VAPID_SUBJECT ?? "mailto:alerts@example.com";
      if (publicKey && privateKey) {
        try {
          const webpush = (await import("web-push")).default;
          webpush.setVapidDetails(subject, publicKey, privateKey);
          const payload = JSON.stringify({ title, body, url, tag: `ship-${shipment.tracking_code}` });
          for (const s of subs) {
            try {
              await webpush.sendNotification(
                { endpoint: s.endpoint, keys: { p256dh: s.p256dh, auth: s.auth_key } },
                payload
              );
              result.channels.push.sent++;
            } catch (err: unknown) {
              result.channels.push.failed++;
              const code = (err as { statusCode?: number })?.statusCode;
              if (code === 404 || code === 410) {
                await supabaseAdmin.from("push_subscriptions").delete().eq("id", s.id);
              } else {
                console.error("[push] send failed", err);
              }
            }
          }
        } catch (err) {
          console.error("[push] library init failed", err);
        }
      }
    }

    // ---- Email ----
    const sendEmailAlert = async (prefs: any, shipment: any) => {
      if (!prefs?.email_enabled) return;
      const domain = import.meta.env.VITE_EMAIL_DOMAIN;
      if (!domain) {
        console.warn("[email] domain not configured — skipping email notification");
        return;
      }
      try {
        await fetch('/api/notifications/email', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ shipmentId: shipment.id, recipient: shipment.recipient_email }),
        });
      } catch (err) {
        console.error("[email] send failed", err);
      }
    };
    await sendEmailAlert(prefs, shipment);

    return result;
  });
