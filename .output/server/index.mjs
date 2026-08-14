globalThis.__nitro_main__ = import.meta.url;
import { n as HTTPError, r as defineLazyEventHandler, t as H3Core } from "./_libs/h3+rou3+srvx.mjs";
import { t as HookableCore } from "./_libs/hookable.mjs";
import { r as FastResponse } from "./_libs/h3-v2+rou3+srvx.mjs";
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
		"mtime": "2026-08-14T15:34:11.819Z",
		"size": 23,
		"path": "../public/robots.txt"
	},
	"/favicon.png": {
		"type": "image/png",
		"etag": "\"14fe-HZGvB0SdTJ73lTyX0ZAnEuDNixo\"",
		"mtime": "2026-08-14T15:34:11.818Z",
		"size": 5374,
		"path": "../public/favicon.png"
	},
	"/assets/about-C9v9hUDA.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1bfc-Fo8UayexooDHCz0rJzpFsXyaYWU\"",
		"mtime": "2026-08-14T15:34:10.374Z",
		"size": 7164,
		"path": "../public/assets/about-C9v9hUDA.js"
	},
	"/sw.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"4e1-WnMNPCxv9u+sHli23LDWskkYFdk\"",
		"mtime": "2026-08-14T15:34:11.818Z",
		"size": 1249,
		"path": "../public/sw.js"
	},
	"/assets/about-heritage-npVZ00Kj.jpg": {
		"type": "image/jpeg",
		"etag": "\"2ada7-HrSVE5Z2evEqJpUnrj8+PscM1ws\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 175527,
		"path": "../public/assets/about-heritage-npVZ00Kj.jpg"
	},
	"/assets/admin-Bd-Pmf08.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"48b0-hYBaVKSPJNnUqwSkKI+YbgX98nw\"",
		"mtime": "2026-08-14T15:34:10.374Z",
		"size": 18608,
		"path": "../public/assets/admin-Bd-Pmf08.js"
	},
	"/assets/ascl-logo-D1R6kXmd.png": {
		"type": "image/png",
		"etag": "\"1b17a-Xdc9HG6oLi9UYaDWM172Eey36j4\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 110970,
		"path": "../public/assets/ascl-logo-D1R6kXmd.png"
	},
	"/assets/bell-BereWhFM.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"117-cf/cBJy9dxN9a3Hvh5LKlN3nOWw\"",
		"mtime": "2026-08-14T15:34:10.374Z",
		"size": 279,
		"path": "../public/assets/bell-BereWhFM.js"
	},
	"/assets/building-2-CkQgOJYC.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"174-WafP+N128hoT8eQhgBAkQsMSkYA\"",
		"mtime": "2026-08-14T15:34:10.374Z",
		"size": 372,
		"path": "../public/assets/building-2-CkQgOJYC.js"
	},
	"/assets/careers-DkOMkuEn.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"13fe-5qRypIklZ6aez9/k/EoCRL2glNg\"",
		"mtime": "2026-08-14T15:34:10.374Z",
		"size": 5118,
		"path": "../public/assets/careers-DkOMkuEn.js"
	},
	"/assets/cargo-port-CkDtuTJJ.jpg": {
		"type": "image/jpeg",
		"etag": "\"21dcf-HYC9igHS8PQUcgWz4fc12S4cI6A\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 138703,
		"path": "../public/assets/cargo-port-CkDtuTJJ.jpg"
	},
	"/assets/cargo-port-NdEnc6iJ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"37-MeK6scVLxMEy5vajkZCKlSYgA/Y\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 55,
		"path": "../public/assets/cargo-port-NdEnc6iJ.js"
	},
	"/assets/check-CCwL4Fl-.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"71-FzMAMB4uCeBNZdMX+oFFPI9+JkM\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 113,
		"path": "../public/assets/check-CCwL4Fl-.js"
	},
	"/assets/circle-check-DXjTqMuH.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"a7-ZDcKqe54bznyed9tY9J14GqQvS4\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 167,
		"path": "../public/assets/circle-check-DXjTqMuH.js"
	},
	"/assets/contact-CLAspJKe.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"cad-d/bnylinB6yWlOJGC1k28kwhdOM\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 3245,
		"path": "../public/assets/contact-CLAspJKe.js"
	},
	"/assets/dashboard--heDjbUs.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22c3-a8xaStIQp7K4/1tSYh9WgjB4uXo\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 8899,
		"path": "../public/assets/dashboard--heDjbUs.js"
	},
	"/assets/express-handoff-BfZQceII.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c-pC8OJNG9s4+eKEefP0hWK7k/3Yc\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 60,
		"path": "../public/assets/express-handoff-BfZQceII.js"
	},
	"/assets/careers-hero-B8HOyqJ3.jpg": {
		"type": "image/jpeg",
		"etag": "\"21d9a-li39E5L2Sl6XdG3+W3rvzAvkULo\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 138650,
		"path": "../public/assets/careers-hero-B8HOyqJ3.jpg"
	},
	"/assets/express-handoff-CJxVwHen.jpg": {
		"type": "image/jpeg",
		"etag": "\"1a06c-wVq0w4EzNddselmC+LvtDuBpnXo\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 106604,
		"path": "../public/assets/express-handoff-CJxVwHen.jpg"
	},
	"/assets/global-planes-BJ574qCR.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-+obtml3nN1iKQeHv064C1ouy5cU\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 58,
		"path": "../public/assets/global-planes-BJ574qCR.js"
	},
	"/assets/global-planes-Dt22eXIg.jpg": {
		"type": "image/jpeg",
		"etag": "\"65fd-dyR4aR9nqj1x/EyTM+aPuHV0l2w\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 26109,
		"path": "../public/assets/global-planes-Dt22eXIg.jpg"
	},
	"/assets/help-C8LkyOqa.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"d32-sA+0jam+GUrAPKziD8twKYnM6Q4\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 3378,
		"path": "../public/assets/help-C8LkyOqa.js"
	},
	"/assets/hero-courier-Br-4tkiW.jpg": {
		"type": "image/jpeg",
		"etag": "\"22807-OFNoLpAuy7ETKCjaxD6sxf2nweE\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 141319,
		"path": "../public/assets/hero-courier-Br-4tkiW.jpg"
	},
	"/assets/image-gallery-BZfsnwwZ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"10e8-rjPyIBuQRFM9qs0q/vWS6FFZ58c\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 4328,
		"path": "../public/assets/image-gallery-BZfsnwwZ.js"
	},
	"/assets/index-BSivSO_-.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"a042b-2CfDSY43s5TbIaMsWsy8JeAOFMI\"",
		"mtime": "2026-08-14T15:34:10.372Z",
		"size": 656427,
		"path": "../public/assets/index-BSivSO_-.js"
	},
	"/assets/innovation-data-C6cYsrYg.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c-haYuCHN+5t61a0RAoWZwrGq71nA\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 60,
		"path": "../public/assets/innovation-data-C6cYsrYg.js"
	},
	"/assets/leaf-CQrEuVLF.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"fe-bMUjmXg8xRwU1+8TJLBn7QxfoPs\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 254,
		"path": "../public/assets/leaf-CQrEuVLF.js"
	},
	"/assets/legal-C2yM3Fsu.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"784-g5Q969mcJlqvV04c63Jc6QlUMS0\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 1924,
		"path": "../public/assets/legal-C2yM3Fsu.js"
	},
	"/assets/jsx-runtime-BkSabwWG.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3c1-VkW1xFbt56H2FC99QIi6PTzaFIo\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 961,
		"path": "../public/assets/jsx-runtime-BkSabwWG.js"
	},
	"/assets/innovation-data-0yhncJlr.jpg": {
		"type": "image/jpeg",
		"etag": "\"163e7-Pa0RMC32PdjprWbjPD8np49xo38\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 91111,
		"path": "../public/assets/innovation-data-0yhncJlr.jpg"
	},
	"/assets/map-pin-_NDwOXFr.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f8-uKDJTy+I7JOhIKR/HaN9ywXzEX4\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 248,
		"path": "../public/assets/map-pin-_NDwOXFr.js"
	},
	"/assets/news-D5ooIgaf.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"e09-1Lgr0r482jbKkDIW/H8urcvznr8\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 3593,
		"path": "../public/assets/news-D5ooIgaf.js"
	},
	"/assets/news-hero-CNtVzuot.jpg": {
		"type": "image/jpeg",
		"etag": "\"145aa-ga3sNJBG777KJrXR882OkhM9xVI\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 83370,
		"path": "../public/assets/news-hero-CNtVzuot.jpg"
	},
	"/assets/newspaper-D0MQq6PY.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"14d-19HFTT2e/VVuaYdoPnxr5/DIY2M\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 333,
		"path": "../public/assets/newspaper-D0MQq6PY.js"
	},
	"/assets/onboarding-DdpWHCe8.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1b03-+T1HObNK11L+aoq3y4pTqycawSw\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 6915,
		"path": "../public/assets/onboarding-DdpWHCe8.js"
	},
	"/assets/package-BEGnpOsi.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"169-bqVMXU5hw4qwS40sBtj21BSP5Bo\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 361,
		"path": "../public/assets/package-BEGnpOsi.js"
	},
	"/assets/quote-gWYu4SGZ.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22c3-QRaIHnTO7xGL1KG0WP0Ut9MqbOs\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 8899,
		"path": "../public/assets/quote-gWYu4SGZ.js"
	},
	"/assets/privacy-Bei1l4Aa.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"78e-16FECInF2yLCWsX2sETdWrIyRVM\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 1934,
		"path": "../public/assets/privacy-Bei1l4Aa.js"
	},
	"/assets/pricing-XU7Do4tl.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"f1b-wuVCC2LnVxAY3pe16TL8s+uurkk\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 3867,
		"path": "../public/assets/pricing-XU7Do4tl.js"
	},
	"/assets/react-DHmoMYoq.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1d67-nufvvndhXtiz6VWh8XcPEWVqP1g\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 7527,
		"path": "../public/assets/react-DHmoMYoq.js"
	},
	"/assets/route-C86beB9J.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1ef-iN1s1YNqSLTXJNPg0ZfSPoyFXXU\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 495,
		"path": "../public/assets/route-C86beB9J.js"
	},
	"/assets/routes-C1Cfqb1J.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"5e96-+7wom9eAuI8TnJ887PzxaftovhU\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 24214,
		"path": "../public/assets/routes-C1Cfqb1J.js"
	},
	"/assets/service-ecommerce-fV2g4Ikv.jpg": {
		"type": "image/jpeg",
		"etag": "\"348e-GagV+DBOyeHRIGbA5gDS7/O8lCA\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 13454,
		"path": "../public/assets/service-ecommerce-fV2g4Ikv.jpg"
	},
	"/assets/service-industrial-BjpKOjr3.jpg": {
		"type": "image/jpeg",
		"etag": "\"427c-tpNXglCfz64xCGM54LPDtAsOVrU\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 17020,
		"path": "../public/assets/service-industrial-BjpKOjr3.jpg"
	},
	"/assets/service-ocean-air-DYb89scQ.jpg": {
		"type": "image/jpeg",
		"etag": "\"4f5d-q3cCWmE2KGjMGHHVB9TGDxT6YAk\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 20317,
		"path": "../public/assets/service-ocean-air-DYb89scQ.jpg"
	},
	"/assets/service-supply-chain-B-tn33fr.jpg": {
		"type": "image/jpeg",
		"etag": "\"47ec-HwL6/PBX3mvxR+HUwtaAIGmka8s\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 18412,
		"path": "../public/assets/service-supply-chain-B-tn33fr.jpg"
	},
	"/assets/services-DpT4WAzA.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"22f3-FJyzLvs0QkZVFnCOhW4pev+WWUA\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 8947,
		"path": "../public/assets/services-DpT4WAzA.js"
	},
	"/assets/services-hero-DdzTuCum.jpg": {
		"type": "image/jpeg",
		"etag": "\"30a3d-fG+Pj3kXWKYq57nm5FzwO+K4JWo\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 199229,
		"path": "../public/assets/services-hero-DdzTuCum.jpg"
	},
	"/assets/styles-ziPb7C5W.css": {
		"type": "text/css; charset=utf-8",
		"etag": "\"16f50-1zwuEn18EVZaC0e4B2JhnQFDvX4\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 94032,
		"path": "../public/assets/styles-ziPb7C5W.css"
	},
	"/assets/sustainability-bKyZ7ovf.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"1124-mX3Jk11FDKiDvxEeXwMV2tazCyc\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 4388,
		"path": "../public/assets/sustainability-bKyZ7ovf.js"
	},
	"/assets/sustainability-hero-DaYcT8DZ.jpg": {
		"type": "image/jpeg",
		"etag": "\"38bf4-3neOvK9i0rgNozTFNfgdegw56l8\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 232436,
		"path": "../public/assets/sustainability-hero-DaYcT8DZ.jpg"
	},
	"/assets/sustainability-van-D-5ZfF9m.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3f-WdhRlgVJpAAaBvx3rMTHM1ARzO8\"",
		"mtime": "2026-08-14T15:34:10.375Z",
		"size": 63,
		"path": "../public/assets/sustainability-van-D-5ZfF9m.js"
	},
	"/assets/team-portrait-BbSWKlwB.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-fWD4vXIgwmS7czQqgkhq6oKISIc\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 58,
		"path": "../public/assets/team-portrait-BbSWKlwB.js"
	},
	"/assets/team-portrait-ClIXUWbH.jpg": {
		"type": "image/jpeg",
		"etag": "\"3be82-Ude0q9RheM+hDI+m5PTXg9A+p9M\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 245378,
		"path": "../public/assets/team-portrait-ClIXUWbH.jpg"
	},
	"/assets/sustainability-van-DjoVtKcX.jpg": {
		"type": "image/jpeg",
		"etag": "\"109b9-tablrN1ewiWUXmM56k1Mg15Hs30\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 68025,
		"path": "../public/assets/sustainability-van-DjoVtKcX.jpg"
	},
	"/assets/terms-D0e-HJBX.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"64e-5vIpyjbhOUGvLZPo8iby5z/jM9w\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 1614,
		"path": "../public/assets/terms-D0e-HJBX.js"
	},
	"/assets/testimonial-customer-BCVW9qyW.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"41-InTFLx5ZgIkWTyNqfy4ENhRDusw\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 65,
		"path": "../public/assets/testimonial-customer-BCVW9qyW.js"
	},
	"/assets/testimonial-customer-Bdy82DCA.jpg": {
		"type": "image/jpeg",
		"etag": "\"1aaed-cBx0UHSYqhZz7wKKILrmpwSkTVg\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 109293,
		"path": "../public/assets/testimonial-customer-Bdy82DCA.jpg"
	},
	"/assets/track-CATKG1q0.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"16ec-2S3DIlvFb5GBvgJZPMEuYKVp/5U\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 5868,
		"path": "../public/assets/track-CATKG1q0.js"
	},
	"/assets/useStore-BqDt8rA_.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"153e-0LljSK9bcHL/E+3V+jeBcctrpF4\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 5438,
		"path": "../public/assets/useStore-BqDt8rA_.js"
	},
	"/assets/warehouse-JKC5l2Bw.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"16c-6vTABJzmLhO3RiSseWVoUpYxgmM\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 364,
		"path": "../public/assets/warehouse-JKC5l2Bw.js"
	},
	"/assets/users-37zTm3y0.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"127-1+r8lsxRaN/tAcASnqkJ7XvfI2Q\"",
		"mtime": "2026-08-14T15:34:10.376Z",
		"size": 295,
		"path": "../public/assets/users-37zTm3y0.js"
	},
	"/assets/warehouse-ops-CQcMgWIS.jpg": {
		"type": "image/jpeg",
		"etag": "\"24817-Lh/Z6VpVl9+yfW8D9MEUPVbXQsE\"",
		"mtime": "2026-08-14T15:34:10.377Z",
		"size": 149527,
		"path": "../public/assets/warehouse-ops-CQcMgWIS.jpg"
	},
	"/assets/warehouse-ops-CWcONLV9.js": {
		"type": "text/javascript; charset=utf-8",
		"etag": "\"3a-DLcN4hGtNxCdGFLoSalUxT/0RiU\"",
		"mtime": "2026-08-14T15:34:10.376Z",
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
var _lazy_hnE4De = defineLazyEventHandler(() => import("./_chunks/ssr-renderer.mjs"));
var findRoute = /* @__PURE__ */ (() => {
	const data = {
		route: "/**",
		handler: _lazy_hnE4De
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
