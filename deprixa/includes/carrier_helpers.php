<?php
/**
 * Carrier Tracking Admin Helpers
 * -------------------------------
 * Shared helpers for rendering carrier tracking UI in the admin dashboard.
 */

if (!function_exists('carrierTrackingOptions')) {
    /**
     * Return active tracking integrations for dropdowns.
     *
     * @return array<int, array>
     */
    function carrierTrackingOptions(PDO $db) {
        try {
            return $db->query("
                SELECT id, integration_name, provider, integration_type
                FROM api_integrations
                WHERE is_active = 1 AND integration_type = 'tracking'
                ORDER BY provider, integration_name
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('renderCarrierBadge')) {
    /**
     * Render a small Bootstrap badge for carrier tracking status.
     *
     * @param array|null $info  Result of getCarrierTrackingInfo()
     * @return string HTML
     */
    function renderCarrierBadge($info) {
        if (!$info || empty($info['carrier_tracking_number'])) {
            return '<span class="badge bg-secondary">No Carrier</span>';
        }
        $provider = htmlspecialchars($info['provider'] ?? $info['carrier_name'] ?? 'Carrier');
        $tn = htmlspecialchars($info['carrier_tracking_number']);
        $sync = $info['last_carrier_sync_at'] ?? null;
        $syncLabel = $sync ? ('Synced ' . date('M d H:i', strtotime($sync))) : 'Never synced';
        return '<span class="badge bg-info text-dark" title="' . htmlspecialchars($syncLabel) . '">'
            . $provider . ': ' . $tn . '</span>';
    }
}

if (!function_exists('carrierSyncForm')) {
    /**
     * Render the carrier sync button + modal trigger for a shipment row.
     *
     * @param int    $shipmentId
     * @param string $csrfToken
     * @return string HTML
     */
    function carrierSyncForm($shipmentId, $csrfToken) {
        $sid = (int)$shipmentId;
        return '<form method="POST" action="../carrier_tracking.php" class="d-inline" onsubmit="return confirm(\'Sync tracking from carrier?\');">
            <input type="hidden" name="action" value="sync_carrier">
            <input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrfToken) . '">
            <input type="hidden" name="shipment_id" value="' . $sid . '">
            <button type="submit" class="btn btn-sm btn-outline-primary" title="Sync from carrier">
                <i class="bi bi-arrow-clockwise"></i> Sync Carrier
            </button>
        </form>';
    }
}

if (!function_exists('webhookUrlForIntegration')) {
    /**
     * Build the public webhook URL for an integration.
     *
     * @param int    $integrationId
     * @param string $host  Optional override; defaults to $_SERVER['HTTP_HOST']
     * @return string
     */
    function webhookUrlForIntegration($integrationId, $host = '') {
        $host = $host ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return htmlspecialchars($scheme . '://' . $host . '/shp/api/v1/webhooks/carrier.php?integration_id=' . (int)$integrationId, ENT_QUOTES, 'UTF-8');
    }
}
