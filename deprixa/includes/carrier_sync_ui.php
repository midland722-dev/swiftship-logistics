<?php
if (!function_exists('carrierTrackingSyncHealth')) {
    /**
     * Return sync health summary for a given integration.
     *
     * @param PDO $db
     * @param int $integrationId
     * @return array
     */
    function carrierTrackingSyncHealth(PDO $db, $integrationId) {
        $integrationId = (int)$integrationId;
        $stmt = $db->prepare("
            SELECT
                COUNT(*) AS total_linked,
                SUM(CASE WHEN last_carrier_sync_at IS NOT NULL AND last_carrier_sync_at > NOW() - INTERVAL 6 HOUR THEN 1 ELSE 0 END) AS synced_recently,
                SUM(CASE WHEN last_carrier_sync_at IS NULL THEN 1 ELSE 0 END) AS never_synced,
                SUM(CASE WHEN last_carrier_error IS NOT NULL THEN 1 ELSE 0 END) AS has_error
            FROM shipments
            WHERE carrier_integration_id = :iid
        ");
        $stmt->execute([':iid' => $integrationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('syncHealthBadge')) {
    function syncHealthBadge(array $stats) {
        $total = (int)($stats['total_linked'] ?? 0);
        if ($total === 0) {
            return '<span class="badge bg-secondary">No linked shipments</span>';
        }
        $recent = (int)($stats['synced_recently'] ?? 0);
        $pct = $total > 0 ? round(($recent / $total) * 100) : 0;
        $cls = $pct >= 80 ? 'bg-success' : ($pct >= 40 ? 'bg-warning text-dark' : 'bg-danger');
        return '<span class="badge ' . $cls . '">' . $pct . '% synced (6h)</span>';
    }
}
