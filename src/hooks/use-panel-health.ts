import { useQuery } from "@tanstack/react-query";

export type PanelCheck = {
  path: string;
  label: string;
  ok: boolean;
  status: number;
  location: string | null;
  error?: string;
  attempts?: number;
  ms: number;
};

export type PanelHealth = {
  panelOrigin: string;
  healthy: boolean;
  degraded: boolean;
  checkedAt: string;
  checks: PanelCheck[];
};

async function fetchPanelHealth(): Promise<PanelHealth> {
  const res = await fetch("/api/public/panel-health", { cache: "no-store" });
  return (await res.json()) as PanelHealth;
}

export function usePanelHealth(refetchInterval = 60_000) {
  return useQuery({
    queryKey: ["panel-health"],
    queryFn: fetchPanelHealth,
    refetchInterval,
    refetchOnWindowFocus: true,
    staleTime: 15_000,
  });
}
