export const VALID_STATUSES = [
  "booked",
  "picked_up",
  "in_transit",
  "out_for_delivery",
  "delivered",
  "exception",
  "cancelled",
] as const;

export type ShipmentStatus = (typeof VALID_STATUSES)[number];

export const STATUS_LABEL: Record<string, string> = {
  booked: "Booked",
  picked_up: "Picked up",
  in_transit: "In transit",
  out_for_delivery: "Out for delivery",
  delivered: "Delivered",
  exception: "Exception",
  cancelled: "Cancelled",
};

export interface AlertResult {
  ok: boolean;
  status: string;
  channels: { push: { sent: number; failed: number }; email: { sent: number; skipped: string | null } };
}
