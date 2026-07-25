<?php
/**
 * Shipment Simulation Engine
 * ---------------------------
 * Automatically progresses shipments through realistic shipping stages
 * with believable timestamps, locations, and status transitions.
 *
 * Usage:
 *   $engine = new SimulationEngine($db);
 *   $engine->progressShipment($shipmentId);
 *   $engine->runBatch($maxShipments = 50);
 */

require_once __DIR__ . '/../../includes/carrier_tracking.php';
require_once __DIR__ . '/../../includes/tracking.php';
require_once __DIR__ . '/../../includes/shipment_status.php';

class SimulationEngine
{
    private $db;
    private $logger;

    private $usaCities = [
        'New York'=>'US','Philadelphia'=>'US','Boston'=>'US','Washington DC'=>'US','Chicago'=>'US','Detroit'=>'US','Indianapolis'=>'US',
        'Atlanta'=>'US','Miami'=>'US','Charlotte'=>'US','Dallas'=>'US','Houston'=>'US','Denver'=>'US','Phoenix'=>'US','Los Angeles'=>'US',
        'San Francisco'=>'US','Seattle'=>'US','Las Vegas'=>'US','Portland'=>'US','Salt Lake City'=>'US',
    ];

    private $internationalHubs = [
        'London'=>'GB','Frankfurt'=>'DE','Hamburg'=>'DE','Dubai'=>'AE','Shanghai'=>'CN',
        'Shenzhen'=>'CN','Tokyo'=>'JP','Singapore'=>'SG','Sydney'=>'AU','Hong Kong'=>'HK',
        'Amsterdam'=>'NL','Paris'=>'FR','Mumbai'=>'IN','Delhi'=>'IN','Mexico City'=>'MX',
        'São Paulo'=>'BR','Johannesburg'=>'ZA','Nairobi'=>'KE','Cairo'=>'EG','Toronto'=>'CA',
    ];

    private $stageSequence = [
        'created'          => ['label' => 'Shipment Information Received',  'progress' => 5],
        'pending_pickup'   => ['label' => 'Pickup Scheduled',               'progress' => 10],
        'picked_up'        => ['label' => 'Parcel Picked Up',               'progress' => 20],
        'received_origin'  => ['label' => 'Received at Origin Facility',    'progress' => 30],
        'sorted'           => ['label' => 'Processing Shipment',            'progress' => 35],
        'in_transit'       => ['label' => 'In Transit',                     'progress' => 50],
        'at_hub'           => ['label' => 'Arrived at Destination Hub',     'progress' => 70],
        'out_for_delivery' => ['label' => 'Out for Delivery',               'progress' => 90],
        'delivered'        => ['label' => 'Delivered',                      'progress' => 100],
    ];

    private $exceptionStatuses = [
        'on_hold'          => ['label' => 'On Hold',                  'progress' => 40],
        'returned'         => ['label' => 'Returned to Sender',        'progress' => 0],
        'cancelled'        => ['label' => 'Cancelled',                 'progress' => 0],
        'lost'             => ['label' => 'Shipment Lost',             'progress' => 0],
        'damaged'          => ['label' => 'Damaged in Transit',        'progress' => 0],
    ];

    private $customsStages = [
        'customs_inspection' => ['label' => 'Customs Inspection',     'progress' => 55],
        'customs_clearance'  => ['label' => 'Customs Cleared',        'progress' => 60],
        'customs_delayed'    => ['label' => 'Customs Delayed',        'progress' => 52],
        'customs_seized'     => ['label' => 'Customs Seized',         'progress' => 0],
        'held'               => ['label' => 'Held at Warehouse',      'progress' => 45],
    ];

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->logger = null;
        try { $this->logger = getLogger(); } catch (Exception $e) { /* ignore */ }
    }

    /**
     * Progress a single shipment by one stage.
     *
     * @param int $shipmentId
     * @return array ['success' => bool, 'new_status' => string|null, 'message' => string]
     */
    public function progressShipment(int $shipmentId): array {
        ensureTrackingHistory($this->db);

        $stmt = $this->db->prepare("SELECT * FROM shipments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $shipmentId]);
        $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$shipment) {
            return ['success' => false, 'new_status' => null, 'message' => 'Shipment not found.'];
        }

        $currentStatus = strtolower($shipment['status']);
        $originCity = $shipment['origin_city'] ?? '';
        $destCity = $shipment['destination_city'] ?? '';
        $originCountry = $shipment['origin_country'] ?? 'US';
        $destCountry = $shipment['destination_country'] ?? 'US';
        $isInternational = ($originCountry !== $destCountry);

        $nextStatus = $this->determineNextStatus($currentStatus, $isInternational);
        if (!$nextStatus) {
            return ['success' => true, 'new_status' => $currentStatus, 'message' => 'Shipment is in terminal state.'];
        }

        $location = $this->determineLocation($nextStatus, $originCity, $destCity, $shipment['current_city'] ?? '');
        $description = $this->generateDescription($nextStatus, $location, $shipment);
        $timestamp = $this->generateTimestamp($shipment, $nextStatus);

        try {
            $this->db->beginTransaction();

            $this->db->prepare("
                UPDATE shipments SET status = :status, updated_at = :ts, current_city = :city, current_country = :country
                WHERE id = :id
            ")->execute([
                ':status' => $nextStatus,
                ':ts' => $timestamp,
                ':city' => $location['city'],
                ':country' => $location['country'],
                ':id' => $shipmentId,
            ]);

            $trackingNumber = $shipment['tracking_number'];
            $remarks = $description . ' [Simulated]';
            $adminName = 'Simulation Engine';

            $this->db->prepare("
                INSERT INTO shipment_status_history_v2 (shipment_id, status_code, occurred_at, location, remarks, occurred_by)
                VALUES (:sid, :s, :ts, :loc, :r, :by)
            ")->execute([
                ':sid' => $shipmentId,
                ':s' => $nextStatus,
                ':ts' => $timestamp,
                ':loc' => $location['display'],
                ':r' => $remarks,
                ':by' => $adminName,
            ]);

            $this->db->prepare("
                INSERT INTO tracking_history (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, created_at)
                VALUES (:sid, :tn, :s, :loc, :desc, :ts, :by, NOW())
            ")->execute([
                ':sid' => $shipmentId,
                ':tn' => $trackingNumber,
                ':s' => $nextStatus,
                ':loc' => $location['display'],
                ':desc' => $description,
                ':ts' => $timestamp,
                ':by' => $adminName,
            ]);

            if ($nextStatus === 'delivered') {
                $this->db->prepare("
                    UPDATE shipments SET actual_delivery = :ts, delivery_date = :date
                    WHERE id = :id
                ")->execute([
                    ':ts' => $timestamp,
                    ':date' => date('Y-m-d', strtotime($timestamp)),
                    ':id' => $shipmentId,
                ]);
            }

            $this->db->commit();
            if (function_exists('clearDashboardCache')) {
                clearDashboardCache();
            }

            return [
                'success' => true,
                'new_status' => $nextStatus,
                'message' => 'Progressed to: ' . statusLabel($nextStatus),
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            if ($this->logger) {
                $this->logger->error('SimulationEngine progress failed', [
                    'shipment_id' => $shipmentId,
                    'tracking' => $shipment['tracking_number'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
            return ['success' => false, 'new_status' => $currentStatus, 'message' => $e->getMessage()];
        }
    }

    /**
     * Run simulation on multiple eligible shipments.
     *
     * @param int $maxShipments Maximum number to process
     * @return array ['processed' => int, 'progressed' => int, 'terminal' => int, 'errors' => int]
     */
    public function runBatch(int $maxShipments = 50): array {
        $eligible = $this->fetchEligibleShipments($maxShipments);
        $processed = 0;
        $progressed = 0;
        $terminal = 0;
        $errors = 0;

        foreach ($eligible as $shipment) {
            $sid = (int)$shipment['id'];
            $result = $this->progressShipment($sid);
            $processed++;
            if (!$result['success']) {
                $errors++;
            } elseif (in_array($result['new_status'], ['delivered','cancelled','returned','lost','damaged','customs_seized'], true)) {
                $terminal++;
            } elseif ($result['new_status'] !== $shipment['status']) {
                $progressed++;
            }
        }

        return [
            'processed' => $processed,
            'progressed' => $progressed,
            'terminal' => $terminal,
            'errors' => $errors,
        ];
    }

    /**
     * Fetch shipments that are eligible for simulation (not terminal).
     */
    private function fetchEligibleShipments(int $limit = 50): array {
        $terminal = ['delivered','cancelled','returned','lost','damaged','customs_seized'];
        $placeholders = implode(',', array_fill(0, count($terminal), '?'));
        $stmt = $this->db->prepare("
            SELECT id, tracking_number, status, origin_city, destination_city,
                   origin_country, destination_country, current_city, created_at
            FROM shipments
            WHERE status NOT IN ($placeholders)
              AND is_active = 1
            ORDER BY updated_at ASC
            LIMIT ?
        ");
        $i = 1;
        foreach ($terminal as $t) {
            $stmt->bindValue($i++, $t);
        }
        $stmt->bindValue($i, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Determine the next status for a shipment based on its current status.
     */
    private function determineNextStatus(string $currentStatus, bool $isInternational): string {
        $status = strtolower($currentStatus);

        if (isset($this->exceptionStatuses[$status])) {
            return $status;
        }

        if (isset($this->customsStages[$status])) {
            $customsFlow = ['customs_inspection','customs_clearance','customs_delayed','customs_clearance','in_transit'];
            $idx = array_search($status, $customsFlow);
            if ($idx !== false && $idx < count($customsFlow) - 1) {
                if ($status === 'customs_clearance' && !$isInternational) {
                    return 'in_transit';
                }
                return $customsFlow[$idx + 1];
            }
            return 'in_transit';
        }

        $flow = ['created','pending_pickup','picked_up','received_origin','sorted','in_transit','at_hub','out_for_delivery','delivered'];
        $idx = array_search($status, $flow);
        if ($idx !== false && $idx < count($flow) - 1) {
            if ($flow[$idx + 1] === 'customs_inspection' && !$isInternational) {
                return 'in_transit';
            }
            if ($flow[$idx] === 'in_transit' && $isInternational && random_int(0, 3) === 0) {
                return 'customs_inspection';
            }
            return $flow[$idx + 1];
        }

        if (random_int(0, 15) === 0 && in_array($status, ['in_transit','at_hub'], true)) {
            return 'on_hold';
        }

        return $status;
    }

    /**
     * Determine the location for a given status.
     */
    private function determineLocation(string $status, string $originCity, string $destCity, string $currentCity): array {
        $status = strtolower($status);

        if ($status === 'delivered') {
            return ['city' => $destCity, 'country' => $this->cityCountry($destCity), 'display' => $destCity];
        }
        if ($status === 'returned') {
            return ['city' => $originCity, 'country' => $this->cityCountry($originCity), 'display' => 'Returned to ' . $originCity];
        }
        if ($status === 'cancelled' || $status === 'lost' || $status === 'damaged') {
            $city = $currentCity ?: $originCity;
            return ['city' => $city, 'country' => $this->cityCountry($city), 'display' => $city];
        }

        if ($status === 'customs_inspection' || $status === 'customs_clearance' || $status === 'customs_delayed' || $status === 'customs_seized') {
            $hub = $this->pickHub($destCity);
            return ['city' => $hub, 'country' => $this->hubCountry($hub), 'display' => $hub . ' — Customs Clearance'];
        }
        if ($status === 'held') {
            $hub = $this->pickHub($destCity);
            return ['city' => $hub, 'country' => $this->hubCountry($hub), 'display' => 'Held at ' . $hub];
        }

        // Build a believable route through intermediate cities.
        $hops = [$originCity];
        $shuffled = $this->usaCities;
        shuffle($shuffled);
        foreach ($shuffled as $c) {
            if ($c !== $originCity && $c !== $destCity) {
                $hops[] = $c;
                if (count($hops) >= random_int(2, 4)) break;
            }
        }
        $hops[] = $destCity;
        $routeIdx = random_int(1, max(1, count($hops) - 2));
        $routeCity = $hops[$routeIdx] ?? $destCity;

        if ($status === 'created' || $status === 'pending_pickup' || $status === 'processing') {
            return ['city' => $originCity, 'country' => $this->cityCountry($originCity), 'display' => $originCity . ' (Order Received)'];
        }
        if ($status === 'picked_up') {
            return ['city' => $originCity, 'country' => $this->cityCountry($originCity), 'display' => $originCity . ' — Picked Up'];
        }
        if ($status === 'received_origin') {
            return ['city' => $originCity, 'country' => $this->cityCountry($originCity), 'display' => $originCity . ' Sorting Center'];
        }
        if ($status === 'sorted') {
            return ['city' => $originCity, 'country' => $this->cityCountry($originCity), 'display' => $originCity . ' Sorting Center'];
        }
        if ($status === 'in_transit') {
            return ['city' => $routeCity, 'country' => $this->cityCountry($routeCity), 'display' => 'In Transit: ' . $originCity . ' → ' . $destCity];
        }
        if ($status === 'at_hub') {
            return ['city' => $destCity, 'country' => $this->cityCountry($destCity), 'display' => $destCity . ' Destination Hub'];
        }
        if ($status === 'out_for_delivery') {
            return ['city' => $destCity, 'country' => $this->cityCountry($destCity), 'display' => 'Out for Delivery — ' . $destCity];
        }

        $city = $currentCity ?: $originCity;
        return ['city' => $city, 'country' => $this->cityCountry($city), 'display' => $city];
    }

    private function pickHub(string $preferredCity): string {
        if (isset($this->internationalHubs[$preferredCity])) {
            return $preferredCity;
        }
        return $this->randomItem(array_keys($this->internationalHubs));
    }

    private function hubCountry(string $hub): string {
        return $this->internationalHubs[$hub] ?? 'US';
    }

    private function cityCountry(string $city): string {
        return $this->internationalHubs[$city] ?? ($this->usaCities[$city] ?? 'US');
    }

    /**
     * Generate a human-readable description for a status event.
     */
    private function generateDescription(string $status, array $location, array $shipment): string {
        $status = strtolower($status);
        $weight = $shipment['total_weight'] ?? 0;
        $tn = $shipment['tracking_number'] ?? '';

        $descriptions = [
            'created' => 'Shipment information received. Awaiting pickup.',
            'pending_pickup' => 'Pickup has been scheduled. Courier will arrive soon.',
            'picked_up' => 'Parcel successfully picked up by courier. Weight: ' . $weight . ' kg.',
            'received_origin' => 'Parcel received at origin facility. Scanning and sorting in progress.',
            'sorted' => 'Parcel has been sorted and is ready for dispatch.',
            'in_transit' => 'Parcel is in transit to destination hub. Expected delivery on schedule.',
            'at_hub' => 'Parcel has arrived at the destination hub. Awaiting final dispatch.',
            'out_for_delivery' => 'Parcel is out for delivery. Driver en route to recipient.',
            'delivered' => 'Parcel has been delivered successfully. Thank you for using our service.',
            'customs_inspection' => 'Parcel is undergoing customs inspection. Processing time: 1-3 business days.',
            'customs_clearance' => 'Customs clearance completed. Parcel released for onward journey.',
            'customs_delayed' => 'Customs clearance delayed. Additional documentation may be required.',
            'customs_seized' => 'Parcel has been seized by customs authorities. Contact support.',
            'on_hold' => 'Shipment is on hold pending resolution. Customer service will contact you.',
            'returned' => 'Parcel is being returned to sender. Delivery unsuccessful.',
            'cancelled' => 'Shipment has been cancelled.',
            'lost' => 'Shipment status: lost. Investigation initiated.',
            'damaged' => 'Parcel damaged in transit. Damage report filed.',
        ];

        $desc = $descriptions[$status] ?? 'Status updated to: ' . ($status ?? 'unknown');
        return $desc;
    }

    /**
     * Generate a realistic timestamp for a status event.
     */
    private function generateTimestamp(array $shipment, string $nextStatus): string {
        $startAnchor = $shipment['shipment_date'] ?? $shipment['created_at'] ?? 'now';
        $createdAt = strtotime($startAnchor);
        if ($createdAt === false || $createdAt > time()) {
            $createdAt = time() - 86400;
        }
        $now = time();

        $status = strtolower($nextStatus);
        if ($status === 'delivered') {
            $ts = random_int($createdAt, $now);
            return date('Y-m-d H:i:s', $ts);
        }

        $flow = ['created','pending_pickup','picked_up','received_origin','sorted','in_transit','at_hub','out_for_delivery','delivered'];
        $currentStatus = strtolower($shipment['status']);
        $currentIdx = array_search($currentStatus, $flow);
        $nextIdx = array_search($status, $flow);
        if ($currentIdx === false) $currentIdx = 0;
        if ($nextIdx === false) $nextIdx = $currentIdx + 1;

        $totalWindow = $now - $createdAt;
        if ($totalWindow <= 0) $totalWindow = 3600;
        $stepFraction = ($nextIdx + 1) / count($flow);
        $targetOffset = (int)($totalWindow * $stepFraction * random_int(80, 100) / 100);
        $ts = $createdAt + $targetOffset;
        if ($ts > $now) $ts = $now;
        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * Reset a shipment to initial state for re-simulation.
     */
    public function resetShipment(int $shipmentId): array {
        try {
            $this->db->beginTransaction();

            $this->db->prepare("UPDATE shipments SET status = 'pending_pickup', current_city = origin_city, current_country = origin_country, updated_at = NOW() WHERE id = :id")
                ->execute([':id' => $shipmentId]);

            $this->db->prepare("DELETE FROM tracking_history WHERE shipment_id = :sid")
                ->execute([':sid' => $shipmentId]);
            $this->db->prepare("DELETE FROM shipment_status_history_v2 WHERE shipment_id = :sid")
                ->execute([':sid' => $shipmentId]);

            $this->db->prepare("
                INSERT INTO tracking_history (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, created_at)
                VALUES (:sid, :tn, 'pending_pickup', :loc, 'Shipment information received', NOW(), 'Simulation Engine', NOW())
            ")->execute([
                ':sid' => $shipmentId,
                ':tn' => $this->getTrackingNumber($shipmentId),
                ':loc' => '',
            ]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Shipment reset to initial state.'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Run full simulation for a shipment (all stages until delivered).
     */
    public function simulateFull(int $shipmentId, callable $progressCallback = null): array {
        $maxSteps = 20;
        $steps = 0;
        $results = [];

        while ($steps < $maxSteps) {
            $result = $this->progressShipment($shipmentId);
            $results[] = $result;
            if (!$result['success']) break;
            if (in_array($result['new_status'], ['delivered','cancelled','returned','lost','damaged','customs_seized'], true)) break;
            $steps++;
            if ($progressCallback) {
                $progressCallback($steps, $result);
            }
        }

        return $results;
    }

    private function getTrackingNumber(int $shipmentId): string {
        $stmt = $this->db->prepare("SELECT tracking_number FROM shipments WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $shipmentId]);
        return $stmt->fetchColumn() ?: 'UNKNOWN';
    }
}
