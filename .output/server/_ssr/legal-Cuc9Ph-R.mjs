import { n as require_jsx_runtime } from "../_libs/react+tanstack__react-query.mjs";
import { h as Link } from "../_libs/@tanstack/react-router+[...].mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/legal-Cuc9Ph-R.js
var import_jsx_runtime = require_jsx_runtime();
function LegalPage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x py-16 md:py-20",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "font-mono text-xs uppercase tracking-widest text-brand",
				children: "Legal"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
				className: "mt-2 font-display text-4xl font-bold md:text-5xl",
				children: "Legal notice"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "prose prose-neutral mt-8 max-w-3xl text-muted-foreground",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "mt-8 font-display text-xl font-semibold text-foreground",
						children: "Company information"
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("p", { children: [
						"American Shipping & Logistics Inc.",
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("br", {}),
						"United States of America",
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("br", {}),
						"Phone: +1 (215) 815-9791",
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("br", {}),
						"Email: ",
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("a", {
							href: "mailto:info@ascl-logistics.com",
							className: "text-brand hover:underline",
							children: "info@ascl-logistics.com"
						})
					] }),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "mt-8 font-display text-xl font-semibold text-foreground",
						children: "Board & representation"
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", { children: "Represented by the Board of Directors." }),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "mt-8 font-display text-xl font-semibold text-foreground",
						children: "Contact"
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("p", { children: ["Email: ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)("a", {
						href: "mailto:info@ascl-logistics.com",
						className: "text-brand hover:underline",
						children: "info@ascl-logistics.com"
					})] }),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
						className: "mt-8 font-display text-xl font-semibold text-foreground",
						children: "Disclaimer"
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", { children: "Information on this website is provided for general purposes. While we take care to keep content accurate and current, American Shipping & Logistics makes no warranty as to completeness or fitness for a particular purpose." }),
					/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("p", {
						className: "mt-10 text-sm",
						children: [
							"See also our ",
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
								to: "/privacy",
								className: "text-brand hover:underline",
								children: "Privacy Notice"
							}),
							" and",
							" ",
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
								to: "/terms",
								className: "text-brand hover:underline",
								children: "Terms of Use"
							}),
							"."
						]
					})
				]
			})
		]
	});
}
//#endregion
export { LegalPage as component };
