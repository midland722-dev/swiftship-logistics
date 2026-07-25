<?php
/**
 * DHL carrier adapter.
 */
require_once __DIR__ . '/CarrierAdapter.php';

class DHLCarrierAdapter extends CarrierAdapter {
    protected $defaultStatusMap = [
        'pre_transit'          => 'created',
        'information_received' => 'created',
        'picked_up'            => 'picked_up',
        'in_transit'           => 'in_transit',
        'transit'              => 'in_transit',
        'delivered'            => 'delivered',
        'delivery'             => 'delivered',
        'exception'            => 'on_hold',
        'failed_attempt'       => 'delivery_failed',
        'return'               => 'returned',
        'cancelled'            => 'cancelled',
    ];

    public function pollTracking($trackingNumber) {
        return $this->request('GET', 'shipments', [], ['tracking-number' => $trackingNumber]);
    }
}
