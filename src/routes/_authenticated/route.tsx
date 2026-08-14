import { createFileRoute, Outlet, useLocation } from "@tanstack/react-router";
import { useEffect } from "react";
import { useAuth } from "@/hooks/use-auth";

export const Route = createFileRoute("/_authenticated")({
  component: ProtectedLayout,
});

function ProtectedLayout() {
  const { session, loading } = useAuth();
  const location = useLocation();

  useEffect(() => {
    if (!session && !loading) {
      window.location.href = "/admin";
    }
  }, [session, loading]);

  if (loading) {
    return (
      <div className="container-x py-24 text-center text-sm text-muted-foreground">Loading…</div>
    );
  }
  if (!session) {
    return null;
  }
  return <Outlet />;
}
