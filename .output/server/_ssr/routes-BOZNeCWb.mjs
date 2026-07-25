import { n as __toESM } from "../_runtime.mjs";
import { n as express_handoff_default, t as cargo_port_default } from "./cargo-port-BARjQwb4.mjs";
import { n as require_jsx_runtime, r as require_react } from "../_libs/react+tanstack__react-query.mjs";
import { g as useNavigate, h as Link } from "../_libs/@tanstack/react-router+[...].mjs";
import { E as Leaf, L as Building2, T as Lightbulb, V as ArrowRight, _ as Package, a as Warehouse, c as Truck, f as Search, h as Plane, k as FileText, u as Ship, v as Newspaper } from "../_libs/lucide-react.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/routes-BOZNeCWb.js
var import_react = /* @__PURE__ */ __toESM(require_react());
var import_jsx_runtime = require_jsx_runtime();
var american_shipping_hero_default = "/assets/american-shipping-hero--WTupvYl.jpg";
var sustainability_van_default = "/assets/sustainability-van-DjoVtKcX.jpg";
var innovation_data_default = "/assets/innovation-data-0yhncJlr.jpg";
var global_planes_default = "/assets/global-planes-Dt22eXIg.jpg";
function HomePage() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Hero, {}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)(QuickActions, {}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Bulletin, {}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Divisions, {}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)(BusinessSplit, {}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Updates, {}),
		/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Highlights, {})
	] });
}
function Hero() {
	const navigate = useNavigate();
	const [tracking, setTracking] = (0, import_react.useState)("");
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "relative overflow-hidden bg-brand",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
				src: american_shipping_hero_default,
				alt: "",
				width: 1600,
				height: 900,
				loading: "eager",
				decoding: "async",
				fetchPriority: "high",
				sizes: "100vw",
				srcSet: `${american_shipping_hero_default} 1600w`,
				className: "pointer-events-none absolute inset-0 h-full w-full object-cover opacity-70 mix-blend-multiply"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", { className: "pointer-events-none absolute inset-0 bg-gradient-to-r from-brand via-brand/85 to-brand/30" }),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", { className: "pointer-events-none absolute inset-0 grid-lines opacity-30" }),
			/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "container-x relative grid gap-10 pb-14 pt-14 md:pt-20 lg:grid-cols-[1.2fr_1fr] lg:gap-12 lg:pb-20",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "flex flex-col justify-center text-brand-foreground",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("h1", {
						className: "font-display text-5xl font-bold leading-[1.02] tracking-tight md:text-6xl lg:text-7xl",
						children: [
							"American Shipping",
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("br", {}),
							"& Logistics.",
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("br", {}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
								className: "text-accent",
								children: "Excellence, delivered."
							})
						]
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "mt-5 max-w-xl text-lg text-brand-foreground/80",
						children: "Track a shipment, get an instant quote, or book a pickup — all in one place. American Shipping & Logistics connects 220+ countries with one simple experience."
					})]
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
					className: "relative",
					children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
						className: "rounded-sm border-2 border-brand-foreground/10 bg-background p-6 shadow-2xl md:p-7",
						children: [
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
								className: "font-display text-xl font-bold",
								children: "Track Your Shipment"
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
								className: "mt-1 text-sm text-muted-foreground",
								children: "Enter your tracking number(s)"
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("form", {
								onSubmit: (e) => {
									e.preventDefault();
									navigate({
										to: "/track",
										search: { id: tracking || "VLT-0000000" }
									});
								},
								className: "mt-4",
								children: [
									/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
										className: "flex items-center gap-2 rounded-sm border-2 border-border bg-background px-3 focus-within:border-accent",
										children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Search, { className: "h-4 w-4 text-muted-foreground" }), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("input", {
											value: tracking,
											onChange: (e) => setTracking(e.target.value),
											placeholder: "e.g. VLT-4820193",
											className: "w-full bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground/70"
										})]
									}),
									/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
										type: "submit",
										className: "mt-3 w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
										children: "Track"
									}),
									/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("p", {
										className: "mt-3 text-xs text-muted-foreground",
										children: [
											"Try",
											" ",
											/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
												type: "button",
												onClick: () => setTracking("VLT-4820193"),
												className: "font-semibold text-accent underline underline-offset-2",
												children: "VLT-4820193"
											}),
											" ",
											"for a live example."
										]
									})
								]
							})
						]
					})
				})]
			})
		]
	});
}
function QuickActions() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
		className: "border-b border-border bg-background",
		children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
			className: "container-x grid gap-0 divide-y divide-border md:grid-cols-3 md:divide-x md:divide-y-0",
			children: [
				{
					icon: Package,
					title: "Ship Now",
					desc: "Find the right service for your parcel in seconds.",
					to: "/quote"
				},
				{
					icon: FileText,
					title: "Get a Quote",
					desc: "Estimate cost, share and compare pricing before you book.",
					to: "/quote"
				},
				{
					icon: Building2,
					title: "Request a Business Account",
					desc: "Shipping regularly? Unlock volume discounts and priority support.",
					to: "/contact"
				}
			].map(({ icon: Icon, title, desc, to }) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(Link, {
				to,
				className: "group flex items-start gap-4 p-6 transition hover:bg-surface md:p-8",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
					className: "grid h-12 w-12 shrink-0 place-items-center rounded-sm bg-brand text-brand-foreground",
					children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, {
						className: "h-5 w-5",
						strokeWidth: 2.5
					})
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "min-w-0",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
						className: "flex items-center gap-2 font-display text-lg font-bold group-hover:text-accent",
						children: [title, /* @__PURE__ */ (0, import_jsx_runtime.jsx)(ArrowRight, { className: "h-4 w-4 -translate-x-1 opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100" })]
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
						className: "mt-1 text-sm text-muted-foreground",
						children: desc
					})]
				})]
			}, title))
		})
	});
}
function Bulletin() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
		className: "bg-surface",
		children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
			className: "container-x grid gap-6 py-14 md:grid-cols-[1.4fr_1fr] md:items-center md:py-16",
			children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", { children: [
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "font-mono text-xs font-bold uppercase tracking-widest text-accent",
					children: "Global trade update"
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
					className: "mt-2 font-display text-3xl font-bold md:text-4xl",
					children: "Navigating the latest tariff developments."
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "mt-3 max-w-xl text-muted-foreground",
					children: "Global trade is becoming increasingly complex as new tariffs and reciprocal measures emerge across countries and industries. American Shipping & Logistics is committed to helping you navigate."
				}),
				/* @__PURE__ */ (0, import_jsx_runtime.jsxs)(Link, {
					to: "/services",
					className: "mt-6 inline-flex items-center gap-2 rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
					children: ["Explore our solutions ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)(ArrowRight, { className: "h-4 w-4" })]
				})
			] }), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
				className: "relative hidden overflow-hidden rounded-sm border border-border md:block",
				children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
					src: sustainability_van_default,
					alt: "American Shipping & Logistics delivery driver",
					width: 1e3,
					height: 800,
					loading: "lazy",
					decoding: "async",
					sizes: "(min-width: 768px) 40vw, 100vw",
					srcSet: `${sustainability_van_default} 1000w`,
					className: "h-full w-full object-cover"
				})
			})]
		})
	});
}
function Divisions() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x py-16 md:py-20",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "font-mono text-xs font-bold uppercase tracking-widest text-accent",
				children: "Document and Parcel Shipping"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
				className: "mt-2 max-w-2xl font-display text-3xl font-bold md:text-4xl",
				children: "For all shippers."
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "mt-3 max-w-2xl text-muted-foreground",
				children: "American Shipping & Logistics Express is the undisputed global leader in international express shipping. We deliver time-definite service to 220+ countries with real-time tracking, customs clearance, and full insurance coverage."
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "mt-10 grid gap-6 md:grid-cols-3",
				children: [
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
						className: "relative overflow-hidden rounded-sm border border-border md:col-span-1",
						children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
							src: express_handoff_default,
							alt: "American Shipping & Logistics package handoff",
							width: 1200,
							height: 900,
							loading: "lazy",
							decoding: "async",
							sizes: "(min-width: 768px) 33vw, 100vw",
							srcSet: `${express_handoff_default} 1200w`,
							className: "h-full w-full object-cover"
						})
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
						className: "flex flex-col gap-5 rounded-sm border border-border bg-surface p-8",
						children: [
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
								className: "grid h-12 w-12 place-items-center rounded-sm bg-brand text-brand-foreground",
								children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Plane, {
									className: "h-6 w-6",
									strokeWidth: 2.5
								})
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h3", {
								className: "font-display text-2xl font-bold",
								children: "American Shipping Express"
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("ul", {
								className: "grid grid-cols-2 gap-y-2 text-sm text-muted-foreground",
								children: [
									"Next possible business day",
									"Flexible import/export",
									"Tailored business solutions",
									"Wide variety of options"
								].map((f) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("li", {
									className: "flex items-start gap-2",
									children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", { className: "mt-1.5 h-1 w-1 rounded-full bg-accent" }), f]
								}, f))
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsxs)(Link, {
								to: "/services",
								className: "mt-auto inline-flex w-fit items-center gap-2 rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
								children: ["Explore American Shipping Express ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)(ArrowRight, { className: "h-4 w-4" })]
							})
						]
					}),
					/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
						className: "flex flex-col justify-center gap-4 rounded-sm bg-brand p-8 text-brand-foreground",
						children: [
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
								className: "text-xs font-bold uppercase tracking-widest",
								children: "On-time performance"
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
								className: "font-display text-6xl font-bold",
								children: "99.2%"
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
								className: "max-w-sm text-brand-foreground/80",
								children: "of American Shipping & Logistics Express shipments arrive on or before their promised time — measured across 220+ countries, every day."
							}),
							/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
								className: "mt-4 grid grid-cols-3 gap-4 border-t border-brand-foreground/20 pt-4 text-sm",
								children: [
									/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Stat, {
										n: "6.1M",
										label: "Parcels / day"
									}),
									/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Stat, {
										n: "4,500",
										label: "Service points"
									}),
									/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Stat, {
										n: "47k",
										label: "Vehicles"
									})
								]
							})
						]
					})
				]
			})
		]
	});
}
function Stat({ n, label }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", { children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
		className: "font-display text-2xl font-bold",
		children: n
	}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
		className: "text-xs text-brand-foreground/70",
		children: label
	})] });
}
function BusinessSplit() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
		className: "bg-surface",
		children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
			className: "container-x space-y-10 py-16 md:py-20",
			children: [
				{
					tag: "Retailer or Volume Shipping",
					title: "Business only.",
					desc: "Two divisions offering reliable business shipping for e-commerce, supplier and manufacturing.",
					image: express_handoff_default,
					alt: "Business shipping partners exchanging a parcel",
					items: [{
						icon: Truck,
						title: "American Shipping &amp; Logistics eCommerce",
						desc: "Domestic and international residential delivery and returns."
					}, {
						icon: Plane,
						title: "American Shipping Express",
						desc: "Fast, door-to-door, courier delivered to 220+ countries."
					}]
				},
				{
					tag: "Cargo Shipping",
					title: "Global Forwarding.",
					desc: "Discover shipping and logistics service options from American Shipping &amp; Logistics Global Forwarding.",
					image: cargo_port_default,
					alt: "Freight workers at a cargo shipping port",
					items: [{
						icon: Plane,
						title: "Air Freight",
						desc: "Charter, consolidated and time-critical air cargo."
					}, {
						icon: Ship,
						title: "Ocean Freight",
						desc: "FCL, LCL, and specialised container services."
					}]
				},
				{
					tag: "Enterprise Logistics Services",
					title: "American Shipping &amp; Logistics Supply Chain.",
					desc: "Find out how American Shipping &amp; Logistics Supply Chain can revolutionize your business as a 3PL provider.",
					image: american_shipping_hero_default,
					alt: "Warehouse operations",
					items: [{
						icon: Warehouse,
						title: "Warehousing",
						desc: "Flexible storage, pick, pack, and kitting."
					}, {
						icon: Truck,
						title: "Transport & Packaging",
						desc: "Distribution, service logistics and more."
					}]
				}
			].map((row, idx) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: `grid overflow-hidden rounded-sm border border-border bg-background lg:grid-cols-2 ${idx % 2 === 1 ? "lg:grid-flow-dense" : ""}`,
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
					className: `relative min-h-[240px] ${idx % 2 === 1 ? "lg:col-start-2" : ""}`,
					children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
						src: row.image,
						alt: row.alt,
						width: 1200,
						height: 900,
						loading: "lazy",
						decoding: "async",
						sizes: "(min-width: 1024px) 50vw, 100vw",
						srcSet: `${row.image} 1200w`,
						className: "absolute inset-0 h-full w-full object-cover"
					})
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "p-8 md:p-10",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "font-mono text-xs font-bold uppercase tracking-widest text-accent",
							children: row.tag
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h3", {
							className: "mt-2 font-display text-2xl font-bold md:text-3xl",
							children: row.title
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "mt-3 max-w-md text-muted-foreground",
							children: row.desc
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
							className: "mt-6 grid gap-3 sm:grid-cols-2",
							children: row.items.map(({ icon: Icon, title, desc }) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
								className: "rounded-sm border-l-4 border-brand bg-surface p-4",
								children: [
									/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, {
										className: "h-5 w-5 text-accent",
										strokeWidth: 2
									}),
									/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
										className: "mt-2 font-display text-base font-bold",
										children: title
									}),
									/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
										className: "mt-1 text-xs text-muted-foreground",
										children: desc
									})
								]
							}, title))
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)(Link, {
							to: "/services",
							className: "mt-6 inline-flex items-center gap-2 rounded-sm bg-accent px-5 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
							children: ["Explore ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)(ArrowRight, { className: "h-4 w-4" })]
						})
					]
				})]
			}, row.tag))
		})
	});
}
function Updates() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x py-16 md:py-20",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "font-mono text-xs font-bold uppercase tracking-widest text-accent",
				children: "Important Service Updates"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h2", {
				className: "mt-2 font-display text-3xl font-bold md:text-4xl",
				children: "Service bulletins."
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "mt-2 text-muted-foreground",
				children: "Keep up to date with news and alerts."
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "mt-8 grid gap-6 md:grid-cols-2",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("ul", {
					className: "divide-y divide-border rounded-2xl border border-border bg-surface/60",
					children: [
						"American Shipping & Logistics Express will implement weekly fuel surcharge updates",
						"New customs rules for shipments under €150 from outside the EU",
						"Operational update: Middle East corridor",
						"Peak-season capacity now available across all lanes"
					].map((u) => /* @__PURE__ */ (0, import_jsx_runtime.jsx)("li", { children: /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("a", {
						href: "#",
						className: "group flex items-center justify-between gap-4 px-5 py-4 hover:bg-surface",
						children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
							className: "text-sm font-medium",
							children: u
						}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(ArrowRight, { className: "h-4 w-4 shrink-0 text-accent transition group-hover:translate-x-1" })]
					}) }, u))
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "relative overflow-hidden rounded-2xl border border-border",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
							src: global_planes_default,
							alt: "Global air network",
							className: "h-full w-full object-cover",
							loading: "lazy",
							decoding: "async"
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", { className: "absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" }),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "absolute bottom-4 left-4 text-white",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
								className: "text-xs uppercase tracking-widest text-white/70",
								children: "Network"
							}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
								className: "mt-1 text-sm font-semibold",
								children: "Serving 220+ countries"
							})]
						})
					]
				})]
			})
		]
	});
}
function Highlights() {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsx)("section", {
		className: "container-x pb-24",
		children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
			className: "grid gap-6 md:grid-cols-3",
			children: [
				{
					icon: Leaf,
					title: "Sustainability",
					desc: "Low-carbon supply chains, GoGreen Plus, and net-zero operations by 2050.",
					detail: "We are investing $7 billion by 2030 in clean fuels, electrified fleets, and climate-neutral buildings.",
					image: sustainability_van_default,
					alt: "American Shipping & Logistics electric delivery van driver"
				},
				{
					icon: Lightbulb,
					title: "Innovation",
					desc: "Customer-centric innovation, trend research and next-generation solutions.",
					detail: "AI-optimized routing, autonomous warehouses, and real-time shipment visibility across every mode.",
					image: innovation_data_default,
					alt: "Global data connectivity visualization"
				},
				{
					icon: Newspaper,
					title: "Global Connectedness",
					desc: "The American Shipping & Logistics 2026 report — the most comprehensive view of globalization available.",
					detail: "Trade flows, lane analytics, and economic insights drawn from 1.9 billion shipments annually.",
					image: global_planes_default,
					alt: "Cargo planes at sunrise"
				}
			].map(({ icon: Icon, title, desc, detail, image, alt }) => /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "group flex flex-col overflow-hidden rounded-sm border border-border bg-background transition hover:border-accent",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "relative aspect-[4/3] overflow-hidden",
					children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("img", {
						src: image,
						alt,
						width: 800,
						height: 600,
						loading: "lazy",
						decoding: "async",
						sizes: "(min-width: 768px) 33vw, 100vw",
						srcSet: `${image} 800w`,
						className: "h-full w-full object-cover transition duration-500 group-hover:scale-105"
					}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
						className: "absolute left-4 top-4 grid h-10 w-10 place-items-center rounded-sm bg-brand text-brand-foreground shadow-md",
						children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, {
							className: "h-4 w-4",
							strokeWidth: 2.5
						})
					})]
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "flex flex-1 flex-col p-6",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h3", {
							className: "font-display text-xl font-bold",
							children: title
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "mt-2 text-sm text-muted-foreground",
							children: desc
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
							className: "mt-2 text-sm text-muted-foreground",
							children: detail
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("span", {
							className: "mt-6 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-accent",
							children: ["Learn more ", /* @__PURE__ */ (0, import_jsx_runtime.jsx)(ArrowRight, { className: "h-4 w-4 transition group-hover:translate-x-1" })]
						})
					]
				})]
			}, title))
		})
	});
}
//#endregion
export { HomePage as component };
