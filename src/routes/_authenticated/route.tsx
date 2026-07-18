import { createFileRoute, Outlet, Navigate, useLocation } from "@tanstack/react-router";
import { useAuth } from "@/hooks/use-auth";

export const Route = createFileRoute("/_authenticated")({
  component: ProtectedLayout,
});

function ProtectedLayout() {
  const { session, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="container-x py-24 text-center text-sm text-muted-foreground">Loading…</div>
    );
  }
  if (!session) {
    return <Navigate to="/auth" search={{ next: location.pathname }} replace />;
  }
  return <Outlet />;
}
