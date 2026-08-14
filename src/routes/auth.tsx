import { createFileRoute, useNavigate, Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { z } from "zod";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/use-auth";
import { Package } from "lucide-react";
import { toast } from "sonner";

const searchSchema = z.object({ next: z.string().optional() });

export const Route = createFileRoute("/auth")({
  validateSearch: searchSchema,
  head: () => ({
    meta: [
      { title: "Sign in — American Shipping & Logistics" },
      { name: "description", content: "Sign in or create a American Shipping & Logistics account to ship, track, and manage parcels." },
    ],
  }),
  component: AuthPage,
});

function AuthPage() {
  const { next } = Route.useSearch();
  const { session, loading } = useAuth();
  const navigate = useNavigate();
  const [mode, setMode] = useState<"signin" | "signup">("signin");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [fullName, setFullName] = useState("");
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!loading && session) {
      navigate({ to: (next as "/dashboard") || "/dashboard", replace: true });
    }
  }, [session, loading, next, navigate]);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setBusy(true);
    try {
      if (mode === "signup") {
        const { error } = await supabase.auth.signUp({
          email,
          password,
          options: {
            emailRedirectTo: `${window.location.origin}/dashboard`,
            data: { full_name: fullName },
          },
        });
        if (error) throw error;
        toast.success("Account created. You're signed in.");
      } else {
        const { error } = await supabase.auth.signInWithPassword({ email, password });
        if (error) throw error;
        toast.success("Welcome back.");
      }
    } catch (err: any) {
      toast.error(err.message ?? "Something went wrong");
    } finally {
      setBusy(false);
    }
  };

  return (
    <section className="container-x flex min-h-[70vh] items-center justify-center py-16">
      <div className="w-full max-w-md rounded-2xl border border-border bg-surface/60 p-8">
        <Link to="/" className="mb-6 inline-flex items-center gap-2 font-display text-lg font-bold">
          <span className="grid h-8 w-8 place-items-center rounded-sm bg-brand text-accent">
            <Package className="h-4 w-4" strokeWidth={2.75} />
          </span>
          American Shipping & Logistics
        </Link>
        <h1 className="font-display text-3xl font-bold">
          {mode === "signin" ? "Sign in" : "Create your account"}
        </h1>
        <p className="mt-1 text-sm text-muted-foreground">
          {mode === "signin"
            ? "Access your shipments, quotes, and alerts."
            : "Start shipping in minutes."}
        </p>

        <form onSubmit={submit} className="mt-6 space-y-4">
          {mode === "signup" && (
            <label className="block">
              <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">Full name</span>
              <input
                required
                value={fullName}
                onChange={(e) => setFullName(e.target.value)}
                className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
              />
            </label>
          )}
          <label className="block">
            <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">Email</span>
            <input
              required
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
            />
          </label>
          <label className="block">
            <span className="text-xs font-medium uppercase tracking-wider text-muted-foreground">Password</span>
            <input
              required
              type="password"
              minLength={6}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="mt-2 w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm outline-none focus:border-brand"
            />
          </label>
          <button
            disabled={busy}
            className="w-full rounded-sm bg-accent py-3 text-sm font-bold uppercase tracking-wider text-accent-foreground hover:opacity-90 disabled:opacity-60"
          >
            {busy ? "Please wait…" : mode === "signin" ? "Sign in" : "Create account"}
          </button>
        </form>

        <button
          onClick={() => setMode(mode === "signin" ? "signup" : "signin")}
          className="mt-4 w-full text-center text-sm text-muted-foreground hover:text-foreground"
        >
          {mode === "signin" ? "New to American Shipping & Logistics? Create an account" : "Have an account? Sign in"}
        </button>
      </div>
    </section>
  );
}
