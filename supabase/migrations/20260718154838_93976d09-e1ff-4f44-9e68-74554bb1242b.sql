
-- Fix search_path on helpers
CREATE OR REPLACE FUNCTION public.touch_updated_at()
RETURNS TRIGGER LANGUAGE plpgsql SET search_path = public AS $$
BEGIN NEW.updated_at = now(); RETURN NEW; END;
$$;

CREATE OR REPLACE FUNCTION public.generate_tracking_code()
RETURNS TEXT LANGUAGE plpgsql SET search_path = public AS $$
DECLARE code TEXT;
BEGIN
  code := 'VLT-' || lpad(floor(random()*9999999)::text, 7, '0');
  RETURN code;
END;
$$;

-- Restrict EXECUTE on SECURITY DEFINER functions
REVOKE ALL ON FUNCTION public.handle_new_user() FROM PUBLIC, anon, authenticated;
REVOKE ALL ON FUNCTION public.has_role(uuid, public.app_role) FROM PUBLIC;
GRANT EXECUTE ON FUNCTION public.has_role(uuid, public.app_role) TO authenticated, service_role;
REVOKE ALL ON FUNCTION public.admin_grant_role(text, public.app_role) FROM PUBLIC, anon;
GRANT EXECUTE ON FUNCTION public.admin_grant_role(text, public.app_role) TO authenticated;
REVOKE ALL ON FUNCTION public.admin_revoke_role(uuid, public.app_role) FROM PUBLIC, anon;
GRANT EXECUTE ON FUNCTION public.admin_revoke_role(uuid, public.app_role) TO authenticated;
