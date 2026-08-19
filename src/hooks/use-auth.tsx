import { createContext, useContext, useEffect, useRef, useState, type ReactNode } from "react";
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
  const timeoutRef = useRef<ReturnType<typeof setTimeout>>();

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
    let sub: { subscription: { unsubscribe: () => void } } | undefined;
    try {
      const { data } = supabase.auth.onAuthStateChange((_e, s) => {
        setSession(s);
        if (timeoutRef.current) clearTimeout(timeoutRef.current);
        timeoutRef.current = setTimeout(() => loadRole(s?.user?.id), 0);
      });
      sub = data;
      supabase.auth
        .getSession()
        .then(({ data }) => {
          setSession(data.session);
          return loadRole(data.session?.user?.id);
        })
        .catch(() => {})
        .finally(() => setLoading(false));
    } catch {
      setLoading(false);
    }
    return () => {
      sub?.subscription.unsubscribe();
      if (timeoutRef.current) clearTimeout(timeoutRef.current);
    };
  }, []);

  const signOut = async () => {
    try {
      const { error } = await supabase.auth.signOut();
      if (error) {
        console.error("Sign out failed:", error);
      }
    } catch (err) {
      console.error("Sign out error:", err);
    } finally {
      setSession(null);
      setRole(null);
    }
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
