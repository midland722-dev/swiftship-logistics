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
	"/favicon.png": {
		"type": "image/png",
		"etag": "\"14fe-HZGvB0SdTJ73lTyX0ZAnEuDNixo\"",
		"mtime": "2026-08-14T12:41:10.124Z",
		"size": 5374,
		"path": "../public/favicon.png"
	},
	"/robots.txt": {
		"type": "text/plain; charset=utf-8",
		"etag": "\"17-ZZkCVrbr4BSdjt/K43J0tq8+Qq4\"",
		"mtime": "2026-08-14T12:41:10.124Z",
		"size": 23,
		"path": "../public/robots.txt"
	},
	"/sw.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"4e1-WnMNPCxv9u+sHli23LDWskkYFdk\"",
		"mtime": "2026-08-14T12:41:10.124Z",
		"size": 1249,
		"path": "../public/sw.js"
	},
	"/assets/about-Dlb0aDyH.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1c1c-HiSghibKO6UkGCCa/agXhaSXx1o\"",
		"mtime": "2026-08-14T12:41:08.631Z",
		"size": 7196,
		"path": "../public/assets/about-Dlb0aDyH.js"
	},
	"/assets/about-heritage-npVZ00Kj.jpg": {
		"type": "image/jpeg",
		"etag": "\"2ada7-HrSVE5Z2evEqJpUnrj8+PscM1ws\"",
		"mtime": "2026-08-14T12:41:08.642Z",
		"size": 175527,
		"path": "../public/assets/about-heritage-npVZ00Kj.jpg"
	},
	"/assets/admin-LXFAUO9C.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"4214-ZnwuWhyS5le4ZP7KNsWPNmmzJA0\"",
		"mtime": "2026-08-14T12:41:08.631Z",
		"size": 16916,
		"path": "../public/assets/admin-LXFAUO9C.js"
	},
	"/assets/ascl-logo-BOcyvmZ-.png": {
		"type": "image/png",
		"etag": "\"2297-qppfsTa147BF1NuwQTPgmV/zGtU\"",
		"mtime": "2026-08-14T12:41:08.642Z",
		"size": 8855,
		"path": "../public/assets/ascl-logo-BOcyvmZ-.png"
	},
	"/assets/auth-B1OuWfbD.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"dbc-JVlSa2mNgvgx1ZO8fzL6G4CVN8Q\"",
		"mtime": "2026-08-14T12:41:08.631Z",
		"size": 3516,
		"path": "../public/assets/auth-B1OuWfbD.js"
	},
	"/assets/bell-BJp5TyD-.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"117-uR9UI+hCBL3UUMKO7WJJ7tYeXKo\"",
		"mtime": "2026-08-14T12:41:08.632Z",
		"size": 279,
		"path": "../public/assets/bell-BJp5TyD-.js"
	},
	"/assets/building-2-Mlyftc-f.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"174-RD5KYBPV6olQwkelPrHmag1+CIE\"",
		"mtime": "2026-08-14T12:41:08.632Z",
		"size": 372,
		"path": "../public/assets/building-2-Mlyftc-f.js"
	},
	"/assets/careers-hero-B8HOyqJ3.jpg": {
		"type": "image/jpeg",
		"etag": "\"21d9a-li39E5L2Sl6XdG3+W3rvzAvkULo\"",
		"mtime": "2026-08-14T12:41:08.643Z",
		"size": 138650,
		"path": "../public/assets/careers-hero-B8HOyqJ3.jpg"
	},
	"/assets/cargo-port-NdEnc6iJ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"37-MeK6scVLxMEy5vajkZCKlSYgA/Y\"",
		"mtime": "2026-08-14T12:41:08.632Z",
		"size": 55,
		"path": "../public/assets/cargo-port-NdEnc6iJ.js"
	},
	"/assets/cargo-port-CkDtuTJJ.jpg": {
		"type": "image/jpeg",
		"etag": "\"21dcf-HYC9igHS8PQUcgWz4fc12S4cI6A\"",
		"mtime": "2026-08-14T12:41:08.643Z",
		"size": 138703,
		"path": "../public/assets/cargo-port-CkDtuTJJ.jpg"
	},
	"/assets/check-DJeQNFij.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"71-EPJy2VlDQx+IfQrpib632rScnGI\"",
		"mtime": "2026-08-14T12:41:08.633Z",
		"size": 113,
		"path": "../public/assets/check-DJeQNFij.js"
	},
	"/assets/circle-check-DiUqBdhf.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"a7-gYINqjiiXbHlVbqfIXLh25cC29U\"",
		"mtime": "2026-08-14T12:41:08.633Z",
		"size": 167,
		"path": "../public/assets/circle-check-DiUqBdhf.js"
	},
	"/assets/contact-CcmZkhQb.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"cad-+R+XqkwrnftSXGXB5f/gpJrJkNY\"",
		"mtime": "2026-08-14T12:41:08.633Z",
		"size": 3245,
		"path": "../public/assets/contact-CcmZkhQb.js"
	},
	"/assets/careers-uS4weiuD.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"141e-0I67TrZ4jxihGbW7QGSCgfOXNqY\"",
		"mtime": "2026-08-14T12:41:08.632Z",
		"size": 5150,
		"path": "../public/assets/careers-uS4weiuD.js"
	},
	"/assets/dashboard-Bi5Qk8_c.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22e3-r95SbNt6BcF2H/WMogIAlTW3yj4\"",
		"mtime": "2026-08-14T12:41:08.633Z",
		"size": 8931,
		"path": "../public/assets/dashboard-Bi5Qk8_c.js"
	},
	"/assets/global-planes-BJ574qCR.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-+obtml3nN1iKQeHv064C1ouy5cU\"",
		"mtime": "2026-08-14T12:41:08.634Z",
		"size": 58,
		"path": "../public/assets/global-planes-BJ574qCR.js"
	},
	"/assets/global-planes-Dt22eXIg.jpg": {
		"type": "image/jpeg",
		"etag": "\"65fd-dyR4aR9nqj1x/EyTM+aPuHV0l2w\"",
		"mtime": "2026-08-14T12:41:08.644Z",
		"size": 26109,
		"path": "../public/assets/global-planes-Dt22eXIg.jpg"
	},
	"/assets/help-tULA-SDZ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"d52-+IBTNvHjNfW2ncgX/2qRDrKH2XA\"",
		"mtime": "2026-08-14T12:41:08.634Z",
		"size": 3410,
		"path": "../public/assets/help-tULA-SDZ.js"
	},
	"/assets/express-handoff-CJxVwHen.jpg": {
		"type": "image/jpeg",
		"etag": "\"1a06c-wVq0w4EzNddselmC+LvtDuBpnXo\"",
		"mtime": "2026-08-14T12:41:08.643Z",
		"size": 106604,
		"path": "../public/assets/express-handoff-CJxVwHen.jpg"
	},
	"/assets/hero-courier-Br-4tkiW.jpg": {
		"type": "image/jpeg",
		"etag": "\"22807-OFNoLpAuy7ETKCjaxD6sxf2nweE\"",
		"mtime": "2026-08-14T12:41:08.644Z",
		"size": 141319,
		"path": "../public/assets/hero-courier-Br-4tkiW.jpg"
	},
	"/assets/image-gallery-DYduv5IN.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"10e8-ZWrUPRPDuwAyT+WI9FBp14uQzXk\"",
		"mtime": "2026-08-14T12:41:08.634Z",
		"size": 4328,
		"path": "../public/assets/image-gallery-DYduv5IN.js"
	},
	"/assets/express-handoff-BfZQceII.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c-pC8OJNG9s4+eKEefP0hWK7k/3Yc\"",
		"mtime": "2026-08-14T12:41:08.633Z",
		"size": 60,
		"path": "../public/assets/express-handoff-BfZQceII.js"
	},
	"/assets/innovation-data-0yhncJlr.jpg": {
		"type": "image/jpeg",
		"etag": "\"163e7-Pa0RMC32PdjprWbjPD8np49xo38\"",
		"mtime": "2026-08-14T12:41:08.644Z",
		"size": 91111,
		"path": "../public/assets/innovation-data-0yhncJlr.jpg"
	},
	"/assets/innovation-data-C6cYsrYg.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c-haYuCHN+5t61a0RAoWZwrGq71nA\"",
		"mtime": "2026-08-14T12:41:08.634Z",
		"size": 60,
		"path": "../public/assets/innovation-data-C6cYsrYg.js"
	},
	"/assets/jsx-runtime-BkSabwWG.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c1-VkW1xFbt56H2FC99QIi6PTzaFIo\"",
		"mtime": "2026-08-14T12:41:08.635Z",
		"size": 961,
		"path": "../public/assets/jsx-runtime-BkSabwWG.js"
	},
	"/assets/leaf-Dm1wQANm.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"fe-oo/Gz4/FLEz/YXb+L0EEPQ5wR8E\"",
		"mtime": "2026-08-14T12:41:08.635Z",
		"size": 254,
		"path": "../public/assets/leaf-Dm1wQANm.js"
	},
	"/assets/legal-D0IJb2sF.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"77e-aA7620bI9gXJ18QXEZ5mCNQieE0\"",
		"mtime": "2026-08-14T12:41:08.635Z",
		"size": 1918,
		"path": "../public/assets/legal-D0IJb2sF.js"
	},
	"/assets/link-C944SZWc.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3e03-dc5tIfwnEqyA4KoRaU07uxFVs/o\"",
		"mtime": "2026-08-14T12:41:08.635Z",
		"size": 15875,
		"path": "../public/assets/link-C944SZWc.js"
	},
	"/assets/map-pin-CRhcLHn7.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f8-nTAKtd0bb1U3lidYUlaHlyLeTnY\"",
		"mtime": "2026-08-14T12:41:08.636Z",
		"size": 248,
		"path": "../public/assets/map-pin-CRhcLHn7.js"
	},
	"/assets/news-DuN5Fbot.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"e29-IUSBmais0kBIBbXk0ZWYKD71A94\"",
		"mtime": "2026-08-14T12:41:08.636Z",
		"size": 3625,
		"path": "../public/assets/news-DuN5Fbot.js"
	},
	"/assets/news-hero-CNtVzuot.jpg": {
		"type": "image/jpeg",
		"etag": "\"145aa-ga3sNJBG777KJrXR882OkhM9xVI\"",
		"mtime": "2026-08-14T12:41:08.644Z",
		"size": 83370,
		"path": "../public/assets/news-hero-CNtVzuot.jpg"
	},
	"/assets/newspaper-CW7QAU2-.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"14d-K+TSl+BzyeO4Tlj7aSKwJzJgAnQ\"",
		"mtime": "2026-08-14T12:41:08.636Z",
		"size": 333,
		"path": "../public/assets/newspaper-CW7QAU2-.js"
	},
	"/assets/not-found-DIgawKw1.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"37-RTB6YH5iXRKeXz1Sn6ZQ+vS0lnc\"",
		"mtime": "2026-08-14T12:41:08.636Z",
		"size": 55,
		"path": "../public/assets/not-found-DIgawKw1.js"
	},
	"/assets/onboarding-DMnRPFSE.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1b03-BpB5pSeuzxqbiw5ARpU56qjX0pg\"",
		"mtime": "2026-08-14T12:41:08.636Z",
		"size": 6915,
		"path": "../public/assets/onboarding-DMnRPFSE.js"
	},
	"/assets/pricing-CVonQMy5.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f1a-UYD80IK268Iqef0VoTZOKqKDQu0\"",
		"mtime": "2026-08-14T12:41:08.637Z",
		"size": 3866,
		"path": "../public/assets/pricing-CVonQMy5.js"
	},
	"/assets/privacy-Bei1l4Aa.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"78e-16FECInF2yLCWsX2sETdWrIyRVM\"",
		"mtime": "2026-08-14T12:41:08.637Z",
		"size": 1934,
		"path": "../public/assets/privacy-Bei1l4Aa.js"
	},
	"/assets/quote-n-5YOXyf.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22c1-smPErBL3zUJzrUUujU42GsJ41dc\"",
		"mtime": "2026-08-14T12:41:08.637Z",
		"size": 8897,
		"path": "../public/assets/quote-n-5YOXyf.js"
	},
	"/assets/package-C9ajYJld.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"169-e7YZmYIQHe821UE2bhIl7XYEOkA\"",
		"mtime": "2026-08-14T12:41:08.637Z",
		"size": 361,
		"path": "../public/assets/package-C9ajYJld.js"
	},
	"/assets/react-DHmoMYoq.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1d67-nufvvndhXtiz6VWh8XcPEWVqP1g\"",
		"mtime": "2026-08-14T12:41:08.638Z",
		"size": 7527,
		"path": "../public/assets/react-DHmoMYoq.js"
	},
	"/assets/route-Dc2BkFYL.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"232-AELF5ZHP7jj0MwX+ySVrcBsQ8kc\"",
		"mtime": "2026-08-14T12:41:08.638Z",
		"size": 562,
		"path": "../public/assets/route-Dc2BkFYL.js"
	},
	"/assets/redirect-Dhm19zUi.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1f4-ePZWCXP5uehkmkGMkMl5xDch+/Y\"",
		"mtime": "2026-08-14T12:41:08.638Z",
		"size": 500,
		"path": "../public/assets/redirect-Dhm19zUi.js"
	},
	"/assets/routes-mQJfKFS_.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"5eb6-tSr3JU5xiCRDWI2IFPmlEtL9wFE\"",
		"mtime": "2026-08-14T12:41:08.639Z",
		"size": 24246,
		"path": "../public/assets/routes-mQJfKFS_.js"
	},
	"/assets/service-ecommerce-fV2g4Ikv.jpg": {
		"type": "image/jpeg",
		"etag": "\"348e-GagV+DBOyeHRIGbA5gDS7/O8lCA\"",
		"mtime": "2026-08-14T12:41:08.645Z",
		"size": 13454,
		"path": "../public/assets/service-ecommerce-fV2g4Ikv.jpg"
	},
	"/assets/service-industrial-BjpKOjr3.jpg": {
		"type": "image/jpeg",
		"etag": "\"427c-tpNXglCfz64xCGM54LPDtAsOVrU\"",
		"mtime": "2026-08-14T12:41:08.645Z",
		"size": 17020,
		"path": "../public/assets/service-industrial-BjpKOjr3.jpg"
	},
	"/assets/service-ocean-air-DYb89scQ.jpg": {
		"type": "image/jpeg",
		"etag": "\"4f5d-q3cCWmE2KGjMGHHVB9TGDxT6YAk\"",
		"mtime": "2026-08-14T12:41:08.645Z",
		"size": 20317,
		"path": "../public/assets/service-ocean-air-DYb89scQ.jpg"
	},
	"/assets/service-supply-chain-B-tn33fr.jpg": {
		"type": "image/jpeg",
		"etag": "\"47ec-HwL6/PBX3mvxR+HUwtaAIGmka8s\"",
		"mtime": "2026-08-14T12:41:08.645Z",
		"size": 18412,
		"path": "../public/assets/service-supply-chain-B-tn33fr.jpg"
	},
	"/assets/index-B2PirDDc.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"9c570-vRh5YyIci0rH8MIYfOnJQ0geTtI\"",
		"mtime": "2026-08-14T12:41:08.628Z",
		"size": 640368,
		"path": "../public/assets/index-B2PirDDc.js"
	},
	"/assets/services-hero-DdzTuCum.jpg": {
		"type": "image/jpeg",
		"etag": "\"30a3d-fG+Pj3kXWKYq57nm5FzwO+K4JWo\"",
		"mtime": "2026-08-14T12:41:08.646Z",
		"size": 199229,
		"path": "../public/assets/services-hero-DdzTuCum.jpg"
	},
	"/assets/services-ESnrCaP9.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"2318-z0k1Dszn5LpSqtFK9yCEZMI4sOw\"",
		"mtime": "2026-08-14T12:41:08.639Z",
		"size": 8984,
		"path": "../public/assets/services-ESnrCaP9.js"
	},
	"/assets/styles-DrBReH8Q.css": {
		"type": "text/css; charset=utf-8",
		"etag": "\"16f7d-WApfoEuXHu10xkWzkNL31YW9wAw\"",
		"mtime": "2026-08-14T12:41:08.646Z",
		"size": 94077,
		"path": "../public/assets/styles-DrBReH8Q.css"
	},
	"/assets/sustainability-hero-DaYcT8DZ.jpg": {
		"type": "image/jpeg",
		"etag": "\"38bf4-3neOvK9i0rgNozTFNfgdegw56l8\"",
		"mtime": "2026-08-14T12:41:08.646Z",
		"size": 232436,
		"path": "../public/assets/sustainability-hero-DaYcT8DZ.jpg"
	},
	"/assets/sustainability-pVmo83RY.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1149-3o8ET894ZZWp+g4AqppW7nbl+5s\"",
		"mtime": "2026-08-14T12:41:08.639Z",
		"size": 4425,
		"path": "../public/assets/sustainability-pVmo83RY.js"
	},
	"/assets/sustainability-van-D-5ZfF9m.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3f-WdhRlgVJpAAaBvx3rMTHM1ARzO8\"",
		"mtime": "2026-08-14T12:41:08.639Z",
		"size": 63,
		"path": "../public/assets/sustainability-van-D-5ZfF9m.js"
	},
	"/assets/sustainability-van-DjoVtKcX.jpg": {
		"type": "image/jpeg",
		"etag": "\"109b9-tablrN1ewiWUXmM56k1Mg15Hs30\"",
		"mtime": "2026-08-14T12:41:08.647Z",
		"size": 68025,
		"path": "../public/assets/sustainability-van-DjoVtKcX.jpg"
	},
	"/assets/team-portrait-BbSWKlwB.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-fWD4vXIgwmS7czQqgkhq6oKISIc\"",
		"mtime": "2026-08-14T12:41:08.640Z",
		"size": 58,
		"path": "../public/assets/team-portrait-BbSWKlwB.js"
	},
	"/assets/team-portrait-ClIXUWbH.jpg": {
		"type": "image/jpeg",
		"etag": "\"3be82-Ude0q9RheM+hDI+m5PTXg9A+p9M\"",
		"mtime": "2026-08-14T12:41:08.647Z",
		"size": 245378,
		"path": "../public/assets/team-portrait-ClIXUWbH.jpg"
	},
	"/assets/terms-D0e-HJBX.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"64e-5vIpyjbhOUGvLZPo8iby5z/jM9w\"",
		"mtime": "2026-08-14T12:41:08.640Z",
		"size": 1614,
		"path": "../public/assets/terms-D0e-HJBX.js"
	},
	"/assets/testimonial-customer-BCVW9qyW.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"41-InTFLx5ZgIkWTyNqfy4ENhRDusw\"",
		"mtime": "2026-08-14T12:41:08.640Z",
		"size": 65,
		"path": "../public/assets/testimonial-customer-BCVW9qyW.js"
	},
	"/assets/testimonial-customer-Bdy82DCA.jpg": {
		"type": "image/jpeg",
		"etag": "\"1aaed-cBx0UHSYqhZz7wKKILrmpwSkTVg\"",
		"mtime": "2026-08-14T12:41:08.647Z",
		"size": 109293,
		"path": "../public/assets/testimonial-customer-Bdy82DCA.jpg"
	},
	"/assets/useMatch-FB94qkx7.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"277-MUBeHk//e7Q9PDGn4ia6hUARd7w\"",
		"mtime": "2026-08-14T12:41:08.641Z",
		"size": 631,
		"path": "../public/assets/useMatch-FB94qkx7.js"
	},
	"/assets/useRouter-BFssjxle.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22a-ZSuZOaeNo7zgh8hE9xL8p2cWRjQ\"",
		"mtime": "2026-08-14T12:41:08.641Z",
		"size": 554,
		"path": "../public/assets/useRouter-BFssjxle.js"
	},
	"/assets/useStore-BqDt8rA_.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"153e-0LljSK9bcHL/E+3V+jeBcctrpF4\"",
		"mtime": "2026-08-14T12:41:08.641Z",
		"size": 5438,
		"path": "../public/assets/useStore-BqDt8rA_.js"
	},
	"/assets/track-Blxt1_lD.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"16ec-cfCKHTAYAURUlr2dWDq855kdTUY\"",
		"mtime": "2026-08-14T12:41:08.640Z",
		"size": 5868,
		"path": "../public/assets/track-Blxt1_lD.js"
	},
	"/assets/users-CDyxwiWu.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"127-Jxmx1fkIcQRE35MFMhzf6Ce0Mec\"",
		"mtime": "2026-08-14T12:41:08.641Z",
		"size": 295,
		"path": "../public/assets/users-CDyxwiWu.js"
	},
	"/assets/warehouse-DKTAZ6mK.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"16c-T72jH0QODy8R/P96FW0bZ8kOaiY\"",
		"mtime": "2026-08-14T12:41:08.642Z",
		"size": 364,
		"path": "../public/assets/warehouse-DKTAZ6mK.js"
	},
	"/assets/warehouse-ops-CQcMgWIS.jpg": {
		"type": "image/jpeg",
		"etag": "\"24817-Lh/Z6VpVl9+yfW8D9MEUPVbXQsE\"",
		"mtime": "2026-08-14T12:41:08.648Z",
		"size": 149527,
		"path": "../public/assets/warehouse-ops-CQcMgWIS.jpg"
	},
	"/assets/warehouse-ops-CWcONLV9.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-DLcN4hGtNxCdGFLoSalUxT/0RiU\"",
		"mtime": "2026-08-14T12:41:08.642Z",
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
