import { n as express_handoff_default, t as cargo_port_default } from "./cargo-port-BARjQwb4.mjs";
import { n as require_jsx_runtime } from "../_libs/react+tanstack__react-query.mjs";
import { h as Link } from "../_libs/@tanstack/react-router+[...].mjs";
import { B as Award, O as Globe, o as Users, p as Rocket } from "../_libs/lucide-react.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/about-lMYTGhbZ.js
var import_jsx_runtime = require_jsx_runtime();
var stats = [
	{
		icon: Globe,
		value: "220+",
		label: "Countries served"
	},
	{
		icon: Users,
		value: "128k",
		label: "Team members"
	},
	{
		icon: Award,
		value: "1969",
		label: "Founded"
	},
	{
		icon: Rocket,
		value: "1.9B",
		label: "Shipments per year"
	}
];
function AboutPage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [
		/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
			className: "container-x pt-16 pb-14 md:pt-24",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "font-mono text-xs uppercase tracking-widest text-brand",
					children: "About Us"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
					className: "mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl",
					children: "Excellence. Simply delivered."
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-5 max-w-2xl text-lg text-muted-foreground",
					children: "For over five decades American Shipping & Logistics has connected people, businesses and communities. From the first international courier flight to today's AI-optimized global routing, we keep supply chains moving — reliably, sustainably, everywhere."
				})
			]
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
			className: "container-x grid gap-4 pb-16 sm:grid-cols-2 lg:grid-cols-4",
			children: stats.map(({ icon: Icon, value, label }) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "rounded-2xl border border-border bg-surface/60 p-6",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, { className: "h-6 w-6 text-brand" }),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
						className: "mt-4 font-display text-3xl font-bold",
						children: value
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
						className: "text-sm text-muted-foreground",
						children: label
					})
				]
			}, label))
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
			className: "container-x grid gap-10 pb-24 md:grid-cols-2",
			children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", { children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
					className: "font-display text-3xl font-bold",
					children: "Our story"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-4 text-muted-foreground",
					children: "Founded in 1969 with a single cargo flight, American Shipping & Logistics has grown into one of the world's most trusted logistics networks. Today we operate across 220+ countries with 128,000 team members, 4,500+ service points, and 60+ major hubs."
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-4 text-muted-foreground",
					children: "Our heritage is built on reliability, innovation, and sustainability — the same values that guide every shipment we handle."
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-4 text-muted-foreground",
					children: "From the first international courier flight to today's AI-optimized global routing, we keep supply chains moving — reliably, sustainably, everywhere."
				})
			] }), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "grid grid-cols-2 gap-4",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
					src: express_handoff_default,
					alt: "Courier handoff",
					className: "h-full w-full rounded-2xl object-cover",
					loading: "lazy",
					decoding: "async"
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
					src: cargo_port_default,
					alt: "Ocean freight",
					className: "h-full w-full rounded-2xl object-cover",
					loading: "lazy",
					decoding: "async"
				})]
			})]
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
			className: "container-x pb-24",
			children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "rounded-3xl border border-border bg-surface/60 p-10 text-center md:p-16",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "font-display text-3xl font-bold md:text-4xl",
						children: "Work with our team"
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "mx-auto mt-3 max-w-xl text-muted-foreground",
						children: "From same-day couriers to multi-modal freight, our specialists design the right solution for your business."
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
						className: "mt-6 flex flex-wrap justify-center gap-3",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
							to: "/contact",
							className: "rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-white hover:opacity-90",
							children: "Contact sales"
						}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
							to: "/careers",
							className: "rounded-sm border border-border px-5 py-3 text-sm font-semibold hover:bg-surface",
							children: "See open roles"
						})]
					})
				]
			})
		})
	] });
}
//#endregion
export { AboutPage as component };
