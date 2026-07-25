<?php
/**
 * Admin helpers for the Integrations module.
 * Shared by integrations.php, integrations_edit.php, integrations_logs.php.
 */
require_once __DIR__ . '/../../includes/db.php';

if (!function_exists('integrationTypeOptions')) {
    function integrationTypeOptions() {
        return [
            'tracking' => 'Carrier Tracking (inbound)',
            'rating'   => 'Carrier Rating (outbound)',
            'shipping' => 'Carrier Shipping / Labels (outbound)',
            'payment'  => 'Payment Gateway',
            'sms'      => 'SMS Provider',
            'email'    => 'Email Provider',
            'customs'  => 'Customs System',
            'other'    => 'Other',
        ];
    }
}

if (!function_exists('authTypeOptions')) {
    function authTypeOptions() {
        return [
            'api_key' => 'API Key (header)',
            'bearer'  => 'Bearer Token',
            'basic'   => 'Basic Auth',
            'oauth'   => 'OAuth Token',
            'none'    => 'None',
        ];
    }
}

if (!function_exists('providerOptions')) {
    function providerOptions($type) {
        $map = [
            'tracking' => ['fedex' => 'FedEx', 'dhl' => 'DHL', 'ups' => 'UPS', 'usps' => 'USPS', 'fastship' => 'FastShip Logistics', 'generic' => 'Generic REST'],
            'rating'   => ['fedex' => 'FedEx', 'dhl' => 'DHL', 'ups' => 'UPS', 'fastship' => 'FastShip Logistics', 'generic' => 'Generic REST'],
            'shipping' => ['fedex' => 'FedEx', 'dhl' => 'DHL', 'ups' => 'UPS', 'fastship' => 'FastShip Logistics', 'generic' => 'Generic REST'],
            'payment'  => ['stripe' => 'Stripe', 'paypal' => 'PayPal'],
            'sms'      => ['twilio' => 'Twilio'],
            'email'    => ['sendgrid' => 'SendGrid', 'smtp' => 'SMTP'],
        ];
        return $map[$type] ?? ['generic' => 'Generic'];
    }
}

if (!function_exists('integrationHealthBadge')) {
    function integrationHealthBadge(array $row) {
        if (empty($row['is_active'])) {
            return '<span class="badge bg-secondary">Disabled</span>';
        }
        $fails = (int)($row['consecutive_failures'] ?? 0);
        if ($fails >= 5) {
            return '<span class="badge bg-danger">Failing (' . $fails . ')</span>';
        }
        if ($fails > 0) {
            return '<span class="badge bg-warning text-dark">Degraded (' . $fails . ')</span>';
        }
        return '<span class="badge bg-success">Healthy</span>';
    }
}
