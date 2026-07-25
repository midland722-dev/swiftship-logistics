export interface TrackingShipment {
  id: number;
  tracking_number: string;
  status: string;
  service_type: string;
  origin_city: string;
  origin_country: string;
  destination_city: string;
  destination_country: string;
  total_weight: string | null;
  total_amount: string | null;
  currency: string;
  estimated_delivery: string | null;
  actual_delivery: string | null;
  payment_status: string | null;
  created_at: string;
  customer_name: string | null;
  customer_email: string | null;
  is_legacy?: boolean;
}

export interface TrackingHistoryEvent {
  status: string;
  location: string | null;
  description: string | null;
  occurred_at: string;
  customs_procedure: string | null;
  transit_location: string | null;
}

export interface TrackingResponse {
  found: boolean;
  shipment: TrackingShipment | null;
  history: TrackingHistoryEvent[];
  message?: string;
}

const API_BASE = (import.meta.env?.VITE_API_BASE ?? "") || "http://localhost/ships";

export async function fetchTracking(id: string): Promise<TrackingResponse> {
  const url = API_BASE
    ? `${API_BASE}/php/process/track_ajax.php?id=${encodeURIComponent(id)}`
    : `/php/process/track_ajax.php?id=${encodeURIComponent(id)}`;

  const response = await fetch(url, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  });

  if (!response.ok) {
    const text = await response.text();
    let detail = text;
    try {
      const parsed = JSON.parse(text);
      detail = parsed.message || parsed.debug || text;
    } catch {
      // not JSON, use raw text
    }
    throw new Error(`Tracking request failed: ${response.status} — ${detail}`);
  }

  return response.json();
}
