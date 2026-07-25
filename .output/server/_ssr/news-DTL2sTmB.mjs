import { n as require_jsx_runtime } from "../_libs/react+tanstack__react-query.mjs";
import { h as Link } from "../_libs/@tanstack/react-router+[...].mjs";
import { I as Calendar } from "../_libs/lucide-react.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/news-DTL2sTmB.js
var import_jsx_runtime = require_jsx_runtime();
var posts = [
	{
		date: "Jan 12, 2026",
		title: "American Shipping & Logistics opens new automated hub in the USA",
		tag: "Network"
	},
	{
		date: "Dec 04, 2025",
		title: "1,200 additional electric vans deployed across US cities",
		tag: "Sustainability"
	},
	{
		date: "Nov 18, 2025",
		title: "American Shipping & Logistics Q3 results: 8.7% year-over-year revenue growth",
		tag: "Investors"
	},
	{
		date: "Oct 02, 2025",
		title: "New Trans-Pacific express route: LAX ↔ HKG in 18 hours",
		tag: "Service"
	},
	{
		date: "Sep 15, 2025",
		title: "GoGreen Plus expanded to all international parcels",
		tag: "Sustainability"
	}
];
function NewsPage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x pt-16 pb-14 md:pt-24",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
			className: "font-mono text-xs uppercase tracking-widest text-brand",
			children: "Newsroom"
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
			className: "mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl",
			children: "The latest from American Shipping & Logistics."
		})]
	}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
		className: "container-x pb-24",
		children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
			className: "divide-y divide-border rounded-2xl border border-border bg-surface/60",
			children: posts.map((p) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("article", {
				className: "flex flex-col gap-3 p-6 md:flex-row md:items-center md:justify-between",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", { children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "flex items-center gap-3 text-xs text-muted-foreground",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("span", {
						className: "inline-flex items-center gap-1.5",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Calendar, { className: "h-3.5 w-3.5" }), p.date]
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
						className: "rounded-full bg-brand/15 px-2 py-0.5 font-semibold text-brand",
						children: p.tag
					})]
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
					className: "mt-2 text-lg font-semibold",
					children: p.title
				})] }), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
					to: "/contact",
					className: "text-xs font-bold uppercase tracking-wider text-brand hover:underline",
					children: "Read more →"
				})]
			}, p.title))
		})
	})] });
}
//#endregion
export { NewsPage as component };
