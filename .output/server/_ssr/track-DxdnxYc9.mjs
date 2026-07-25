import { f as lazyRouteComponent, p as createFileRoute } from "../_libs/@tanstack/react-router+[...].mjs";
import { n as stringType, t as objectType } from "../_libs/zod.mjs";
//#region node_modules/.nitro/vite/services/ssr/assets/track-DxdnxYc9.js
var $$splitComponentImporter = () => import("./track-DuNLcp4W.mjs");
var searchSchema = objectType({ id: stringType().optional() });
var Route = createFileRoute("/track")({
	validateSearch: searchSchema,
	head: () => ({
		meta: [
			{ title: "Track shipment — American Shipping & Logistics" },
			{
				name: "description",
				content: "Live tracking for American Shipping & Logistics parcels and freight worldwide."
			},
			{
				property: "og:title",
				content: "Track shipment — American Shipping & Logistics"
			},
			{
				property: "og:description",
				content: "Real-time shipment status, ETA, and proof of delivery."
			}
		],
		links: [{
			rel: "canonical",
			href: "/track"
		}]
	}),
	component: lazyRouteComponent($$splitComponentImporter, "component")
});
//#endregion
export { Route as t };
