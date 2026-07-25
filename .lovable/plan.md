
## Overview

Enable Lovable Cloud (auth + database) and build four connected features on top of it. This is a large multi-step build; I'll ship it in phases so each is testable.

## Phase 1 — Foundation (Cloud + Auth + Roles)

- Enable Lovable Cloud.
- Add `/auth` (email+password sign in/up) and a protected `_authenticated` layout.
- Schema:
  - `profiles` (id → auth.users, full_name, company, phone, onboarded_at)
  - `app_role` enum: `admin`, `staff`, `customer`
  - `user_roles` (user_id, role) + `has_role()` security-definer function
  - Trigger to auto-create profile + default `customer` role on signup
- Header shows account menu / sign out when signed in.

## Phase 2 — Dynamic Pricing Calculator

- Table `pricing_rules` (base_fee, per_kg, volumetric_divisor, speed multipliers, insurance_fee, currency) — single active row, admin-editable.
- Rewrite `/quote` to:
  - Load active rules from DB
  - Live estimate as user types (weight, dims, speed, insurance, declared value)
  - Show line-item breakdown (base, weight, speed multiplier, insurance)
  - "Save quote" persists to `quotes` table for signed-in users; guests get a shareable local estimate
  - "Book this shipment" (signed-in) → creates a `shipments` row and redirects to tracking

## Phase 3 — Shipments + Tracking

- Tables:
  - `shipments` (id, tracking_code, owner_id, from, to, weight, dims, speed, status, eta, price, created_at)
  - `shipment_events` (shipment_id, label, location, occurred_at, done)
  - `shipment_alert_prefs` (user_id, email_enabled, sms_enabled, push_enabled, phone_e164)
- `/track` reads real data by tracking code (public read of limited fields by code; owner sees full detail when signed in).
- `/dashboard` (customer): my shipments, my quotes, alert preferences.

## Phase 4 — Onboarding Wizard

- After first sign-in, redirect to `/onboarding` until `profiles.onboarded_at` is set:
  1. Profile (name, company, phone)
  2. Team role (Individual / Small business / Enterprise) — stored on profile
  3. Alert channels (email/SMS/push) + phone number + browser push permission
  4. Pick a starter shipment template (Documents / Small parcel / Pallet) → seeds `shipment_templates` row
- Skippable steps, resumable, progress bar.

## Phase 5 — Automated Tracking Alerts

- Connectors needed: Twilio (SMS). Email uses Lovable Emails (I'll trigger the email domain setup dialog).
- Web push via native `PushManager` + VAPID keys stored as secrets; subscription saved to `push_subscriptions` table.
- Trigger on `shipments.status` change (Postgres trigger → server route `/api/public/webhooks/shipment-status` with HMAC, OR trigger calls a server route directly via pg_net):
  - Send email template `shipment-status-update` (scaffold Lovable Emails first)
  - Send SMS via Twilio gateway when `sms_enabled` + phone present
  - Send web push to all subscriptions when `push_enabled`
- Admin can manually update status → alerts fire automatically.

## Phase 6 — Admin Dashboard (`/admin`, gated by `has_role('admin')`)

- **Users & roles**: list users, assign/revoke roles, disable accounts (via `supabaseAdmin`).
- **Shipments**: table with filters, status updates (triggers alerts), edit ETA.
- **Pricing rules**: form to edit the active `pricing_rules` row.
- **Content**: CRUD for `news_posts` and `service_bulletins` (bulletins shown on homepage banner, posts on `/news`).
- First admin: promoted via SQL migration for a specified email.

## Technical Notes

- All tables get explicit `GRANT`s + RLS policies scoped to `auth.uid()` or `has_role('admin')`.
- Server functions use `requireSupabaseAuth`; admin-only fns re-check `has_role('admin')` via `context.supabase` before loading `supabaseAdmin`.
- Push, SMS, and email each fail gracefully — a missing channel doesn't block the others.
- Homepage bulletin banner + news page switch from hardcoded to DB-driven.

## What I need from you before starting

1. **Admin email** — which email should be seeded as the first admin? (needed for the migration in Phase 1)
2. **Twilio** — I'll prompt you to connect it when we reach Phase 5. OK?
3. **Email domain** — Lovable Emails requires a domain you own for branded sending. When we hit Phase 5 I'll open the setup dialog; you can also skip and use default templates.

Shall I proceed phase by phase, starting with Phase 1?
