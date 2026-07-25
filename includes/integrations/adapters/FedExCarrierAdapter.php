<?php
/**
 * FedEx carrier adapter.
 * Extends CarrierAdapter with FedEx-specific status mapping and endpoint paths.
 */
require_once __DIR__ . '/CarrierAdapter.php';

class FedExCarrierAdapter extends CarrierAdapter {
    protected $defaultStatusMap = [
        'created'              => 'created',
        'label_created'        => 'created',
        'picked_up'            => 'picked_up',
        'in_transit'           => 'in_transit',
        'on_feeder'            => 'in_transit',
        'delivered'            => 'delivered',
        'delivery'             => 'delivered',
        'exception'            => 'on_hold',
        'delivery_exception'    => 'delivery_failed',
        'return_to_shipper'     => 'returned',
        'cancelled'            => 'cancelled',
    ];

    public function pollTracking($trackingNumber) {
        return $this->request('GET', 'track/v1/trackingnumbers/' . rawurlencode($trackingNumber));
    }

    public function getRates(array $params) {
        return $this->request('POST', 'rate/v1/rates/quotes', $params);
    }

    public function createShipment(array $shipment) {
        return $this->request('POST', 'ship/v1/shipments', $shipment);
    }
}
