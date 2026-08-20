import { createFileRoute } from "@tanstack/react-router";

// Public health endpoint used by the hosting platform / load balancer.
// Returns 200 so the panel is reported healthy. Intentionally has no
// dependencies (DB, Supabase) so it cannot fail due to missing env/config.
export const Route = createFileRoute("/api/public/panel-health")({
  server: {
    handlers: {
      GET: async () => {
        return Response.json({
          status: "ok",
          timestamp: new Date().toISOString(),
        });
      },
    },
  },
});
