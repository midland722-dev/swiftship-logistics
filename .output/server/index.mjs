globalThis.__nitro_main__ = import.meta.url;
import { a as FastResponse, i as defineLazyEventHandler, n as HTTPError, t as H3Core } from "./_libs/h3+rou3+srvx.mjs";
import { t as HookableCore } from "./_libs/hookable.mjs";
//#region #nitro-vite-setup
function lazyService(loader) {
	let promise, mod;
	return { fetch(req) {
		if (mod) return mod.fetch(req);
		if (!promise) promise = loader().then((_mod) => mod = _mod.default || _mod);
		return promise.then((mod) => mod.fetch(req));
	} };
}
var services = { ["ssr"]: lazyService(() => import("./_ssr/ssr.mjs")) };
globalThis.__nitro_vite_envs__ = services;
//#endregion
//#region #nitro/virtual/public-assets-data
var public_assets_data_default = {
	"/favicon1.ico": {
		"type": "image/vnd.microsoft.icon",
		"etag": "\"10be-yMnBTkHSF6PmIjpJdFLhvTDa6Xg\"",
		"mtime": "2026-07-09T16:19:18.894Z",
		"size": 4286,
		"path": "../public/favicon1.ico"
	},
	"/favicon.ico": {
		"type": "image/vnd.microsoft.icon",
		"etag": "\"10be-yMnBTkHSF6PmIjpJdFLhvTDa6Xg\"",
		"mtime": "2026-07-09T16:19:18.894Z",
		"size": 4286,
		"path": "../public/favicon.ico"
	},
	"/robots.txt": {
		"type": "text/plain; charset=utf-8",
		"etag": "\"17-ZZkCVrbr4BSdjt/K43J0tq8+Qq4\"",
		"mtime": "2026-07-17T14:03:51.000Z",
		"size": 23,
		"path": "../public/robots.txt"
	},
	"/assets/about-De4k51NE.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1153-/8RLRi8FyruOta9WUuzhRz+AiiY\"",
		"mtime": "2026-07-24T15:34:39.668Z",
		"size": 4435,
		"path": "../public/assets/about-De4k51NE.js"
	},
	"/assets/careers-2e7BmsY6.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"95e-5FZV3JpAOFBOgtJwYDAnBn0DfPc\"",
		"mtime": "2026-07-24T15:34:39.671Z",
		"size": 2398,
		"path": "../public/assets/careers-2e7BmsY6.js"
	},
	"/assets/cargo-port-B29_fnH6.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"62-pOfD1k0KiJYXAJ+oyr5AiZRrz7o\"",
		"mtime": "2026-07-24T15:34:39.674Z",
		"size": 98,
		"path": "../public/assets/cargo-port-B29_fnH6.js"
	},
	"/assets/circle-check-COvndBpw.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"a7-VdWhfzp+dYy4rG7LDJS9k0/gcoI\"",
		"mtime": "2026-07-24T15:34:39.677Z",
		"size": 167,
		"path": "../public/assets/circle-check-COvndBpw.js"
	},
	"/assets/american-shipping-hero--WTupvYl.jpg": {
		"type": "image/jpeg",
		"etag": "\"40126-PQVzMLEtYw4FIjznkAg5mLCK9fM\"",
		"mtime": "2026-07-24T15:34:39.827Z",
		"size": 262438,
		"path": "../public/assets/american-shipping-hero--WTupvYl.jpg"
	},
	"/assets/contact-IINwOQTt.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"196b-m8oQmzhgcQJskAeKO5csLvuWORc\"",
		"mtime": "2026-07-24T15:34:39.690Z",
		"size": 6507,
		"path": "../public/assets/contact-IINwOQTt.js"
	},
	"/assets/cargo-port-CkDtuTJJ.jpg": {
		"type": "image/jpeg",
		"etag": "\"21dcf-HYC9igHS8PQUcgWz4fc12S4cI6A\"",
		"mtime": "2026-07-24T15:34:39.830Z",
		"size": 138703,
		"path": "../public/assets/cargo-port-CkDtuTJJ.jpg"
	},
	"/assets/help-CBeglIEr.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"d2b-7HQpShHTOVh5B8qfxLA2mElc54c\"",
		"mtime": "2026-07-24T15:34:39.694Z",
		"size": 3371,
		"path": "../public/assets/help-CBeglIEr.js"
	},
	"/assets/global-planes-Dt22eXIg.jpg": {
		"type": "image/jpeg",
		"etag": "\"65fd-dyR4aR9nqj1x/EyTM+aPuHV0l2w\"",
		"mtime": "2026-07-24T15:34:39.839Z",
		"size": 26109,
		"path": "../public/assets/global-planes-Dt22eXIg.jpg"
	},
	"/assets/leaf-D4A1Frq5.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"fe-ZlJ/RWx+kKJ8Oh43bylrLm+QwzU\"",
		"mtime": "2026-07-24T15:34:39.697Z",
		"size": 254,
		"path": "../public/assets/leaf-D4A1Frq5.js"
	},
	"/assets/express-handoff-CJxVwHen.jpg": {
		"type": "image/jpeg",
		"etag": "\"1a06c-wVq0w4EzNddselmC+LvtDuBpnXo\"",
		"mtime": "2026-07-24T15:34:39.834Z",
		"size": 106604,
		"path": "../public/assets/express-handoff-CJxVwHen.jpg"
	},
	"/assets/map-pin-DMSvkB5a.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f8-qquEsoT7fItJzJUNUzbx+R4Ry30\"",
		"mtime": "2026-07-24T15:34:39.709Z",
		"size": 248,
		"path": "../public/assets/map-pin-DMSvkB5a.js"
	},
	"/assets/legal-Csrbzb6C.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"7cf-E07u9qMJf2qItfqVdcvteeK+z/Y\"",
		"mtime": "2026-07-24T15:34:39.700Z",
		"size": 1999,
		"path": "../public/assets/legal-Csrbzb6C.js"
	},
	"/assets/pricing-Bh9Lvlyp.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f13-oA7+6Q7aF6/tIhLOqZtjr9AdDoM\"",
		"mtime": "2026-07-24T15:34:39.716Z",
		"size": 3859,
		"path": "../public/assets/pricing-Bh9Lvlyp.js"
	},
	"/assets/news-DHp9gc5X.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"82a-PQarF4WY53hP3upBHT6RdRXYwKI\"",
		"mtime": "2026-07-24T15:34:39.712Z",
		"size": 2090,
		"path": "../public/assets/news-DHp9gc5X.js"
	},
	"/assets/quote-BsdEUJhb.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"18f0-kgGxN4K1YX0aSX0c2+7Zm8FCIf4\"",
		"mtime": "2026-07-24T15:34:39.722Z",
		"size": 6384,
		"path": "../public/assets/quote-BsdEUJhb.js"
	},
	"/assets/privacy-DIe1D-4I.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"78a-dfb1bdRVSj6CFxgYIo9NYoGDM+M\"",
		"mtime": "2026-07-24T15:34:39.719Z",
		"size": 1930,
		"path": "../public/assets/privacy-DIe1D-4I.js"
	},
	"/assets/routes-B2GgZ_fI.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"43f4-ioqu3ci+FrJasP7OxM+fGivL2lQ\"",
		"mtime": "2026-07-24T15:34:39.729Z",
		"size": 17396,
		"path": "../public/assets/routes-B2GgZ_fI.js"
	},
	"/assets/index-CyL0KTyv.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"649cd-vbThPUWB2nwFGCJ0FB2H18tGwqQ\"",
		"mtime": "2026-07-24T15:34:39.664Z",
		"size": 412109,
		"path": "../public/assets/index-CyL0KTyv.js"
	},
	"/assets/react-C1VktWof.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1f64-3p4bCHIDfvittea0i4AgXd8BRqs\"",
		"mtime": "2026-07-24T15:34:39.725Z",
		"size": 8036,
		"path": "../public/assets/react-C1VktWof.js"
	},
	"/assets/innovation-data-0yhncJlr.jpg": {
		"type": "image/jpeg",
		"etag": "\"163e7-Pa0RMC32PdjprWbjPD8np49xo38\"",
		"mtime": "2026-07-24T15:34:39.847Z",
		"size": 91111,
		"path": "../public/assets/innovation-data-0yhncJlr.jpg"
	},
	"/assets/service-ecommerce-fV2g4Ikv.jpg": {
		"type": "image/jpeg",
		"etag": "\"348e-GagV+DBOyeHRIGbA5gDS7/O8lCA\"",
		"mtime": "2026-07-24T15:34:39.856Z",
		"size": 13454,
		"path": "../public/assets/service-ecommerce-fV2g4Ikv.jpg"
	},
	"/assets/service-express-BQggO2dn.jpg": {
		"type": "image/jpeg",
		"etag": "\"2dba-66mxiCzV8+DlYBbq23ROodfCdDI\"",
		"mtime": "2026-07-24T15:34:39.860Z",
		"size": 11706,
		"path": "../public/assets/service-express-BQggO2dn.jpg"
	},
	"/assets/service-freight-DCl1DPsi.jpg": {
		"type": "image/jpeg",
		"etag": "\"32d2-cdbtFfrRS3U08IFc+ffvZwbFLy4\"",
		"mtime": "2026-07-24T15:34:39.863Z",
		"size": 13010,
		"path": "../public/assets/service-freight-DCl1DPsi.jpg"
	},
	"/assets/service-industrial-BjpKOjr3.jpg": {
		"type": "image/jpeg",
		"etag": "\"427c-tpNXglCfz64xCGM54LPDtAsOVrU\"",
		"mtime": "2026-07-24T15:34:39.866Z",
		"size": 17020,
		"path": "../public/assets/service-industrial-BjpKOjr3.jpg"
	},
	"/assets/service-ocean-air-DYb89scQ.jpg": {
		"type": "image/jpeg",
		"etag": "\"4f5d-q3cCWmE2KGjMGHHVB9TGDxT6YAk\"",
		"mtime": "2026-07-24T15:34:39.871Z",
		"size": 20317,
		"path": "../public/assets/service-ocean-air-DYb89scQ.jpg"
	},
	"/assets/service-supply-chain-B-tn33fr.jpg": {
		"type": "image/jpeg",
		"etag": "\"47ec-HwL6/PBX3mvxR+HUwtaAIGmka8s\"",
		"mtime": "2026-07-24T15:34:39.874Z",
		"size": 18412,
		"path": "../public/assets/service-supply-chain-B-tn33fr.jpg"
	},
	"/assets/services-C6IInlVO.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1be0-MI/7BwT5aR1HqZMJvHr3FSdyzzM\"",
		"mtime": "2026-07-24T15:34:39.765Z",
		"size": 7136,
		"path": "../public/assets/services-C6IInlVO.js"
	},
	"/assets/sustainability-it9xcnPO.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"cb5-502BUTA/xvQDYloRH/xirFeuMB4\"",
		"mtime": "2026-07-24T15:34:39.800Z",
		"size": 3253,
		"path": "../public/assets/sustainability-it9xcnPO.js"
	},
	"/assets/ship-5sxj54aN.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1d4-SYdeCZmzF/Jw6FLxhZ6jq9qP3QM\"",
		"mtime": "2026-07-24T15:34:39.781Z",
		"size": 468,
		"path": "../public/assets/ship-5sxj54aN.js"
	},
	"/assets/service-warehousing-DWt02ZfQ.jpg": {
		"type": "image/jpeg",
		"etag": "\"3994e-tyDE6crCSOfYbS4ixV8+MvccML8\"",
		"mtime": "2026-07-24T15:34:39.877Z",
		"size": 235854,
		"path": "../public/assets/service-warehousing-DWt02ZfQ.jpg"
	},
	"/assets/styles-TbfLtQq3.css": {
		"type": "text/css; charset=utf-8",
		"etag": "\"153e5-eG/VEyz1SwaN5RTrvoNw4r+QzMc\"",
		"mtime": "2026-07-24T15:34:39.881Z",
		"size": 87013,
		"path": "../public/assets/styles-TbfLtQq3.css"
	},
	"/assets/sustainability-van-DjoVtKcX.jpg": {
		"type": "image/jpeg",
		"etag": "\"109b9-tablrN1ewiWUXmM56k1Mg15Hs30\"",
		"mtime": "2026-07-24T15:34:39.886Z",
		"size": 68025,
		"path": "../public/assets/sustainability-van-DjoVtKcX.jpg"
	},
	"/assets/terms-B4iIoNHW.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"648-/GHphQDsCjlAfOifd2CKBhCJ7AU\"",
		"mtime": "2026-07-24T15:34:39.808Z",
		"size": 1608,
		"path": "../public/assets/terms-B4iIoNHW.js"
	},
	"/assets/track-CLRAvTcc.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"21e2-MhEAgaztkS57dLM2fwQSvt9gDi4\"",
		"mtime": "2026-07-24T15:34:39.811Z",
		"size": 8674,
		"path": "../public/assets/track-CLRAvTcc.js"
	},
	"/assets/shield-D9lACzDf.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"105-qShR17eOjnk153822JWyzzebYvs\"",
		"mtime": "2026-07-24T15:34:39.772Z",
		"size": 261,
		"path": "../public/assets/shield-D9lACzDf.js"
	},
	"/assets/truck-B71VRAAA.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"18b-3KqWCabtGi0jCmmqTEBQuu8Vuyc\"",
		"mtime": "2026-07-24T15:34:39.814Z",
		"size": 395,
		"path": "../public/assets/truck-B71VRAAA.js"
	},
	"/assets/warehouse-CfHCouOc.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"269-NLqjYMUi+3Be3BOmpfLSRyEL50Q\"",
		"mtime": "2026-07-24T15:34:39.817Z",
		"size": 617,
		"path": "../public/assets/warehouse-CfHCouOc.js"
	},
	"/assets/zap-Da2o1_10.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"fb-dpWD/CAKqJT35NDc7MDQIdfxjqs\"",
		"mtime": "2026-07-24T15:34:39.824Z",
		"size": 251,
		"path": "../public/assets/zap-Da2o1_10.js"
	}
};
//#endregion
//#region #nitro/virtual/public-assets
var publicAssetBases = {};
function isPublicAssetURL(id = "") {
	if (public_assets_data_default[id]) return true;
	for (const base in publicAssetBases) if (id.startsWith(base)) return true;
	return false;
}
//#endregion
//#region node_modules/nitro/dist/runtime/internal/route-rules.mjs
var headers = ((m) => function headersRouteRule(event) {
	for (const [key, value] of Object.entries(m.options || {})) event.res.headers.set(key, value);
});
//#endregion
//#region #nitro/virtual/routing
var findRouteRules = /* @__PURE__ */ (() => {
	const $0 = [{
		name: "headers",
		route: "/assets/**",
		handler: headers,
		options: { "cache-control": "public, max-age=31536000, immutable" }
	}];
	return (m, p) => {
		let r = [];
		if (p.charCodeAt(p.length - 1) === 47) p = p.slice(0, -1) || "/";
		let s = p.split("/");
		if (s.length > 1) {
			if (s[1] === "assets") r.unshift({
				data: $0,
				params: { "_": s.slice(2).join("/") }
			});
		}
		return r;
	};
})();
var _lazy_ZO2DvI = defineLazyEventHandler(() => import("./_chunks/renderer-template.mjs"));
var findRoute = /* @__PURE__ */ (() => {
	const data = {
		route: "/**",
		handler: _lazy_ZO2DvI
	};
	return ((_m, p) => {
		return {
			data,
			params: { "_": p.slice(1) }
		};
	});
})();
[].filter(Boolean);
//#endregion
//#region node_modules/nitro/dist/runtime/internal/error/prod.mjs
var errorHandler = (error, event) => {
	const res = defaultHandler(error, event);
	return new FastResponse(typeof res.body === "string" ? res.body : JSON.stringify(res.body, null, 2), res);
};
function defaultHandler(error, event) {
	const unhandled = error.unhandled ?? !HTTPError.isError(error);
	const { status = 500, statusText = "" } = unhandled ? {} : error;
	if (status === 404) {
		const url = event.url || new URL(event.req.url);
		const baseURL = "/";
		if (/^\/[^/]/.test(baseURL) && !url.pathname.startsWith(baseURL)) return {
			status: 302,
			headers: new Headers({ location: `${baseURL}${url.pathname.slice(1)}${url.search}` })
		};
	}
	const headers = new Headers(unhandled ? {} : error.headers);
	headers.set("content-type", "application/json; charset=utf-8");
	return {
		status,
		statusText,
		headers,
		body: {
			error: true,
			...unhandled ? {
				status,
				unhandled: true
			} : typeof error.toJSON === "function" ? error.toJSON() : {
				status,
				statusText,
				message: error.message
			}
		}
	};
}
//#endregion
//#region #nitro/virtual/error-handler
var errorHandlers = [errorHandler];
async function error_handler_default(error, event) {
	for (const handler of errorHandlers) try {
		const response = await handler(error, event, { defaultHandler });
		if (response) return response;
	} catch (error) {
		console.error(error);
	}
}
//#endregion
//#region #nitro/virtual/app
function createNitroApp() {
	const captureError = (error, errorCtx) => {
		if (errorCtx?.event) {
			const errors = errorCtx.event.req.context?.nitro?.errors;
			if (errors) errors.push({
				error,
				context: errorCtx
			});
		}
	};
	const h3App = createH3App({ onError(error, event) {
		return error_handler_default(error, event);
	} });
	let appHandler = (req) => {
		req.context ||= {};
		req.context.nitro = req.context.nitro || { errors: [] };
		return h3App.fetch(req);
	};
	return {
		fetch: appHandler,
		h3: h3App,
		hooks: void 0,
		captureError
	};
}
function createH3App(config) {
	const h3App = new H3Core(config);
	h3App["~findRoute"] = (event) => findRoute(event.req.method, event.url.pathname);
	h3App["~getMiddleware"] = (event, route) => {
		const pathname = event.url.pathname;
		const method = event.req.method;
		const middleware = [];
		const routeRules = getRouteRules(method, pathname);
		event.context.routeRules = routeRules?.routeRules;
		if (routeRules?.routeRuleMiddleware.length) middleware.push(...routeRules.routeRuleMiddleware);
		if (route?.data?.middleware?.length) middleware.push(...route.data.middleware);
		return middleware;
	};
	return h3App;
}
//#endregion
//#region node_modules/nitro/dist/runtime/internal/app.mjs
var APP_ID = "default";
function useNitroApp() {
	let instance = useNitroApp._instance;
	if (instance) return instance;
	instance = useNitroApp._instance = createNitroApp();
	globalThis.__nitro__ = globalThis.__nitro__ || {};
	globalThis.__nitro__[APP_ID] = instance;
	return instance;
}
function useNitroHooks() {
	const nitroApp = useNitroApp();
	const hooks = nitroApp.hooks;
	if (hooks) return hooks;
	return nitroApp.hooks = new HookableCore();
}
function getRouteRules(method, pathname) {
	const m = findRouteRules(method, pathname);
	if (!m?.length) return { routeRuleMiddleware: [] };
	const routeRules = {};
	for (const layer of m) for (const rule of layer.data) {
		const currentRule = routeRules[rule.name];
		if (currentRule) {
			if (rule.options === false) {
				delete routeRules[rule.name];
				continue;
			}
			if (typeof currentRule.options === "object" && typeof rule.options === "object") currentRule.options = {
				...currentRule.options,
				...rule.options
			};
			else currentRule.options = rule.options;
			currentRule.route = rule.route;
			currentRule.params = {
				...currentRule.params,
				...layer.params
			};
		} else if (rule.options !== false) routeRules[rule.name] = {
			...rule,
			params: layer.params
		};
	}
	const middleware = [];
	const orderedRules = Object.values(routeRules).sort((a, b) => (a.handler?.order || 0) - (b.handler?.order || 0));
	for (const rule of orderedRules) {
		if (rule.options === false || !rule.handler) continue;
		middleware.push(rule.handler(rule));
	}
	return {
		routeRules,
		routeRuleMiddleware: middleware
	};
}
//#endregion
//#region node_modules/nitro/dist/presets/cloudflare/runtime/_module-handler.mjs
function createHandler(hooks) {
	const nitroApp = useNitroApp();
	const nitroHooks = useNitroHooks();
	return {
		async fetch(request, env, context) {
			globalThis.__env__ = env;
			augmentReq(request, {
				env,
				context
			});
			const ctxExt = {};
			const url = new URL(request.url);
			if (hooks.fetch) {
				const res = await hooks.fetch(request, env, context, url, ctxExt);
				if (res) return res;
			}
			return await nitroApp.fetch(request);
		},
		scheduled(controller, env, context) {
			globalThis.__env__ = env;
			context.waitUntil(nitroHooks.callHook("cloudflare:scheduled", {
				controller,
				env,
				context
			}) || Promise.resolve());
		},
		email(message, env, context) {
			globalThis.__env__ = env;
			context.waitUntil(nitroHooks.callHook("cloudflare:email", {
				message,
				event: message,
				env,
				context
			}) || Promise.resolve());
		},
		queue(batch, env, context) {
			globalThis.__env__ = env;
			context.waitUntil(nitroHooks.callHook("cloudflare:queue", {
				batch,
				event: batch,
				env,
				context
			}) || Promise.resolve());
		},
		tail(traces, env, context) {
			globalThis.__env__ = env;
			context.waitUntil(nitroHooks.callHook("cloudflare:tail", {
				traces,
				env,
				context
			}) || Promise.resolve());
		},
		trace(traces, env, context) {
			globalThis.__env__ = env;
			context.waitUntil(nitroHooks.callHook("cloudflare:trace", {
				traces,
				env,
				context
			}) || Promise.resolve());
		}
	};
}
function augmentReq(cfReq, ctx) {
	const req = cfReq;
	req.ip = cfReq.headers.get("cf-connecting-ip") || void 0;
	req.runtime ??= { name: "cloudflare" };
	req.runtime.cloudflare = {
		...req.runtime.cloudflare,
		...ctx
	};
	req.waitUntil = ctx.context?.waitUntil.bind(ctx.context);
}
//#endregion
//#region node_modules/nitro/dist/presets/cloudflare/runtime/cloudflare-module.mjs
var cloudflare_module_default = createHandler({ fetch(cfRequest, env, context, url) {
	if (env.ASSETS && isPublicAssetURL(url.pathname)) return env.ASSETS.fetch(cfRequest);
} });
//#endregion
export { cloudflare_module_default as default };
