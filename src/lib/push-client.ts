import { supabase } from "@/integrations/supabase/client";

const VAPID_PUBLIC = import.meta.env.VITE_VAPID_PUBLIC_KEY as string | undefined;

function urlBase64ToUint8Array(base64String: string) {
  const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
  const raw = atob(base64);
  const arr = new Uint8Array(raw.length);
  for (let i = 0; i < raw.length; ++i) arr[i] = raw.charCodeAt(i);
  return arr;
}

function bufToB64Url(buf: ArrayBuffer | null) {
  if (!buf) return "";
  const bytes = new Uint8Array(buf);
  let s = "";
  for (let i = 0; i < bytes.byteLength; i++) s += String.fromCharCode(bytes[i]);
  return btoa(s).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/, "");
}

export function pushSupported() {
  return typeof window !== "undefined" && "serviceWorker" in navigator && "PushManager" in window && !!VAPID_PUBLIC;
}

export async function enablePushForCurrentUser(): Promise<{ ok: boolean; reason?: string }> {
  if (!pushSupported()) return { ok: false, reason: "Web push isn't supported in this browser." };
  const { data: userData } = await supabase.auth.getUser();
  if (!userData.user) return { ok: false, reason: "Sign in first." };

  const perm = await Notification.requestPermission();
  if (perm !== "granted") return { ok: false, reason: "Notification permission denied." };

  const reg = await navigator.serviceWorker.register("/sw.js");
  await navigator.serviceWorker.ready;
  let sub = await reg.pushManager.getSubscription();
  if (!sub) {
    sub = await reg.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC!),
    });
  }
  const json = sub.toJSON();
  const p256dh = json.keys?.p256dh ?? bufToB64Url(sub.getKey("p256dh"));
  const auth = json.keys?.auth ?? bufToB64Url(sub.getKey("auth"));
  const { error } = await supabase
    .from("push_subscriptions")
    .upsert(
      { user_id: userData.user.id, endpoint: sub.endpoint, p256dh, auth_key: auth },
      { onConflict: "endpoint" }
    );
  if (error) return { ok: false, reason: error.message };
  return { ok: true };
}

export async function disablePushForCurrentUser() {
  if (!("serviceWorker" in navigator)) return;
  const reg = await navigator.serviceWorker.getRegistration();
  const sub = await reg?.pushManager.getSubscription();
  if (sub) {
    await supabase.from("push_subscriptions").delete().eq("endpoint", sub.endpoint);
    await sub.unsubscribe();
  }
}
