<?php
/**
 * FastShip Logistics carrier adapter.
 */
require_once __DIR__ . '/CarrierAdapter.php';

class FastShipCarrierAdapter extends CarrierAdapter {
    protected $defaultStatusMap = [
        'pre_transit'          => 'created',
        'info_received'        => 'created',
        'picked_up'            => 'picked_up',
        'in_transit'           => 'in_transit',
        'transit'              => 'in_transit',
        'out_for_delivery'     => 'out_for_delivery',
        'delivered'            => 'delivered',
        'delivery'             => 'delivered',
        'exception'            => 'on_hold',
        'failed_attempt'       => 'delivery_failed',
        'return'               => 'returned',
        'cancelled'            => 'cancelled',
    ];

    public function pollTracking($trackingNumber) {
        return $this->request('GET', 'trackings/' . rawurlencode($trackingNumber));
    }
}
