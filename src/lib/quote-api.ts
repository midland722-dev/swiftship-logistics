export interface QuoteBreakdown {
  from: string;
  to: string;
  weight_kg: string;
  volume_m3: string;
  billable_kg: string;
  speed: string;
  multiplier: number;
  base_cost: string;
  total: string;
}

export interface QuoteResponse {
  success: boolean;
  data?: {
    price: string;
    currency: string;
    breakdown: QuoteBreakdown;
  };
  message?: string;
  errors?: string[];
}

const API_BASE = (import.meta.env?.VITE_API_BASE ?? '') || 'http://localhost/ships';

export async function fetchQuote(params: {
  from: string;
  to: string;
  weight: number;
  length: number;
  width: number;
  height: number;
  speed: string;
}): Promise<QuoteResponse> {
  const qs = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    qs.set(key, String(value));
  });
  const url = API_BASE
    ? `${API_BASE}/php/process/quote_calc.php?${qs.toString()}`
    : `/php/process/quote_calc.php?${qs.toString()}`;

  const response = await fetch(url, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
    },
  });

  if (!response.ok) {
    throw new Error(`Quote request failed: ${response.status}`);
  }

  return response.json();
}
