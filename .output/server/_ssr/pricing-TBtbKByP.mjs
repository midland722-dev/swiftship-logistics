import { n as require_jsx_runtime } from "../_libs/react+tanstack__react-query.mjs";
import { Z as Check } from "../_libs/lucide-react.mjs";
import { p as Link } from "../_libs/@tanstack/react-router+[...].mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/pricing-TBtbKByP.js
var import_jsx_runtime = require_jsx_runtime();
var tiers = [
	{
		name: "Send",
		price: "Pay per ship",
		tagline: "For occasional shipments and one-off parcels.",
		features: [
			"Instant quotes",
			"Global tracking",
			"Drop-off at 4,500+ points",
			"Basic insurance up to $100"
		],
		cta: "Get a quote",
		to: "/quote",
		highlight: false
	},
	{
		name: "Business",
		price: "$49/mo",
		tagline: "For growing shops shipping 50+ parcels a month.",
		features: [
			"15% off standard rates",
			"Bulk shipment upload",
			"Branded tracking pages",
			"Priority support",
			"API access"
		],
		cta: "Start business plan",
		to: "/contact",
		highlight: true
	},
	{
		name: "Enterprise",
		price: "Custom",
		tagline: "For global operations and dedicated logistics teams.",
		features: [
			"Custom pricing tiers",
			"Dedicated account manager",
			"SLA guarantees",
			"SSO & audit logs",
			"Full API + webhooks"
		],
		cta: "Talk to sales",
		to: "/contact",
		highlight: false
	}
];
function PricingPage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [
		/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
			className: "container-x pt-16 text-center md:pt-24",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "font-mono text-xs uppercase tracking-widest text-brand",
					children: "Pricing"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("h1", {
					className: "mt-2 font-display text-5xl font-bold md:text-6xl",
					children: ["Priced by the parcel. ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
						className: "text-brand",
						children: "Never by surprise."
					})]
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mx-auto mt-5 max-w-xl text-lg text-muted-foreground",
					children: "Start with pay-as-you-ship, then scale into volume discounts when you're ready."
				})
			]
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
			className: "container-x grid gap-6 py-16 md:grid-cols-3",
			children: tiers.map((t) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: `relative flex flex-col rounded-2xl border p-8 ${t.highlight ? "border-brand bg-gradient-to-b from-brand/10 to-surface" : "border-border bg-surface/60"}`,
				children: [
					t.highlight && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
						className: "absolute right-6 top-6 rounded-full bg-brand px-2.5 py-1 text-xs font-semibold text-brand-foreground",
						children: "Most popular"
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "font-display text-2xl font-bold",
						children: t.name
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
						className: "mt-3 font-display text-4xl font-bold",
						children: t.price
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "mt-2 text-sm text-muted-foreground",
						children: t.tagline
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("ul", {
						className: "mt-6 space-y-3 text-sm",
						children: t.features.map((f) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("li", {
							className: "flex items-start gap-2",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Check, { className: "mt-0.5 h-4 w-4 shrink-0 text-brand" }), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", { children: f })]
						}, f))
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
						to: t.to,
						className: `mt-8 rounded-sm py-3 text-center text-sm font-bold uppercase tracking-wider ${t.highlight ? "bg-accent text-accent-foreground hover:opacity-90" : "border border-border hover:bg-surface"}`,
						children: t.cta
					})
				]
			}, t.name))
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
			className: "container-x pb-24",
			children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "rounded-2xl border border-border bg-surface/60 p-8",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
					className: "font-display text-xl font-semibold",
					children: "Rate examples (0.5 kg parcel)"
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
					className: "mt-6 overflow-x-auto",
					children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("table", {
						className: "w-full text-sm",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("thead", {
							className: "text-left text-xs uppercase tracking-wider text-muted-foreground",
							children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("tr", { children: [
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("th", {
									className: "pb-3 pr-4",
									children: "Route"
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("th", {
									className: "pb-3 pr-4",
									children: "Standard"
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("th", {
									className: "pb-3 pr-4",
									children: "Express"
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("th", {
									className: "pb-3",
									children: "Same-day"
								})
							] })
						}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("tbody", {
							className: "divide-y divide-border",
							children: [
								[
									"Berlin → Paris",
									"$12.40",
									"$24.90",
									"—"
								],
								[
									"London → New York",
									"$28.10",
									"$59.90",
									"—"
								],
								[
									"Tokyo → Singapore",
									"$18.50",
									"$42.00",
									"—"
								],
								[
									"Metro same-city",
									"$6.00",
									"—",
									"$14.90"
								]
							].map((row) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)("tr", { children: row.map((cell, i) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)("td", {
								className: `py-3 pr-4 ${i === 0 ? "font-medium" : "text-muted-foreground"}`,
								children: cell
							}, i)) }, row[0]))
						})]
					})
				})]
			})
		})
	] });
}
//#endregion
export { PricingPage as component };
