import { createFileRoute } from "@tanstack/react-router";
import type {} from "@tanstack/react-start";

// Set this to your production domain before deploying.
const BASE_URL = "https://ascl-logistics.com";

const paths = [
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
  "/terms",
];

export const Route = createFileRoute("/sitemap.xml")({
  server: {
    handlers: {
      GET: async () => {
        const urls = paths
          .map(
            (p) =>
              `  <url><loc>${BASE_URL}${p}</loc><changefreq>weekly</changefreq></url>`,
          )
          .join("\n");
        const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls}
</urlset>`;
        return new Response(xml, {
          headers: {
            "Content-Type": "application/xml",
            "Cache-Control": "public, max-age=3600",
          },
        });
      },
    },
  },
});
