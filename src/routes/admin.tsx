import { createFileRoute, Navigate } from "@tanstack/react-router";

export const Route = createFileRoute("/admin")({
  head: () => ({ meta: [{ title: "Admin — American Shipping & Logistics" }] }),
  component: AdminRedirect,
});

function AdminRedirect() {
  return <Navigate to="/deprixa/login.php" replace />;
}
