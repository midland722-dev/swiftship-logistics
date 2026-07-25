<?php
/**
 * UPS carrier adapter.
 */
require_once __DIR__ . '/CarrierAdapter.php';

class UPSCarrierAdapter extends CarrierAdapter {
    protected $defaultStatusMap = [
        'M'  => 'created',          // Manifest picked up / order received
        'P'  => 'picked_up',
        'I'  => 'in_transit',
        'X'  => 'out_for_delivery',
        'D'  => 'delivered',
        'RS' => 'returned',
        'C'  => 'cancelled',
        'E'  => 'on_hold',
    ];

    public function pollTracking($trackingNumber) {
        return $this->request('GET', 'track/v1/details/' . rawurlencode($trackingNumber));
    }
}
