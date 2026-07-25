import { n as __toESM } from "../_runtime.mjs";
import { n as require_jsx_runtime, r as require_react } from "../_libs/react+tanstack__react-query.mjs";
import { g as useNavigate } from "../_libs/@tanstack/react-router+[...].mjs";
import { N as Circle, P as CircleCheck, S as MapPin } from "../_libs/lucide-react.mjs";
import { t as Route } from "./track-DxdnxYc9.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/track-DuNLcp4W.js
var import_react = /* @__PURE__ */ __toESM(require_react());
var import_jsx_runtime = require_jsx_runtime();
var API_BASE = "http://localhost/ships";
async function fetchTracking(id) {
	const url = `${API_BASE}/php/process/track_ajax.php?id=${encodeURIComponent(id)}`;
	const response = await fetch(url, {
		method: "GET",
		headers: { Accept: "application/json" }
	});
	if (!response.ok) {
		const text = await response.text();
		let detail = text;
		try {
			const parsed = JSON.parse(text);
			detail = parsed.message || parsed.debug || text;
		} catch {}
		throw new Error(`Tracking request failed: ${response.status} — ${detail}`);
	}
	return response.json();
}
var STATUS_LABELS = {
	pending: {
		label: "Pending",
		color: "#6b7280"
	},
	processing: {
		label: "Processing",
		color: "#3b82f6"
	},
	picked_up: {
		label: "Picked Up",
		color: "#8b5cf6"
	},
	in_transit: {
		label: "In Transit",
		color: "#f59e0b"
	},
	at_hub: {
		label: "At Hub",
		color: "#f59e0b"
	},
	out_for_delivery: {
		label: "Out for Delivery",
		color: "#10b981"
	},
	delivered: {
		label: "Delivered",
		color: "#059669"
	},
	customs_inspection: {
		label: "Customs Inspection",
		color: "#ef4444"
	},
	customs_clearance: {
		label: "Customs Clearance",
		color: "#f59e0b"
	},
	customs_delayed: {
		label: "Customs Delayed",
		color: "#ef4444"
	},
	held: {
		label: "On Hold",
		color: "#ef4444"
	},
	returned: {
		label: "Returned",
		color: "#6b7280"
	},
	cancelled: {
		label: "Cancelled",
		color: "#6b7280"
	},
	Booked: {
		label: "Booked",
		color: "#3b82f6"
	},
	Approved: {
		label: "Approved",
		color: "#10b981"
	},
	Delivered: {
		label: "Delivered",
		color: "#059669"
	}
};
var PROGRESS_MAP = {
	pending: 5,
	processing: 10,
	picked_up: 20,
	at_warehouse: 30,
	in_transit: 50,
	at_hub: 60,
	customs_inspection: 55,
	customs_clearance: 65,
	out_for_delivery: 80,
	delivered: 100,
	returned: 100,
	cancelled: 100,
	Booked: 10,
	Approved: 25,
	Delivered: 100
};
function statusInfo(status) {
	return STATUS_LABELS[status] ?? {
		label: status.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase()),
		color: "#6b7280"
	};
}
function formatDateTime(value) {
	if (!value) return "—";
	const d = new Date(value);
	if (Number.isNaN(d.getTime())) return value;
	return d.toLocaleString("en-US", {
		month: "short",
		day: "numeric",
		year: "numeric",
		hour: "2-digit",
		minute: "2-digit"
	});
}
function TrackPage() {
	const { id } = Route.useSearch();
	const navigate = useNavigate();
	const [input, setInput] = (0, import_react.useState)(id ?? "");
	const [loading, setLoading] = (0, import_react.useState)(false);
	const [error, setError] = (0, import_react.useState)(null);
	const [data, setData] = (0, import_react.useState)(null);
	(0, import_react.useEffect)(() => {
		if (!id) return;
		let cancelled = false;
		setLoading(true);
		setError(null);
		setData(null);
		fetchTracking(id).then((res) => {
			if (cancelled) return;
			if (!res.found || !res.shipment) {
				setError(res.message ?? "Shipment not found.");
				return;
			}
			setData({
				shipment: res.shipment,
				history: res.history
			});
		}).catch((err) => {
			if (cancelled) return;
			setError(err instanceof Error ? err.message : "Something went wrong.");
		}).finally(() => {
			if (!cancelled) setLoading(false);
		});
		return () => {
			cancelled = true;
		};
	}, [id]);
	const si = data ? statusInfo(data.shipment.status) : null;
	const progress = data ? PROGRESS_MAP[data.shipment.status] ?? 40 : 40;
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("section", {
		className: "container-x py-16 md:py-20",
		children: [
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
				className: "font-mono text-xs uppercase tracking-widest text-brand",
				children: "Live tracking"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h1", {
				className: "mt-2 font-display text-4xl font-bold md:text-5xl",
				children: "Where's my shipment?"
			}),
			/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("form", {
				onSubmit: (e) => {
					e.preventDefault();
					navigate({
						to: "/track",
						search: { id: input || "VLT-0000000" }
					});
				},
				className: "mt-8 flex max-w-2xl flex-col gap-3 rounded-2xl border border-border bg-surface/60 p-4 sm:flex-row",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("input", {
					value: input,
					onChange: (e) => setInput(e.target.value),
					placeholder: "Enter tracking number, e.g. VLT-4820193",
					className: "flex-1 rounded-lg border border-border bg-background px-4 py-3 text-sm outline-none placeholder:text-muted-foreground/70 focus:border-brand"
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
					className: "rounded-sm bg-accent px-6 py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90",
					children: "Track"
				})]
			}),
			!id && /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("p", {
				className: "mt-6 text-sm text-muted-foreground",
				children: [
					"Enter a tracking number to see live status. Try",
					" ",
					/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
						onClick: () => {
							setInput("VLT-4820193");
							navigate({
								to: "/track",
								search: { id: "VLT-4820193" }
							});
						},
						className: "text-brand underline underline-offset-2",
						children: "VLT-4820193"
					}),
					"."
				]
			}),
			loading && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
				className: "mt-10 rounded-2xl border border-border bg-surface/60 p-6 md:p-8",
				children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
					className: "text-sm text-muted-foreground",
					children: "Loading tracking information…"
				})
			}),
			error && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
				className: "mt-8 rounded-lg border border-red-200 bg-red-50 p-6 text-sm text-red-800",
				children: error
			}),
			data && si && /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
				className: "mt-10 grid gap-6 lg:grid-cols-[1.4fr_1fr]",
				children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "rounded-2xl border border-border bg-surface/60 p-6 md:p-8",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "grid grid-cols-[minmax(0,1fr)_auto] items-start gap-4",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
								className: "min-w-0",
								children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
									className: "font-mono text-xs text-muted-foreground",
									children: data.shipment.tracking_number
								}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("h2", {
									className: "mt-1 truncate font-display text-2xl font-bold",
									children: [si.label, data.shipment.estimated_delivery ? ` — arriving ${formatDateTime(data.shipment.estimated_delivery)}` : ""]
								})]
							}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", {
								className: "shrink-0 rounded-full px-3 py-1 text-xs font-semibold text-white",
								style: { background: si.color },
								children: si.label
							})]
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "mt-6",
							children: [
								/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
									className: "flex items-center justify-between text-xs text-muted-foreground",
									children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", { children: data.shipment.origin_city ?? "Origin" }), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("span", { children: data.shipment.destination_city ?? "Destination" })]
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
									className: "mt-2 h-2 overflow-hidden rounded-full bg-background",
									children: /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
										className: "h-full rounded-full bg-brand",
										style: { width: `${progress}%` }
									})
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
									className: "mt-1 text-right font-mono text-xs text-brand",
									children: [progress, "%"]
								})
							]
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsx)("ol", {
							className: "mt-8 space-y-4",
							children: data.history.map((ev, i) => {
								const time = (() => {
									const d = new Date(ev.occurred_at);
									if (Number.isNaN(d.getTime())) return "—";
									return d.toLocaleTimeString("en-US", {
										hour: "2-digit",
										minute: "2-digit"
									});
								})();
								const date = (() => {
									const d = new Date(ev.occurred_at);
									if (Number.isNaN(d.getTime())) return "—";
									return d.toLocaleDateString("en-US", {
										month: "short",
										day: "numeric"
									});
								})();
								const isLast = i === data.history.length - 1;
								const isDone = i < data.history.length - 1;
								return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("li", {
									className: "flex gap-4",
									children: [/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
										className: "flex flex-col items-center",
										children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
											className: `grid h-9 w-9 shrink-0 place-items-center rounded-full border ${isDone ? "border-brand bg-brand text-brand-foreground" : "border-border bg-background text-muted-foreground"}`,
											children: isDone ? /* @__PURE__ */ (0, import_jsx_runtime.jsx)(CircleCheck, { className: "h-4 w-4" }) : /* @__PURE__ */ (0, import_jsx_runtime.jsx)(Circle, { className: "h-3 w-3" })
										}), !isLast && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", { className: `mt-1 h-8 w-px ${isDone ? "bg-brand/50" : "bg-border"}` })]
									}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
										className: "min-w-0 flex-1 pb-2",
										children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
											className: `text-sm font-medium ${isDone ? "text-foreground" : "text-muted-foreground"}`,
											children: ev.description ?? ev.status.replace(/_/g, " ")
										}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
											className: "mt-0.5 text-xs text-muted-foreground",
											children: [
												[ev.location, ev.transit_location].filter(Boolean).join(" · ") || "—",
												" ·",
												" ",
												date,
												" ",
												time !== "—" && `· ${time}`
											]
										})]
									})]
								}, i);
							})
						})
					]
				}), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
					className: "space-y-4",
					children: [
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)(InfoCard, {
							title: "Route",
							children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(RouteRow, {
								icon: MapPin,
								label: "From",
								value: `${data.shipment.origin_city ?? ""} ${data.shipment.origin_country ?? ""}`.trim() || "—"
							}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)(RouteRow, {
								icon: MapPin,
								label: "To",
								value: `${data.shipment.destination_city ?? ""} ${data.shipment.destination_country ?? ""}`.trim() || "—"
							})]
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)(InfoCard, {
							title: "Details",
							children: [
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Row, {
									label: "Service",
									value: data.shipment.service_type ?? "—"
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Row, {
									label: "Weight",
									value: data.shipment.total_weight ? `${data.shipment.total_weight} kg` : "—"
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Row, {
									label: "Estimated delivery",
									value: formatDateTime(data.shipment.estimated_delivery)
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Row, {
									label: "Status",
									value: si.label
								})
							]
						}),
						/* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
							className: "rounded-2xl border border-border bg-surface/60 p-6",
							children: [
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h3", {
									className: "font-semibold",
									children: "Delivery preferences"
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", {
									className: "mt-2 text-sm text-muted-foreground",
									children: "Reschedule, redirect, or leave delivery instructions for this shipment."
								}),
								/* @__PURE__ */ (0, import_jsx_runtime.jsx)("button", {
									className: "mt-4 w-full rounded-md border border-border py-2.5 text-sm font-medium hover:bg-surface",
									children: "Manage delivery"
								})
							]
						})
					]
				})]
			})
		]
	});
}
function InfoCard({ title, children }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
		className: "rounded-2xl border border-border bg-surface/60 p-6",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("h3", {
			className: "font-semibold",
			children: title
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("dl", {
			className: "mt-4 space-y-3",
			children
		})]
	});
}
function Row({ label, value }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
		className: "flex justify-between gap-4 text-sm",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("dt", {
			className: "text-muted-foreground",
			children: label
		}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("dd", {
			className: "text-right font-medium",
			children: value
		})]
	});
}
function RouteRow({ icon: Icon, label, value }) {
	return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
		className: "flex items-center gap-3 text-sm",
		children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)(Icon, { className: "h-4 w-4 text-brand" }), /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", {
			className: "min-w-0 flex-1",
			children: [/* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
				className: "text-xs text-muted-foreground",
				children: label
			}), /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", {
				className: "truncate font-medium",
				children: value
			})]
		})]
	});
}
//#endregion
export { TrackPage as component };
