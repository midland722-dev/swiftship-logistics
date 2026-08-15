-- Supabase user cleanup script
-- Run this in Supabase Dashboard -> SQL Editor
-- This preserves ONLY the admin user and removes all other auth users

BEGIN;

-- Ensure the admin user exists before deleting anything
DO $$
DECLARE
  admin_user_id uuid;
BEGIN
  SELECT id INTO admin_user_id
  FROM auth.users
  WHERE email = 'admin@ascl-logistics.com';

  IF admin_user_id IS NULL THEN
    RAISE EXCEPTION 'Admin user admin@ascl-logistics.com not found. Create it first in Supabase Dashboard -> Authentication -> Users, then re-run this script.';
  END IF;

  -- Remove non-admin roles
  DELETE FROM public.user_roles
  WHERE user_id != admin_user_id;

  -- Remove non-admin profiles
  DELETE FROM public.profiles
  WHERE id != admin_user_id;

  -- Remove non-admin auth users
  DELETE FROM auth.users
  WHERE id != admin_user_id;

  RAISE NOTICE 'User cleanup complete. Admin user preserved.';
END;
$$;

COMMIT;
