globalThis.__nitro_main__ = import.meta.url;
import { n as HTTPError, r as defineLazyEventHandler, t as H3Core } from "./_libs/h3+rou3+srvx.mjs";
import { t as HookableCore } from "./_libs/hookable.mjs";
import { t as FastResponse } from "./_libs/srvx.mjs";
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
	"/robots.txt": {
		"type": "text/plain; charset=utf-8",
		"etag": "\"17-ZZkCVrbr4BSdjt/K43J0tq8+Qq4\"",
		"mtime": "2026-08-14T12:09:19.588Z",
		"size": 23,
		"path": "../public/robots.txt"
	},
	"/sw.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"4e1-WnMNPCxv9u+sHli23LDWskkYFdk\"",
		"mtime": "2026-08-14T12:09:19.587Z",
		"size": 1249,
		"path": "../public/sw.js"
	},
	"/assets/about-ublLxyQA.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1c1c-mo7Ehoiogkigto8aV/3TiAadkGs\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 7196,
		"path": "../public/assets/about-ublLxyQA.js"
	},
	"/assets/about-heritage-npVZ00Kj.jpg": {
		"type": "image/jpeg",
		"etag": "\"2ada7-HrSVE5Z2evEqJpUnrj8+PscM1ws\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 175527,
		"path": "../public/assets/about-heritage-npVZ00Kj.jpg"
	},
	"/assets/admin-YYhrgWH3.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"4214-v1OfQF12RZ+YoCIGtI8b6Zoa2A4\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 16916,
		"path": "../public/assets/admin-YYhrgWH3.js"
	},
	"/assets/auth-BAaBgP-5.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"dbc-wsOswPtf/D7d9vOdsu+QTkmDE/Y\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 3516,
		"path": "../public/assets/auth-BAaBgP-5.js"
	},
	"/assets/bell-CsYDIIGy.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"117-XNBQ9JahXDUXsaATSmzUU2K721U\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 279,
		"path": "../public/assets/bell-CsYDIIGy.js"
	},
	"/assets/building-2-BrbVlzIF.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"174-qU4QSKg0zwWg41tLj1Jwyjm9Cro\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 372,
		"path": "../public/assets/building-2-BrbVlzIF.js"
	},
	"/favicon.png": {
		"type": "image/png",
		"etag": "\"14fe-HZGvB0SdTJ73lTyX0ZAnEuDNixo\"",
		"mtime": "2026-08-14T12:09:19.587Z",
		"size": 5374,
		"path": "../public/favicon.png"
	},
	"/assets/careers-1H3Ll8YV.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"141e-qydpRwJxoD2XFluSWEiybmAPsuw\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 5150,
		"path": "../public/assets/careers-1H3Ll8YV.js"
	},
	"/assets/careers-hero-B8HOyqJ3.jpg": {
		"type": "image/jpeg",
		"etag": "\"21d9a-li39E5L2Sl6XdG3+W3rvzAvkULo\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 138650,
		"path": "../public/assets/careers-hero-B8HOyqJ3.jpg"
	},
	"/assets/cargo-port-NdEnc6iJ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"37-MeK6scVLxMEy5vajkZCKlSYgA/Y\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 55,
		"path": "../public/assets/cargo-port-NdEnc6iJ.js"
	},
	"/assets/check-C56w725H.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"71-kuAT/f2d4wVaki3vc+1U+0Zhnig\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 113,
		"path": "../public/assets/check-C56w725H.js"
	},
	"/assets/circle-check-BYyaY0IC.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"a7-v02KHLewX3iq/qoszWiXvg6u9q8\"",
		"mtime": "2026-08-14T12:09:18.253Z",
		"size": 167,
		"path": "../public/assets/circle-check-BYyaY0IC.js"
	},
	"/assets/contact-Df-hRXav.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"cad-VJOc8AK7c4S5LpVQxTmNmgAL3D8\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 3245,
		"path": "../public/assets/contact-Df-hRXav.js"
	},
	"/assets/dashboard-Dm8c-fFZ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22e3-J+vUd0JHq79mpz4w4dL4AXPPwf8\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 8931,
		"path": "../public/assets/dashboard-Dm8c-fFZ.js"
	},
	"/assets/cargo-port-CkDtuTJJ.jpg": {
		"type": "image/jpeg",
		"etag": "\"21dcf-HYC9igHS8PQUcgWz4fc12S4cI6A\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 138703,
		"path": "../public/assets/cargo-port-CkDtuTJJ.jpg"
	},
	"/assets/express-handoff-BfZQceII.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c-pC8OJNG9s4+eKEefP0hWK7k/3Yc\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 60,
		"path": "../public/assets/express-handoff-BfZQceII.js"
	},
	"/assets/global-planes-BJ574qCR.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-+obtml3nN1iKQeHv064C1ouy5cU\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 58,
		"path": "../public/assets/global-planes-BJ574qCR.js"
	},
	"/assets/express-handoff-CJxVwHen.jpg": {
		"type": "image/jpeg",
		"etag": "\"1a06c-wVq0w4EzNddselmC+LvtDuBpnXo\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 106604,
		"path": "../public/assets/express-handoff-CJxVwHen.jpg"
	},
	"/assets/global-planes-Dt22eXIg.jpg": {
		"type": "image/jpeg",
		"etag": "\"65fd-dyR4aR9nqj1x/EyTM+aPuHV0l2w\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 26109,
		"path": "../public/assets/global-planes-Dt22eXIg.jpg"
	},
	"/assets/help-C14Lkc_o.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"d52-5Qf+cWlMQHGbW+EhjHjjc7a2TB4\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 3410,
		"path": "../public/assets/help-C14Lkc_o.js"
	},
	"/assets/image-gallery-BCRNaEBT.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"10e8-rSTSLBEeOYXSh8f4385TZLHHT3c\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 4328,
		"path": "../public/assets/image-gallery-BCRNaEBT.js"
	},
	"/assets/hero-courier-Br-4tkiW.jpg": {
		"type": "image/jpeg",
		"etag": "\"22807-OFNoLpAuy7ETKCjaxD6sxf2nweE\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 141319,
		"path": "../public/assets/hero-courier-Br-4tkiW.jpg"
	},
	"/assets/index-PH8lq5iy.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"9c6e6-/3wj5MMPh1RRPkdanfkDHe6UTBw\"",
		"mtime": "2026-08-14T12:09:18.248Z",
		"size": 640742,
		"path": "../public/assets/index-PH8lq5iy.js"
	},
	"/assets/innovation-data-0yhncJlr.jpg": {
		"type": "image/jpeg",
		"etag": "\"163e7-Pa0RMC32PdjprWbjPD8np49xo38\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 91111,
		"path": "../public/assets/innovation-data-0yhncJlr.jpg"
	},
	"/assets/jsx-runtime-BkSabwWG.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c1-VkW1xFbt56H2FC99QIi6PTzaFIo\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 961,
		"path": "../public/assets/jsx-runtime-BkSabwWG.js"
	},
	"/assets/innovation-data-C6cYsrYg.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c-haYuCHN+5t61a0RAoWZwrGq71nA\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 60,
		"path": "../public/assets/innovation-data-C6cYsrYg.js"
	},
	"/assets/legal-D0IJb2sF.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"77e-aA7620bI9gXJ18QXEZ5mCNQieE0\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 1918,
		"path": "../public/assets/legal-D0IJb2sF.js"
	},
	"/assets/leaf-Cf0MazWB.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"fe-dMEa41/NLCpsP+ard7Ou6dfXx6w\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 254,
		"path": "../public/assets/leaf-Cf0MazWB.js"
	},
	"/assets/link-C944SZWc.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3e03-dc5tIfwnEqyA4KoRaU07uxFVs/o\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 15875,
		"path": "../public/assets/link-C944SZWc.js"
	},
	"/assets/map-pin-D_1yGWPZ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f8-NgUfC50Wk0ebRnYbiof2LtiRXbY\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 248,
		"path": "../public/assets/map-pin-D_1yGWPZ.js"
	},
	"/assets/news-97Hc6fTM.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"e29-rovRKBlsONeUN91pWElA4uV5uFY\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 3625,
		"path": "../public/assets/news-97Hc6fTM.js"
	},
	"/assets/news-hero-CNtVzuot.jpg": {
		"type": "image/jpeg",
		"etag": "\"145aa-ga3sNJBG777KJrXR882OkhM9xVI\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 83370,
		"path": "../public/assets/news-hero-CNtVzuot.jpg"
	},
	"/assets/newspaper-Bngq5c3O.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"14d-uBxJaBf7qwsr+iip0XXc1Kjt314\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 333,
		"path": "../public/assets/newspaper-Bngq5c3O.js"
	},
	"/assets/not-found-DIgawKw1.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"37-RTB6YH5iXRKeXz1Sn6ZQ+vS0lnc\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 55,
		"path": "../public/assets/not-found-DIgawKw1.js"
	},
	"/assets/onboarding-DorBFEiI.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1b03-ZyaRnG8FqfGIflS0FE/0nI8O8ik\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 6915,
		"path": "../public/assets/onboarding-DorBFEiI.js"
	},
	"/assets/package-CW97TfGS.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"169-WhRhuQ4HLu7u/K0u4BQaHJgRDss\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 361,
		"path": "../public/assets/package-CW97TfGS.js"
	},
	"/assets/privacy-Bei1l4Aa.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"78e-16FECInF2yLCWsX2sETdWrIyRVM\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 1934,
		"path": "../public/assets/privacy-Bei1l4Aa.js"
	},
	"/assets/quote-BOXO6WgF.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22c1-7Plrba4D2t8shKfXotLINCNnINk\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 8897,
		"path": "../public/assets/quote-BOXO6WgF.js"
	},
	"/assets/pricing-BiZvZPFH.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f1a-rdXAEtxCorH4IA9K99zwzBjsF2s\"",
		"mtime": "2026-08-14T12:09:18.254Z",
		"size": 3866,
		"path": "../public/assets/pricing-BiZvZPFH.js"
	},
	"/assets/react-DHmoMYoq.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1d67-nufvvndhXtiz6VWh8XcPEWVqP1g\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 7527,
		"path": "../public/assets/react-DHmoMYoq.js"
	},
	"/assets/redirect-Dhm19zUi.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1f4-ePZWCXP5uehkmkGMkMl5xDch+/Y\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 500,
		"path": "../public/assets/redirect-Dhm19zUi.js"
	},
	"/assets/routes-C0llczd7.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"5e9c-urJ1yWjXYP2W+K4Hc9kzZ/EXOqY\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 24220,
		"path": "../public/assets/routes-C0llczd7.js"
	},
	"/assets/route-5-YXzva-.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"232-30cNbsjvsVmLeemYn2e4+haY4c8\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 562,
		"path": "../public/assets/route-5-YXzva-.js"
	},
	"/assets/service-ecommerce-fV2g4Ikv.jpg": {
		"type": "image/jpeg",
		"etag": "\"348e-GagV+DBOyeHRIGbA5gDS7/O8lCA\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 13454,
		"path": "../public/assets/service-ecommerce-fV2g4Ikv.jpg"
	},
	"/assets/service-industrial-BjpKOjr3.jpg": {
		"type": "image/jpeg",
		"etag": "\"427c-tpNXglCfz64xCGM54LPDtAsOVrU\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 17020,
		"path": "../public/assets/service-industrial-BjpKOjr3.jpg"
	},
	"/assets/service-ocean-air-DYb89scQ.jpg": {
		"type": "image/jpeg",
		"etag": "\"4f5d-q3cCWmE2KGjMGHHVB9TGDxT6YAk\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 20317,
		"path": "../public/assets/service-ocean-air-DYb89scQ.jpg"
	},
	"/assets/service-supply-chain-B-tn33fr.jpg": {
		"type": "image/jpeg",
		"etag": "\"47ec-HwL6/PBX3mvxR+HUwtaAIGmka8s\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 18412,
		"path": "../public/assets/service-supply-chain-B-tn33fr.jpg"
	},
	"/assets/services-BwORFiy1.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"2318-UmFO8tn8Zqiv3mS7+VaLj53vA7o\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 8984,
		"path": "../public/assets/services-BwORFiy1.js"
	},
	"/assets/services-hero-DdzTuCum.jpg": {
		"type": "image/jpeg",
		"etag": "\"30a3d-fG+Pj3kXWKYq57nm5FzwO+K4JWo\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 199229,
		"path": "../public/assets/services-hero-DdzTuCum.jpg"
	},
	"/assets/styles-DrBReH8Q.css": {
		"type": "text/css; charset=utf-8",
		"etag": "\"16f7d-WApfoEuXHu10xkWzkNL31YW9wAw\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 94077,
		"path": "../public/assets/styles-DrBReH8Q.css"
	},
	"/assets/sustainability-hero-DaYcT8DZ.jpg": {
		"type": "image/jpeg",
		"etag": "\"38bf4-3neOvK9i0rgNozTFNfgdegw56l8\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 232436,
		"path": "../public/assets/sustainability-hero-DaYcT8DZ.jpg"
	},
	"/assets/sustainability-qslEYLWJ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1149-j22AEWJPalhVqEFr0Cm6lET5OG8\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 4425,
		"path": "../public/assets/sustainability-qslEYLWJ.js"
	},
	"/assets/sustainability-van-D-5ZfF9m.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3f-WdhRlgVJpAAaBvx3rMTHM1ARzO8\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 63,
		"path": "../public/assets/sustainability-van-D-5ZfF9m.js"
	},
	"/assets/sustainability-van-DjoVtKcX.jpg": {
		"type": "image/jpeg",
		"etag": "\"109b9-tablrN1ewiWUXmM56k1Mg15Hs30\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 68025,
		"path": "../public/assets/sustainability-van-DjoVtKcX.jpg"
	},
	"/assets/team-portrait-BbSWKlwB.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-fWD4vXIgwmS7czQqgkhq6oKISIc\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 58,
		"path": "../public/assets/team-portrait-BbSWKlwB.js"
	},
	"/assets/team-portrait-ClIXUWbH.jpg": {
		"type": "image/jpeg",
		"etag": "\"3be82-Ude0q9RheM+hDI+m5PTXg9A+p9M\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 245378,
		"path": "../public/assets/team-portrait-ClIXUWbH.jpg"
	},
	"/assets/terms-D0e-HJBX.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"64e-5vIpyjbhOUGvLZPo8iby5z/jM9w\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 1614,
		"path": "../public/assets/terms-D0e-HJBX.js"
	},
	"/assets/testimonial-customer-Bdy82DCA.jpg": {
		"type": "image/jpeg",
		"etag": "\"1aaed-cBx0UHSYqhZz7wKKILrmpwSkTVg\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 109293,
		"path": "../public/assets/testimonial-customer-Bdy82DCA.jpg"
	},
	"/assets/track-ROvELfaq.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"16ec-jakeNwQr4G20tkJNBzpEAhlwnVo\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 5868,
		"path": "../public/assets/track-ROvELfaq.js"
	},
	"/assets/useMatch-FB94qkx7.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"277-MUBeHk//e7Q9PDGn4ia6hUARd7w\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 631,
		"path": "../public/assets/useMatch-FB94qkx7.js"
	},
	"/assets/testimonial-customer-BCVW9qyW.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"41-InTFLx5ZgIkWTyNqfy4ENhRDusw\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 65,
		"path": "../public/assets/testimonial-customer-BCVW9qyW.js"
	},
	"/assets/useStore-BqDt8rA_.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"153e-0LljSK9bcHL/E+3V+jeBcctrpF4\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 5438,
		"path": "../public/assets/useStore-BqDt8rA_.js"
	},
	"/assets/users-Cga_2E3r.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"127-blTJb3xirAw+Zos7WY/rndE/hLg\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 295,
		"path": "../public/assets/users-Cga_2E3r.js"
	},
	"/assets/warehouse-3DWQenEW.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"16c-IUeDvsmlqtIh4a5zXqGjn1byUlM\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 364,
		"path": "../public/assets/warehouse-3DWQenEW.js"
	},
	"/assets/warehouse-ops-CQcMgWIS.jpg": {
		"type": "image/jpeg",
		"etag": "\"24817-Lh/Z6VpVl9+yfW8D9MEUPVbXQsE\"",
		"mtime": "2026-08-14T12:09:18.257Z",
		"size": 149527,
		"path": "../public/assets/warehouse-ops-CQcMgWIS.jpg"
	},
	"/assets/useRouter-BFssjxle.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22a-ZSuZOaeNo7zgh8hE9xL8p2cWRjQ\"",
		"mtime": "2026-08-14T12:09:18.255Z",
		"size": 554,
		"path": "../public/assets/useRouter-BFssjxle.js"
	},
	"/assets/warehouse-ops-CWcONLV9.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-DLcN4hGtNxCdGFLoSalUxT/0RiU\"",
		"mtime": "2026-08-14T12:09:18.256Z",
		"size": 58,
		"path": "../public/assets/warehouse-ops-CWcONLV9.js"
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
//#region node_modules/.pnpm/nitro@3.0.260603-beta_chokidar@5.0.0_jiti@2.7.0_vite@8.2.1_@types+node@22.20.1_jiti@2.7.0_/node_modules/nitro/dist/runtime/internal/route-rules.mjs
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
var _lazy_jnhdQv = defineLazyEventHandler(() => import("./_chunks/ssr-renderer.mjs"));
var findRoute = /* @__PURE__ */ (() => {
	const data = {
		route: "/**",
		handler: _lazy_jnhdQv
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
//#region node_modules/.pnpm/nitro@3.0.260603-beta_chokidar@5.0.0_jiti@2.7.0_vite@8.2.1_@types+node@22.20.1_jiti@2.7.0_/node_modules/nitro/dist/runtime/internal/error/prod.mjs
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
//#region node_modules/.pnpm/nitro@3.0.260603-beta_chokidar@5.0.0_jiti@2.7.0_vite@8.2.1_@types+node@22.20.1_jiti@2.7.0_/node_modules/nitro/dist/runtime/internal/app.mjs
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
//#region node_modules/.pnpm/nitro@3.0.260603-beta_chokidar@5.0.0_jiti@2.7.0_vite@8.2.1_@types+node@22.20.1_jiti@2.7.0_/node_modules/nitro/dist/presets/cloudflare/runtime/_module-handler.mjs
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
//#region node_modules/.pnpm/nitro@3.0.260603-beta_chokidar@5.0.0_jiti@2.7.0_vite@8.2.1_@types+node@22.20.1_jiti@2.7.0_/node_modules/nitro/dist/presets/cloudflare/runtime/cloudflare-module.mjs
var cloudflare_module_default = createHandler({ fetch(cfRequest, env, context, url) {
	if (env.ASSETS && isPublicAssetURL(url.pathname)) return env.ASSETS.fetch(cfRequest);
} });
//#endregion
export { cloudflare_module_default as default };
