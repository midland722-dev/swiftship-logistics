import { n as require_jsx_runtime } from "../_libs/react+tanstack__react-query.mjs";
import { C as Package, W as CreditCard, l as Truck, m as Shield } from "../_libs/lucide-react.mjs";
import { y as Link } from "../_libs/@tanstack/react-router+[...].mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/help-C-F98BAc.js
var import_jsx_runtime = require_jsx_runtime();
var topics = [
	{
		icon: Package,
		title: "Tracking",
		body: "Enter your tracking number on the Track page. Updates appear within minutes of each network scan."
	},
	{
		icon: Truck,
		title: "Shipping",
		body: "Book pickups online, drop off at 4,500+ locations, or schedule recurring collections for your business."
	},
	{
		icon: CreditCard,
		title: "Billing",
		body: "Invoices are issued weekly for business accounts. Log in to your account portal to download PDFs."
	},
	{
		icon: Shield,
		title: "Claims & insurance",
		body: "Report loss or damage within 30 days. Standard insurance covers $100; extended cover up to $2,000."
	}
];
var faqs = [
	{
		q: "How do I track a shipment?",
		a: "Use the tracking number from your booking confirmation on our Track page. Live status updates every few minutes."
	},
	{
		q: "What if my parcel is delayed?",
		a: "Delays over 24 hours past ETA are eligible for our on-time refund guarantee on Priority and Express services."
	},
	{
		q: "Can I change the delivery address?",
		a: "Yes — use the tracking page to request a redirect until the last-mile courier collects the parcel."
	},
	{
		q: "How is shipping cost calculated?",
		a: "The higher of actual weight or volumetric weight (L × W × H ÷ 5000), multiplied by service speed and zone."
	}
];
function HelpPage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [
		/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
			className: "container-x pt-16 pb-14 md:pt-24",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "font-mono text-xs uppercase tracking-widest text-brand",
					children: "Help center"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
					className: "mt-2 max-w-3xl font-display text-5xl font-bold md:text-6xl",
					children: "We're here to help."
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-5 max-w-2xl text-lg text-muted-foreground",
					children: "Find quick answers below, or reach our support team 24/7."
				})
			]
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
			className: "container-x grid gap-4 pb-14 sm:grid-cols-2 lg:grid-cols-4",
			children: topics.map(({ icon: Icon, title, body }) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "rounded-2xl border border-border bg-surface/60 p-6",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, { className: "h-6 w-6 text-brand" }),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "mt-4 font-semibold",
						children: title
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "mt-2 text-sm text-muted-foreground",
						children: body
					})
				]
			}, title))
		}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
			className: "container-x pb-24",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
					className: "font-display text-2xl font-bold",
					children: "Frequently asked questions"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
					className: "mt-6 divide-y divide-border rounded-2xl border border-border bg-surface/60",
					children: faqs.map((f) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("details", {
						className: "group p-5",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("summary", {
							className: "cursor-pointer list-none font-semibold marker:hidden",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
								className: "mr-2 text-brand",
								children: "+"
							}), f.q]
						}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "mt-3 pl-6 text-sm text-muted-foreground",
							children: f.a
						})]
					}, f.q))
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "mt-10 rounded-2xl border border-border bg-surface/60 p-6 text-center",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "text-sm text-muted-foreground",
						children: "Still stuck?"
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
						to: "/contact",
						className: "mt-3 inline-block rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
						children: "Contact support"
					})]
				})
			]
		})
	] });
}
//#endregion
export { HelpPage as component };
