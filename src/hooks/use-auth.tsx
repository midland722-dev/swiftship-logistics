import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import type { Session, User } from "@supabase/supabase-js";
import { supabase } from "@/integrations/supabase/client";

type AuthRole = "admin" | "staff" | "customer" | null;

interface AuthContextValue {
  session: Session | null;
  user: User | null;
  role: AuthRole;
  isAdmin: boolean;
  isStaff: boolean;
  loading: boolean;
  signOut: () => Promise<void>;
  refreshRole: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [session, setSession] = useState<Session | null>(null);
  const [role, setRole] = useState<AuthRole>(null);
  const [loading, setLoading] = useState(true);

  const loadRole = async (uid: string | undefined) => {
    if (!uid) {
      setRole(null);
      return;
    }
    const { data } = await supabase
      .from("user_roles")
      .select("role")
      .eq("user_id", uid);
    const roles = (data ?? []).map((r) => r.role as string);
    if (roles.includes("admin")) setRole("admin");
    else if (roles.includes("staff")) setRole("staff");
    else setRole("customer");
  };

  useEffect(() => {
    // Listen first, then get initial session
    const { data: sub } = supabase.auth.onAuthStateChange((_e, s) => {
      setSession(s);
      // Defer role fetch to avoid deadlocks
      setTimeout(() => loadRole(s?.user?.id), 0);
    });
    supabase.auth.getSession().then(({ data }) => {
      setSession(data.session);
      loadRole(data.session?.user?.id).finally(() => setLoading(false));
    });
    return () => sub.subscription.unsubscribe();
  }, []);

  const signOut = async () => {
    await supabase.auth.signOut();
    setRole(null);
  };

  const refreshRole = async () => loadRole(session?.user?.id);

  return (
    <AuthContext.Provider
      value={{
        session,
        user: session?.user ?? null,
        role,
        isAdmin: role === "admin",
        isStaff: role === "staff" || role === "admin",
        loading,
        signOut,
        refreshRole,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
