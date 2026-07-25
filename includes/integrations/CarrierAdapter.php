<?php
/**
 * Carrier Adapter — shared logic for carrier integrations (tracking / rating / shipping).
 *
 * Responsibilities:
 *  - map a provider status string to a canonical shipment status code
 *    (via the integration_status_map table, with sane built-in defaults)
 *  - normalize provider status spelling (the codebase has inconsistent
 *    internal spellings like 'delivered' vs 'delivered'; we canonicalize both
 *    provider and internal values before comparing)
 *  - ingest a normalized tracking event into the shipment timeline
 *  - (rating/shipping) request rates and create a shipment + buy a label
 */

require_once __DIR__ . '/IntegrationClient.php';
require_once __DIR__ . '/../tracking.php';
require_once __DIR__ . '/../shipment_status.php';

class CarrierAdapter extends IntegrationClient {
    /** Default provider-status => canonical-status mapping (fallback). */
    protected $defaultStatusMap = [
        'label_created'        => 'created',
        'info_received'        => 'created',
        'pending'              => 'created',
        'picked_up'            => 'picked_up',
        'in_transit'           => 'in_transit',
        'out_for_delivery'     => 'out_for_delivery',
        'delivered'            => 'delivered',
        'delivery'             => 'delivered',
        'exception'            => 'on_hold',
        'failed_attempt'       => 'delivery_failed',
        'return_to_sender'     => 'returned',
        'cancelled'            => 'cancelled',
        'canceled'             => 'cancelled',
    ];

    /**
     * Canonicalize a status string so internal typos don't break mapping.
     */
    public static function normalize($status) {
        $s = strtolower(trim((string)$status));
        $fix = [
            'delivered' => 'delivered',
            'deliverd'  => 'delivered',
            'cancelled' => 'cancelled',
            'canceled'  => 'cancelled',
            'returned'  => 'returned',
            'retured'   => 'returned',
            'in_transit'=> 'in_transit',
            'intransit' => 'in_transit',
        ];
        return $fix[$s] ?? $s;
    }

    /**
     * Map a provider status to a canonical shipment status code.
     */
    public function mapStatus($providerStatus) {
        $raw = self::normalize($providerStatus);
        // 1) DB-driven map (per integration / provider).
        try {
            $stmt = $this->db->prepare("
                SELECT canonical_status FROM integration_status_map
                WHERE provider = :p AND provider_status = :ps
                LIMIT 1
            ");
            $stmt->execute([':p' => $this->integration['provider'], ':ps' => $raw]);
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['canonical_status'];
            }
        } catch (Exception $e) { /* table may not exist yet */ }
        // 2) Built-in defaults.
        if (isset($this->defaultStatusMap[$raw])) {
            return $this->defaultStatusMap[$raw];
        }
        // 3) If the raw value is already a canonical code, accept it.
        if (isValidStatus($raw)) {
            return $raw;
        }
        return 'in_transit';
    }

    /**
     * Ingest a normalized tracking event for a shipment.
     * Appends to the timeline (tracking_history + shipment_status_history_v2)
     * and updates the shipment's current status — same contract as the admin
     * update_tracking flow. $source tags the event as carrier-sourced.
     *
     * @param int    $shipmentId
     * @param string $trackingNumber
     * @param string $providerStatus
     * @param array  $event  optional ['location','description','timestamp','city','country']
     * @return int|false new event id
     */
    public function ingestTracking($shipmentId, $trackingNumber, $providerStatus, array $event = []) {
        $status = $this->mapStatus($providerStatus);
        $location = $event['location'] ?? null;
        $description = $event['description'] ?? ('Carrier update: ' . $providerStatus);
        $occurred = $event['timestamp'] ?? date('Y-m-d H:i:s');

        try {
            $this->db->beginTransaction();

            $this->db->prepare("
                INSERT INTO shipment_status_history_v2
                    (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                VALUES (:sid, :s, :at, :l, :r, :by)
            ")->execute([
                ':sid' => $shipmentId, ':s' => $status, ':at' => $occurred,
                ':l' => $location, ':r' => $description, ':by' => 'carrier:' . $this->integration['provider'],
            ]);

            $this->db->prepare("
                INSERT INTO tracking_history
                    (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, source, integration_id, created_at)
                VALUES (:sid, :tn, :s, :l, :d, :at, :by, 'carrier', :iid, NOW())
            ")->execute([
                ':sid' => $shipmentId, ':tn' => $trackingNumber, ':s' => $status,
                ':l' => $location, ':d' => $description, ':at' => $occurred,
                ':by' => 'carrier:' . $this->integration['provider'], ':iid' => $this->integration['id'],
            ]);

            $upd = "UPDATE shipments SET status = :s, updated_at = NOW()";
            $params = [':s' => $status, ':id' => $shipmentId];
            if (!empty($event['city']))      { $upd .= ", current_city = :cc"; $params[':cc'] = $event['city']; }
            if (!empty($event['country']))  { $upd .= ", current_country = :co"; $params[':co'] = $event['country']; }
            $upd .= " WHERE id = :id";
            $this->db->prepare($upd)->execute($params);

            $this->db->commit();
            clearDashboardCache();

            // Notify the customer (mirrors update_tracking.php behaviour).
            notifyTrackingEvent($this->db, $shipmentId, $trackingNumber, $status, $location, $description);

            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            $this->markFailure('ingestTracking: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch a shipping rate quote from the carrier (rating integration).
     * Override in provider subclasses to build provider-specific payloads.
     */
    public function getRates(array $params) {
        $resp = $this->request('POST', 'rates', $params);
        return $resp['body'] ?? null;
    }

    /**
     * Create a shipment with the carrier and return its external id + label.
     */
    public function createShipment(array $shipment) {
        $resp = $this->request('POST', 'shipments', $shipment);
        return $resp['body'] ?? null;
    }

    /**
     * Buy / generate a shipping label; returns a label URL or base64 PNG.
     */
    public function buyLabel($externalShipmentId) {
        $resp = $this->request('POST', 'shipments/' . rawurlencode($externalShipmentId) . '/label');
        return $resp['body'] ?? null;
    }

    /**
     * Parse a carrier inbound webhook payload into a list of normalized events.
     * Supports a flat object or a {"events":[...]} envelope with common field
     * names. Provider subclasses may override for bespoke payloads.
     */
    public function parseInboundWebhook($payload, $headers = []) {
        $events = [];
        if (isset($payload['events']) && is_array($payload['events'])) {
            foreach ($payload['events'] as $e) {
                $events[] = $this->normalizeEvent($e);
            }
        } else {
            $events[] = $this->normalizeEvent($payload);
        }
        return $events;
    }

    protected function normalizeEvent($e) {
        $e = is_array($e) ? $e : [];
        return [
            'tracking_number' => $e['tracking_number'] ?? $e['trackingNumber'] ?? $e['tracking_no'] ?? $e['trackingNumberInfo']['trackingNumber'] ?? null,
            'status'          => $e['status'] ?? $e['statusCode'] ?? $e['event'] ?? $e['shipmentStatus'] ?? null,
            'location'       => $e['location'] ?? $e['city'] ?? null,
            'description'    => $e['description'] ?? $e['remarks'] ?? $e['statusDescription'] ?? null,
            'timestamp'      => $e['timestamp'] ?? $e['event_time'] ?? $e['occurred_at'] ?? $e['dateTime'] ?? null,
            'city'           => $e['city'] ?? null,
            'country'        => $e['country'] ?? null,
        ];
    }

    /**
     * Resolve a local shipment id from a tracking number.
     */
    public function shipmentIdByTracking($trackingNumber) {
        try {
            $stmt = $this->db->prepare("SELECT id, tracking_number FROM shipments WHERE tracking_number = :tn LIMIT 1");
            $stmt->execute([':tn' => $trackingNumber]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['id'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Poll the carrier for the latest tracking status of a shipment.
     * Base implementation expects a REST GET; override per provider.
     */
    public function pollTracking($trackingNumber) {
        $resp = $this->request('GET', 'tracking/' . rawurlencode($trackingNumber));
        return $resp['body'] ?? null;
    }
}
