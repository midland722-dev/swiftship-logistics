import { n as __toESM } from "../_runtime.mjs";
import { n as require_jsx_runtime, r as require_react } from "../_libs/react+tanstack__react-query.mjs";
import { P as CircleCheck, c as Truck, d as Shield, t as Zap, u as Ship } from "../_libs/lucide-react.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/quote-1tOQJ0_Y.js
var import_react = /* @__PURE__ */ __toESM(require_react());
var import_jsx_runtime = require_jsx_runtime();
var API_BASE = "http://localhost/ships";
async function fetchQuote(params) {
	const qs = new URLSearchParams();
	Object.entries(params).forEach(([key, value]) => {
		qs.set(key, String(value));
	});
	const url = `${API_BASE}/php/process/quote_calc.php?${qs.toString()}`;
	const response = await fetch(url, {
		method: "GET",
		headers: { Accept: "application/json" }
	});
	if (!response.ok) throw new Error(`Quote request failed: ${response.status}`);
	return response.json();
}
var speedConfig = {
	standard: {
		icon: Ship,
		label: "Standard",
		days: "5–8 business days"
	},
	express: {
		icon: Truck,
		label: "Express",
		days: "2–3 business days"
	},
	priority: {
		icon: Zap,
		label: "Priority",
		days: "Next business day"
	}
};
function QuotePage() {
	const [from, setFrom] = (0, import_react.useState)("Berlin, DE");
	const [to, setTo] = (0, import_react.useState)("Tokyo, JP");
	const [weight, setWeight] = (0, import_react.useState)(2.4);
	const [length, setLength] = (0, import_react.useState)(30);
	const [width, setWidth] = (0, import_react.useState)(20);
	const [height, setHeight] = (0, import_react.useState)(15);
	const [speed, setSpeed] = (0, import_react.useState)("express");
	const [insurance, setInsurance] = (0, import_react.useState)(true);
	const [quote, setQuote] = (0, import_react.useState)(null);
	const [loading, setLoading] = (0, import_react.useState)(false);
	const [error, setError] = (0, import_react.useState)(null);
	(0, import_react.useEffect)(() => {
		let cancelled = false;
		setLoading(true);
		setError(null);
		fetchQuote({
			from,
			to,
			weight,
			length,
			width,
			height,
			speed
		}).then((res) => {
			if (!cancelled) setQuote(res);
		}).catch((err) => {
			if (!cancelled) setError(err instanceof Error ? err.message : "Something went wrong.");
		}).finally(() => {
			if (!cancelled) setLoading(false);
		});
		return () => {
			cancelled = true;
		};
	}, [
		from,
		to,
		weight,
		length,
		width,
		height,
		speed
	]);
	const price = quote?.data?.price ?? "—";
	const breakdown = quote?.data?.breakdown;
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x py-16 md:py-20",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "font-mono text-xs uppercase tracking-widest text-brand",
				children: "Shipping quote"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("h1", {
				className: "mt-2 font-display text-4xl font-bold md:text-5xl",
				children: ["Instant quotes. ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
					className: "text-brand",
					children: "No account needed."
				})]
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "mt-4 max-w-xl text-muted-foreground",
				children: "Enter your parcel details and we'll price every service tier in seconds."
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "rounded-2xl border border-border bg-surface/60 p-6 md:p-8",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "grid gap-4 md:grid-cols-2",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Field, {
								label: "From",
								value: from,
								onChange: setFrom,
								placeholder: "City, Country"
							}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Field, {
								label: "To",
								value: to,
								onChange: setTo,
								placeholder: "City, Country"
							})]
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h3", {
							className: "mt-8 text-sm font-semibold uppercase tracking-wider text-muted-foreground",
							children: "Parcel"
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "mt-3 grid gap-4 md:grid-cols-4",
							children: [
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(NumField, {
									label: "Weight (kg)",
									value: weight,
									onChange: setWeight,
									step: .1
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(NumField, {
									label: "Length (cm)",
									value: length,
									onChange: setLength
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(NumField, {
									label: "Width (cm)",
									value: width,
									onChange: setWidth
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(NumField, {
									label: "Height (cm)",
									value: height,
									onChange: setHeight
								})
							]
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h3", {
							className: "mt-8 text-sm font-semibold uppercase tracking-wider text-muted-foreground",
							children: "Delivery speed"
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
							className: "mt-3 grid gap-3 md:grid-cols-3",
							children: Object.keys(speedConfig).map((s) => {
								const cfg = speedConfig[s];
								const Icon = cfg.icon;
								const selected = speed === s;
								return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("button", {
									onClick: () => setSpeed(s),
									className: `flex flex-col items-start rounded-xl border p-4 text-left transition ${selected ? "border-brand bg-brand/10" : "border-border bg-background/40 hover:bg-surface"}`,
									children: [
										/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, { className: `h-5 w-5 ${selected ? "text-brand" : "text-muted-foreground"}` }),
										/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
											className: "mt-3 font-semibold",
											children: cfg.label
										}),
										/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
											className: "text-xs text-muted-foreground",
											children: cfg.days
										})
									]
								}, s);
							})
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("label", {
							className: "mt-6 flex items-center gap-3 rounded-xl border border-border bg-background/40 p-4",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("input", {
								type: "checkbox",
								checked: insurance,
								onChange: (e) => setInsurance(e.target.checked),
								className: "h-4 w-4 accent-[var(--brand)]"
							}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
								className: "flex-1",
								children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
									className: "flex items-center gap-2 text-sm font-medium",
									children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Shield, { className: "h-4 w-4 text-brand" }), "Add insurance ($4.50)"]
								}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
									className: "text-xs text-muted-foreground",
									children: "Cover contents up to $2,000 in case of loss or damage."
								})]
							})]
						})
					]
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("aside", {
					className: "lg:sticky lg:top-24 lg:self-start",
					children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
						className: "rounded-2xl border border-brand/40 bg-gradient-to-b from-brand/15 to-surface p-6 md:p-8",
						children: [
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
								className: "text-xs font-mono uppercase tracking-widest text-brand",
								children: "Estimated price"
							}),
							loading && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
								className: "mt-2 text-sm text-muted-foreground",
								children: "Calculating…"
							}),
							error && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
								className: "mt-2 text-sm text-red-700",
								children: error
							}),
							!loading && !error && /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
								className: "mt-2 font-display text-5xl font-bold",
								children: ["$", price]
							}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
								className: "mt-1 text-sm text-muted-foreground",
								children: speedConfig[speed].days
							})] }),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("ul", {
								className: "mt-6 space-y-2 text-sm",
								children: [
									`${from} → ${to}`,
									`${weight} kg parcel`,
									`${length}×${width}×${height} cm`,
									`${speedConfig[speed].label} shipping`,
									insurance ? "Insurance included" : "No insurance",
									breakdown ? `Billable: ${breakdown.billable_kg} kg` : null
								].filter(Boolean).map((line) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("li", {
									className: "flex items-start gap-2 text-muted-foreground",
									children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(CircleCheck, { className: "mt-0.5 h-4 w-4 shrink-0 text-brand" }), String(line)]
								}, String(line)))
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
								className: "mt-6 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
								children: "Book this shipment"
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
								className: "mt-2 w-full rounded-sm border border-border py-3 text-sm font-semibold hover:bg-surface",
								children: "Save quote"
							})
						]
					})
				})]
			})
		]
	});
}
function Field({ label, value, onChange, placeholder }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("label", {
		className: "block",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
			className: "text-xs font-medium uppercase tracking-wider text-muted-foreground",
			children: label
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("input", {
			value,
			onChange: (e) => onChange(e.target.value),
			placeholder,
			className: "mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
		})]
	});
}
function NumField({ label, value, onChange, step = 1 }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("label", {
		className: "block",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
			className: "text-xs font-medium uppercase tracking-wider text-muted-foreground",
			children: label
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("input", {
			type: "number",
			value,
			step,
			min: 0,
			onChange: (e) => onChange(Number(e.target.value)),
			className: "mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
		})]
	});
}
//#endregion
export { QuotePage as component };
