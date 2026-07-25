import { n as __toESM } from "../_runtime.mjs";
import { n as require_jsx_runtime, r as require_react, t as QueryClientProvider } from "../_libs/react+tanstack__react-query.mjs";
import { _ as useRouter, c as HeadContent, d as Outlet, f as lazyRouteComponent, h as Link, m as createRootRouteWithContext, p as createFileRoute, s as Scripts, u as createRouter } from "../_libs/@tanstack/react-router+[...].mjs";
import { D as Instagram, O as Globe, _ as Package, b as MessageCircle, j as Facebook, n as Youtube, r as X, s as Twitter, w as Linkedin, x as Menu } from "../_libs/lucide-react.mjs";
import { t as Route$15 } from "./track-DxdnxYc9.mjs";
import { t as QueryClient } from "../_libs/tanstack__query-core.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/router-xuGNiGBP.js
var import_react = /* @__PURE__ */ __toESM(require_react());
var import_jsx_runtime = require_jsx_runtime();
var styles_default = "/assets/styles-TbfLtQq3.css";
function reportLovableError(error, context = {}) {
	if (typeof window === "undefined") return;
	window.__lovableEvents?.captureException?.(error, {
		source: "react_error_boundary",
		route: window.location.pathname,
		...context
	}, {
		mechanism: "react_error_boundary",
		handled: false,
		severity: "error"
	});
}
var nav = [
	{
		to: "/services",
		label: "Services"
	},
	{
		to: "/track",
		label: "Track"
	},
	{
		to: "/quote",
		label: "Get a quote"
	},
	{
		to: "/pricing",
		label: "Pricing"
	},
	{
		to: "/about",
		label: "About"
	},
	{
		to: "/help",
		label: "Help"
	},
	{
		to: "/contact",
		label: "Contact"
	}
];
function SiteHeader() {
	const [open, setOpen] = (0, import_react.useState)(false);
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("header", {
		className: "sticky top-0 z-40 border-b border-border/60 bg-background/70 backdrop-blur-xl",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
			className: "container-x flex h-16 items-center justify-between gap-6",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsxs)(Link, {
					to: "/",
					className: "flex items-center gap-2 font-display text-lg font-bold tracking-tight",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
						className: "grid h-8 w-8 place-items-center rounded-sm bg-brand text-accent",
						children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Package, {
							className: "h-4 w-4",
							strokeWidth: 2.75
						})
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", { children: "American Shipping & Logistics" })]
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("nav", {
					className: "hidden items-center gap-1 md:flex",
					children: nav.map((n) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
						to: n.to,
						className: "rounded-md px-3 py-2 text-sm text-muted-foreground transition hover:bg-surface hover:text-foreground",
						activeProps: { className: "text-foreground bg-surface" },
						children: n.label
					}, n.to))
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
					className: "hidden items-center gap-2 md:flex",
					children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
						to: "/track",
						className: "rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground transition hover:opacity-90",
						children: "Track"
					})
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
					onClick: () => setOpen((v) => !v),
					className: "grid h-10 w-10 place-items-center rounded-md md:hidden",
					"aria-label": open ? "Close menu" : "Open menu",
					children: open ? /* @__PURE__ */ (0, import_jsx_runtime.jsx)(X, { className: "h-5 w-5" }) : /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Menu, { className: "h-5 w-5" })
				})
			]
		}), open && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
			className: "border-t border-border/60 bg-background md:hidden",
			children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("nav", {
				className: "container-x flex flex-col gap-1 py-3",
				children: nav.map((n) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
					to: n.to,
					onClick: () => setOpen(false),
					className: "rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-surface hover:text-foreground",
					activeProps: { className: "text-foreground bg-surface" },
					children: n.label
				}, n.to))
			})
		})]
	});
}
function SiteFooter() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("footer", {
		className: "mt-20",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
			className: "bg-brand text-brand-foreground",
			children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "container-x flex flex-col gap-3 py-4 text-sm sm:flex-row sm:items-center sm:justify-between",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "flex items-center gap-2 font-semibold",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Globe, { className: "h-4 w-4" }),
						"You are in ",
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
							className: "font-bold",
							children: "United States of America"
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
							className: "mx-2 opacity-40",
							children: "·"
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
							to: "/contact",
							className: "underline underline-offset-4 hover:text-accent",
							children: "Select a different country"
						})
					]
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "flex items-center gap-4 text-brand-foreground/80",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
						className: "text-xs font-semibold uppercase tracking-wider",
						children: "Follow us"
					}), [
						Facebook,
						Twitter,
						Linkedin,
						Youtube,
						Instagram
					].map((I, i) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)("a", {
						href: "#",
						"aria-label": "social",
						className: "hover:text-accent",
						children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(I, { className: "h-4 w-4" })
					}, i))]
				})]
			})
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
			className: "bg-[oklch(0.2_0.02_250)] text-white",
			children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "container-x grid gap-10 py-14 md:grid-cols-5",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
						className: "md:col-span-1",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "flex items-center gap-2 font-display text-lg font-bold",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
								className: "grid h-8 w-8 place-items-center rounded-sm bg-brand text-accent",
								children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Package, {
									className: "h-4 w-4",
									strokeWidth: 2.75
								})
							}), "American Shipping & Logistics"]
						}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "mt-4 text-sm text-white/60",
							children: "Excellence. Simply delivered. Global logistics and courier services in 220+ countries."
						})]
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(FooterCol, {
						title: "About Us",
						links: [
							["Company", "/about"],
							["Newsroom", "/news"],
							["Careers", "/careers"],
							["Sustainability", "/sustainability"],
							["Investors", "/about"]
						]
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(FooterCol, {
						title: "Business Divisions",
						links: [
							["American Shipping Express", "/services"],
							["American Shipping eCommerce", "/services"],
							["Global Forwarding", "/services"],
							["Supply Chain", "/services"],
							["Parcel & Same-day", "/services"]
						]
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(FooterCol, {
						title: "Customer Service",
						links: [
							["Track a shipment", "/track"],
							["Get a quote", "/quote"],
							["Pricing", "/pricing"],
							["Help center", "/help"],
							["Contact us", "/contact"]
						]
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)(FooterCol, {
						title: "Careers & More",
						links: [
							["Careers", "/careers"],
							["Newsroom", "/news"],
							["Sustainability", "/sustainability"],
							["Help center", "/help"],
							["Contact & locations", "/contact"]
						]
					})
				]
			}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
				className: "border-t border-white/10",
				children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "container-x flex flex-col gap-3 py-5 text-xs text-white/60 md:flex-row md:items-center md:justify-between",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("p", { children: [
						"© ",
						(/* @__PURE__ */ new Date()).getFullYear(),
						" American Shipping & Logistics. All rights reserved."
					] }), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("ul", {
						className: "flex flex-wrap gap-x-5 gap-y-2",
						children: [
							["Legal Notice", "/legal"],
							["Terms of Use", "/terms"],
							["Privacy Notice", "/privacy"],
							["Cookie Settings", "/privacy"],
							["Sustainability", "/sustainability"]
						].map(([label, href]) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)("li", { children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
							to: href,
							className: "hover:text-brand",
							children: label
						}) }, label))
					})]
				})
			})]
		})]
	});
}
function FooterCol({ title, links }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", { children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h4", {
		className: "mb-4 text-sm font-bold uppercase tracking-wider text-white",
		children: title
	}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("ul", {
		className: "space-y-2.5 text-sm text-white/70",
		children: links.map(([label, href]) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)("li", { children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
			to: href,
			className: "hover:text-brand",
			children: label
		}) }, label))
	})] });
}
var WHATSAPP_NUMBER = "12158159791";
var WHATSAPP_MESSAGE = "Hello! I'd like to inquire about your shipping services.";
var WHATSAPP_LABEL = "Chat with us on WhatsApp";
function WhatsAppButton() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("a", {
		href: `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(WHATSAPP_MESSAGE)}`,
		target: "_blank",
		rel: "noopener noreferrer",
		"aria-label": WHATSAPP_LABEL,
		className: "fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#25D366] focus:ring-offset-2",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(MessageCircle, {
			className: "h-7 w-7",
			strokeWidth: 2
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
			className: "sr-only",
			children: WHATSAPP_LABEL
		})]
	});
}
function NotFoundComponent() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
		className: "flex min-h-screen flex-col",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)(SiteHeader, {}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("main", {
				className: "flex flex-1 items-center justify-center px-4",
				children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "max-w-md text-center",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "font-mono text-xs uppercase tracking-widest text-brand",
							children: "Error 404"
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
							className: "mt-2 font-display text-6xl font-bold",
							children: "Off route"
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "mt-3 text-sm text-muted-foreground",
							children: "That page isn't on our network. Let's get you back to somewhere useful."
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "mt-6 flex justify-center gap-2",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
								to: "/",
								className: "rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
								children: "Go home"
							}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Link, {
								to: "/track",
								className: "rounded-sm border border-border px-4 py-2 text-sm font-semibold hover:bg-surface",
								children: "Track a shipment"
							})]
						})
					]
				})
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)(SiteFooter, {}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)(WhatsAppButton, {})
		]
	});
}
function ErrorComponent({ error, reset }) {
	console.error(error);
	const router = useRouter();
	(0, import_react.useEffect)(() => {
		reportLovableError(error, { boundary: "tanstack_root_error_component" });
	}, [error]);
	return /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
		className: "flex min-h-screen items-center justify-center px-4",
		children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
			className: "max-w-md text-center",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
					className: "font-display text-2xl font-semibold",
					children: "This page didn't load"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-2 text-sm text-muted-foreground",
					children: "Something went wrong on our end. Try refreshing or head back home."
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "mt-6 flex flex-wrap justify-center gap-2",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
						onClick: () => {
							router.invalidate();
							reset();
						},
						className: "rounded-sm bg-accent px-4 py-2 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
						children: "Try again"
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("a", {
						href: "/",
						className: "rounded-sm border border-border px-4 py-2 text-sm font-semibold hover:bg-surface",
						children: "Go home"
					})]
				})
			]
		})
	});
}
var Route$14 = createRootRouteWithContext()({
	head: () => ({
		meta: [
			{ charSet: "utf-8" },
			{
				name: "viewport",
				content: "width=device-width, initial-scale=1"
			},
			{ title: "American Shipping & Logistics — Global Courier & Freight Services" },
			{
				name: "description",
				content: "American Shipping & Logistics moves parcels and freight to 220+ countries. Track shipments, get instant quotes, and book pickups in seconds."
			},
			{
				property: "og:title",
				content: "American Shipping & Logistics — Global Courier & Freight Services"
			},
			{
				property: "og:description",
				content: "Track, quote, and ship worldwide with American Shipping & Logistics modern logistics network."
			},
			{
				property: "og:type",
				content: "website"
			},
			{
				property: "og:site_name",
				content: "American Shipping & Logistics"
			},
			{
				name: "twitter:card",
				content: "summary_large_image"
			}
		],
		links: [
			{
				rel: "stylesheet",
				href: styles_default
			},
			{
				rel: "icon",
				href: "/favicon.ico",
				type: "image/x-icon"
			},
			{
				rel: "preconnect",
				href: "https://fonts.googleapis.com"
			},
			{
				rel: "preconnect",
				href: "https://fonts.gstatic.com",
				crossOrigin: "anonymous"
			},
			{
				rel: "stylesheet",
				href: "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500&display=swap"
			}
		],
		scripts: [{
			type: "application/ld+json",
			children: JSON.stringify({
				"@context": "https://schema.org",
				"@type": "Organization",
				name: "American Shipping & Logistics",
				url: "/",
				logo: "/favicon.ico",
				sameAs: [],
				contactPoint: [{
					"@type": "ContactPoint",
					telephone: "+1-415-555-0198",
					contactType: "customer service",
					areaServed: "Worldwide",
					availableLanguage: ["English"]
				}]
			})
		}]
	}),
	shellComponent: RootShell,
	component: RootComponent,
	notFoundComponent: NotFoundComponent,
	errorComponent: ErrorComponent
});
function RootShell({ children }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("html", {
		lang: "en",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("head", { children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(HeadContent, {}) }), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("body", { children: [children, /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Scripts, {})] })]
	});
}
function RootComponent() {
	const { queryClient } = Route$14.useRouteContext();
	return /* @__PURE__ */ (0, import_jsx_runtime.jsx)(QueryClientProvider, {
		client: queryClient,
		children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
			className: "flex min-h-screen flex-col",
			children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)(SiteHeader, {}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("main", {
					className: "flex-1",
					children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Outlet, {})
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)(SiteFooter, {}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)(WhatsAppButton, {})
			]
		})
	});
}
var $$splitComponentImporter$12 = () => import("./routes-BOZNeCWb.mjs");
var Route$13 = createFileRoute("/")({
	head: () => ({
		meta: [
			{ title: "American Shipping & Logistics — Global Courier & Freight Services" },
			{
				name: "description",
				content: "Ship, track, and quote parcels and freight to 220+ countries with American Shipping & Logistics. Trusted global logistics, simply delivered."
			},
			{
				property: "og:title",
				content: "American Shipping & Logistics — Excellence, Simply Delivered"
			},
			{
				property: "og:description",
				content: "Global logistics and courier services to 220+ countries. Track, quote, and ship with American Shipping & Logistics."
			}
		],
		links: [{
			rel: "canonical",
			href: "/"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$12, "component")
});
var $$splitComponentImporter$11 = () => import("./about-lMYTGhbZ.mjs");
var Route$12 = createFileRoute("/about")({
	head: () => ({
		meta: [
			{ title: "About — American Shipping & Logistics" },
			{
				name: "description",
				content: "American Shipping & Logistics connects people and businesses across 220+ countries with reliable logistics, courier, and freight services."
			},
			{
				property: "og:title",
				content: "About American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Our story, our network, and the people moving your world."
			},
			{
				property: "og:url",
				content: "/about"
			}
		],
		links: [{
			rel: "canonical",
			href: "/about"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$11, "component")
});
var $$splitComponentImporter$10 = () => import("./careers-YEaA5i-9.mjs");
var Route$11 = createFileRoute("/careers")({
	head: () => ({
		meta: [
			{ title: "Careers — American Shipping & Logistics" },
			{
				name: "description",
				content: "Join 128,000 American Shipping & Logistics colleagues in 220+ countries. Explore open roles in operations, engineering, and corporate teams."
			},
			{
				property: "og:title",
				content: "Careers at American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Open roles across engineering, operations, and corporate teams worldwide."
			},
			{
				property: "og:url",
				content: "/careers"
			}
		],
		links: [{
			rel: "canonical",
			href: "/careers"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$10, "component")
});
var $$splitComponentImporter$9 = () => import("./contact-B2kpNjy1.mjs");
var Route$10 = createFileRoute("/contact")({
	head: () => ({
		meta: [
			{ title: "Contact — American Shipping & Logistics" },
			{
				name: "description",
				content: "Get in touch with American Shipping & Logistics sales, support, or press. We reply within one business hour."
			},
			{
				property: "og:title",
				content: "Contact — American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Talk to American Shipping & Logistics team."
			}
		],
		links: [{
			rel: "canonical",
			href: "/contact"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$9, "component")
});
var $$splitComponentImporter$8 = () => import("./help-C-F98BAc.mjs");
var Route$9 = createFileRoute("/help")({
	head: () => ({
		meta: [
			{ title: "Help center — American Shipping & Logistics" },
			{
				name: "description",
				content: "Answers to common questions about tracking, shipping, billing, and claims."
			},
			{
				property: "og:title",
				content: "American Shipping & Logistics Help Center"
			},
			{
				property: "og:description",
				content: "Support for tracking, shipping, billing, and claims."
			},
			{
				property: "og:url",
				content: "/help"
			}
		],
		links: [{
			rel: "canonical",
			href: "/help"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$8, "component")
});
var $$splitComponentImporter$7 = () => import("./legal-Cuc9Ph-R.mjs");
var Route$8 = createFileRoute("/legal")({
	head: () => ({
		meta: [
			{ title: "Legal notice — American Shipping & Logistics" },
			{
				name: "description",
				content: "Corporate information, registration details, and legal notices for American Shipping & Logistics."
			},
			{
				property: "og:title",
				content: "Legal notice — American Shipping & Logistics"
			},
			{
				property: "og:url",
				content: "/legal"
			}
		],
		links: [{
			rel: "canonical",
			href: "/legal"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$7, "component")
});
var $$splitComponentImporter$6 = () => import("./news-DTL2sTmB.mjs");
var Route$7 = createFileRoute("/news")({
	head: () => ({
		meta: [
			{ title: "Newsroom — American Shipping & Logistics" },
			{
				name: "description",
				content: "Latest press releases, network updates, and service bulletins from American Shipping & Logistics."
			},
			{
				property: "og:title",
				content: "Newsroom — American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Press releases and service bulletins."
			},
			{
				property: "og:url",
				content: "/news"
			}
		],
		links: [{
			rel: "canonical",
			href: "/news"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$6, "component")
});
var $$splitComponentImporter$5 = () => import("./pricing-TBtbKByP.mjs");
var Route$6 = createFileRoute("/pricing")({
	head: () => ({
		meta: [
			{ title: "Pricing — American Shipping & Logistics" },
			{
				name: "description",
				content: "Transparent shipping plans for individuals, small businesses, and global enterprises."
			},
			{
				property: "og:title",
				content: "Pricing — American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Simple, transparent logistics pricing."
			}
		],
		links: [{
			rel: "canonical",
			href: "/pricing"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$5, "component")
});
var $$splitComponentImporter$4 = () => import("./privacy-ClKfGA6I.mjs");
var Route$5 = createFileRoute("/privacy")({
	head: () => ({
		meta: [
			{ title: "Privacy notice — American Shipping & Logistics" },
			{
				name: "description",
				content: "How American Shipping & Logistics collects, uses, and protects your personal data across our logistics services."
			},
			{
				property: "og:title",
				content: "Privacy notice — American Shipping & Logistics"
			},
			{
				property: "og:url",
				content: "/privacy"
			}
		],
		links: [{
			rel: "canonical",
			href: "/privacy"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$4, "component")
});
var $$splitComponentImporter$3 = () => import("./quote-1tOQJ0_Y.mjs");
var Route$4 = createFileRoute("/quote")({
	head: () => ({
		meta: [
			{ title: "Get a shipping quote — American Shipping & Logistics" },
			{
				name: "description",
				content: "Calculate shipping costs by origin, destination, weight, and speed with American Shipping & Logistics instant quote tool."
			},
			{
				property: "og:title",
				content: "Get a shipping quote — American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Instant shipping quotes in seconds."
			}
		],
		links: [{
			rel: "canonical",
			href: "/quote"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$3, "component")
});
var $$splitComponentImporter$2 = () => import("./services-DQKwNRLh.mjs");
var Route$3 = createFileRoute("/services")({
	head: () => ({
		meta: [
			{ title: "Services — American Shipping & Logistics" },
			{
				name: "description",
				content: "Express shipping, freight, eCommerce logistics, and supply chain solutions from American Shipping & Logistics."
			},
			{
				property: "og:title",
				content: "Services — American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Everything American Shipping & Logistics ships, from letters to full truckloads."
			}
		],
		links: [{
			rel: "canonical",
			href: "/services"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$2, "component")
});
var BASE_URL = "";
var paths = [
	"/",
	"/about",
	"/services",
	"/pricing",
	"/quote",
	"/track",
	"/sustainability",
	"/careers",
	"/news",
	"/help",
	"/contact",
	"/legal",
	"/privacy",
	"/terms"
];
var Route$2 = createFileRoute("/sitemap.xml")({ server: { handlers: { GET: async () => {
	const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${paths.map((p) => `  <url><loc>${BASE_URL}${p}</loc><changefreq>weekly</changefreq></url>`).join("\n")}
</urlset>`;
	return new Response(xml, { headers: {
		"Content-Type": "application/xml",
		"Cache-Control": "public, max-age=3600"
	} });
} } } });
var $$splitComponentImporter$1 = () => import("./sustainability-DXi3zXg2.mjs");
var Route$1 = createFileRoute("/sustainability")({
	head: () => ({
		meta: [
			{ title: "Sustainability — American Shipping & Logistics" },
			{
				name: "description",
				content: "Net-zero by 2050. American Shipping & Logistics roadmap to greener logistics — electric fleets, sustainable fuels, and carbon-neutral shipping."
			},
			{
				property: "og:title",
				content: "Sustainability at American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Our path to net-zero logistics by 2050."
			},
			{
				property: "og:url",
				content: "/sustainability"
			}
		],
		links: [{
			rel: "canonical",
			href: "/sustainability"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter$1, "component")
});
var $$splitComponentImporter = () => import("./terms-Boyf8WXz.mjs");
var Route = createFileRoute("/terms")({
	head: () => ({
		meta: [
			{ title: "Terms of use — American Shipping & Logistics" },
			{
				name: "description",
				content: "Terms and conditions governing use of American Shipping & Logistics website and services."
			},
			{
				property: "og:title",
				content: "Terms of use — American Shipping & Logistics"
			},
			{
				property: "og:url",
				content: "/terms"
			}
		],
		links: [{
			rel: "canonical",
			href: "/terms"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter, "component")
});
var rootRouteChildren = {
	IndexRoute: Route$13.update({
		id: "/",
		path: "/",
		getParentRoute: () => Route$14
	}),
	AboutRoute: Route$12.update({
		id: "/about",
		path: "/about",
		getParentRoute: () => Route$14
	}),
	CareersRoute: Route$11.update({
		id: "/careers",
		path: "/careers",
		getParentRoute: () => Route$14
	}),
	ContactRoute: Route$10.update({
		id: "/contact",
		path: "/contact",
		getParentRoute: () => Route$14
	}),
	HelpRoute: Route$9.update({
		id: "/help",
		path: "/help",
		getParentRoute: () => Route$14
	}),
	LegalRoute: Route$8.update({
		id: "/legal",
		path: "/legal",
		getParentRoute: () => Route$14
	}),
	NewsRoute: Route$7.update({
		id: "/news",
		path: "/news",
		getParentRoute: () => Route$14
	}),
	PricingRoute: Route$6.update({
		id: "/pricing",
		path: "/pricing",
		getParentRoute: () => Route$14
	}),
	PrivacyRoute: Route$5.update({
		id: "/privacy",
		path: "/privacy",
		getParentRoute: () => Route$14
	}),
	QuoteRoute: Route$4.update({
		id: "/quote",
		path: "/quote",
		getParentRoute: () => Route$14
	}),
	ServicesRoute: Route$3.update({
		id: "/services",
		path: "/services",
		getParentRoute: () => Route$14
	}),
	SitemapDotxmlRoute: Route$2.update({
		id: "/sitemap.xml",
		path: "/sitemap.xml",
		getParentRoute: () => Route$14
	}),
	SustainabilityRoute: Route$1.update({
		id: "/sustainability",
		path: "/sustainability",
		getParentRoute: () => Route$14
	}),
	TermsRoute: Route.update({
		id: "/terms",
		path: "/terms",
		getParentRoute: () => Route$14
	}),
	TrackRoute: Route$15.update({
		id: "/track",
		path: "/track",
		getParentRoute: () => Route$14
	})
};
var routeTree = Route$14._addFileChildren(rootRouteChildren)._addFileTypes();
var getRouter = () => {
	return createRouter({
		routeTree,
		context: { queryClient: new QueryClient() },
		scrollRestoration: true,
		defaultPreloadStaleTime: 0
	});
};
//#endregion
export { getRouter };
