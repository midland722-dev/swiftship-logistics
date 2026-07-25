-- Migration 0014: FastShip Logistics carrier integration
-- No schema changes required. The existing api_integrations,
-- carrier_tracking_events, and webhook_subscriptions tables already
-- support arbitrary providers. This migration documents the version.

-- Optional: index provider + integration_type for faster carrier lookups.
ALTER TABLE api_integrations
  ADD INDEX idx_provider_type (provider, integration_type);
