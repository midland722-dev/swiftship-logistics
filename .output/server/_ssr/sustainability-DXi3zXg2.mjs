import { n as require_jsx_runtime } from "../_libs/react+tanstack__react-query.mjs";
import { h as Link } from "../_libs/@tanstack/react-router+[...].mjs";
import { E as Leaf, i as Wind, m as Recycle, t as Zap } from "../_libs/lucide-react.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/sustainability-DXi3zXg2.js
var import_jsx_runtime = require_jsx_runtime();
var pillars = [
	{
		icon: Zap,
		title: "Electric fleet",
		desc: "60% of last-mile vehicles electric by 2030. Already 27,000 EVs on the road today."
	},
	{
		icon: Wind,
		title: "Sustainable aviation fuel",
		desc: "GoGreen Plus lets shippers cut air-freight emissions with certified SAF."
	},
	{
		icon: Recycle,
		title: "Circular packaging",
		desc: "Reusable totes and 100% recyclable poly-mailers across our eCommerce network."
	},
	{
		icon: Leaf,
		title: "Carbon insetting",
		desc: "Direct emission reductions in your own supply chain — not offsets on a spreadsheet."
	}
];
function SustainabilityPage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [
		/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
			className: "container-x pt-16 pb-14 md:pt-24",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "font-mono text-xs uppercase tracking-widest text-brand",
					children: "Sustainability"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("h1", {
					className: "mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl",
					children: ["Net-zero logistics by ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
						className: "text-brand",
						children: "2050."
					})]
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-5 max-w-2xl text-lg text-muted-foreground",
					children: "We're investing $7 billion by 2030 in clean fuels, electrified fleets, and climate-neutral buildings. Here's how we get there."
				})
			]
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
			className: "container-x grid gap-4 pb-16 md:grid-cols-2",
			children: pillars.map(({ icon: Icon, title, desc }) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "rounded-2xl border border-border bg-surface/60 p-6",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
						className: "grid h-11 w-11 place-items-center rounded-xl bg-background text-brand",
						children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, { className: "h-5 w-5" })
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "mt-5 text-lg font-semibold",
						children: title
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "mt-2 text-sm text-muted-foreground",
						children: desc
					})
				]
			}, title))
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
			className: "container-x pb-24",
			children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "rounded-3xl border border-brand/30 bg-gradient-to-b from-brand/10 to-surface p-10 md:p-16",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "font-display text-3xl font-bold md:text-4xl",
						children: "Ship greener today"
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "mt-3 max-w-2xl text-muted-foreground",
						children: "Add GoGreen Plus to any American Shipping & Logistics shipment and reduce your Scope 3 emissions with certified sustainable fuel — auditable, additional, and reported."
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
						to: "/quote",
						className: "mt-6 inline-block rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
						children: "Quote a green shipment"
					})
				]
			})
		})
	] });
}
//#endregion
export { SustainabilityPage as component };
