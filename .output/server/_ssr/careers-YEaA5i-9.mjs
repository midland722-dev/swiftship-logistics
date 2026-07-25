import { n as require_jsx_runtime } from "../_libs/react+tanstack__react-query.mjs";
import { h as Link } from "../_libs/@tanstack/react-router+[...].mjs";
import { R as Briefcase, S as MapPin } from "../_libs/lucide-react.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/careers-YEaA5i-9.js
var import_jsx_runtime = require_jsx_runtime();
var roles = [
	{
		title: "Senior Backend Engineer, Tracking Platform",
		team: "Engineering",
		location: "United States"
	},
	{
		title: "Operations Manager, Air Freight",
		team: "Operations",
		location: "United States"
	},
	{
		title: "Product Designer, Shipper Experience",
		team: "Design",
		location: "Remote"
	},
	{
		title: "Data Scientist, Route Optimization",
		team: "Data",
		location: "United States"
	},
	{
		title: "Warehouse Team Lead",
		team: "Operations",
		location: "Dallas, TX"
	},
	{
		title: "Sustainability Program Manager",
		team: "Corporate",
		location: "Remote"
	}
];
function CareersPage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x pt-16 pb-14 md:pt-24",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "font-mono text-xs uppercase tracking-widest text-brand",
				children: "Careers"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
				className: "mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl",
				children: "Move the world with us."
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "mt-5 max-w-2xl text-lg text-muted-foreground",
				children: "From couriers to coders, 128,000 American Shipping & Logistics colleagues keep global trade moving. Find your role — and grow a career that spans continents."
			})
		]
	}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x pb-24",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
			className: "font-display text-2xl font-bold",
			children: "Open roles"
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
			className: "mt-6 divide-y divide-border rounded-2xl border border-border bg-surface/60",
			children: roles.map((r) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "flex flex-col gap-3 p-5 md:flex-row md:items-center md:justify-between",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", { children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
					className: "font-semibold",
					children: r.title
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "mt-1 flex flex-wrap items-center gap-4 text-xs text-muted-foreground",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("span", {
						className: "inline-flex items-center gap-1.5",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Briefcase, { className: "h-3.5 w-3.5" }), r.team]
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("span", {
						className: "inline-flex items-center gap-1.5",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(MapPin, { className: "h-3.5 w-3.5" }), r.location]
					})]
				})] }), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
					to: "/contact",
					className: "rounded-sm border border-border px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-background",
					children: "Apply"
				})]
			}, r.title))
		})]
	})] });
}
//#endregion
export { CareersPage as component };
