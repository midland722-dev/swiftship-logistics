export interface PricingRules {
  base_fee: number;
  per_kg: number;
  volumetric_divisor: number;
  standard_multiplier: number;
  express_multiplier: number;
  priority_multiplier: number;
  insurance_fee: number;
  insurance_pct: number;
  fuel_surcharge_pct: number;
  currency: string;
}

export type ShipSpeed = "standard" | "express" | "priority";

export interface QuoteInput {
  weight_kg: number;
  length_cm: number;
  width_cm: number;
  height_cm: number;
  speed: ShipSpeed;
  insurance: boolean;
  declared_value?: number;
}

export interface QuoteBreakdown {
  billableWeight: number;
  volumetricWeight: number;
  base: number;
  weightCharge: number;
  speedLabel: string;
  speedSubtotal: number;
  fuel: number;
  insurance: number;
  total: number;
  currency: string;
}

export function calcQuote(rules: PricingRules, input: QuoteInput): QuoteBreakdown {
  const volumetric =
    (Math.max(0, input.length_cm) * Math.max(0, input.width_cm) * Math.max(0, input.height_cm)) /
    (rules.volumetric_divisor || 5000);
  const billable = Math.max(input.weight_kg || 0, volumetric);
  const base = rules.base_fee;
  const weightCharge = billable * rules.per_kg;
  const mult =
    input.speed === "priority"
      ? rules.priority_multiplier
      : input.speed === "express"
        ? rules.express_multiplier
        : rules.standard_multiplier;
  const speedSubtotal = (base + weightCharge) * mult;
  const fuel = speedSubtotal * rules.fuel_surcharge_pct;
  const insurance = input.insurance
    ? rules.insurance_fee + (input.declared_value ?? 0) * rules.insurance_pct
    : 0;
  const total = speedSubtotal + fuel + insurance;
  return {
    billableWeight: Number(billable.toFixed(2)),
    volumetricWeight: Number(volumetric.toFixed(2)),
    base,
    weightCharge: Number(weightCharge.toFixed(2)),
    speedLabel: input.speed,
    speedSubtotal: Number(speedSubtotal.toFixed(2)),
    fuel: Number(fuel.toFixed(2)),
    insurance: Number(insurance.toFixed(2)),
    total: Number(total.toFixed(2)),
    currency: rules.currency,
  };
}

export const DEFAULT_RULES: PricingRules = {
  base_fee: 8,
  per_kg: 4.2,
  volumetric_divisor: 5000,
  standard_multiplier: 1,
  express_multiplier: 1.9,
  priority_multiplier: 3.2,
  insurance_fee: 4.5,
  insurance_pct: 0.01,
  fuel_surcharge_pct: 0.06,
  currency: "USD",
};
