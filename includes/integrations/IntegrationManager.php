<?php
/**
 * Integration Manager — factory + dispatch entry point.
 *
 * Resolves the right IntegrationClient + Adapter for a given integration_type
 * and provider. Keeps provider wiring in one place so the rest of the app
 * (admin UI, webhooks, worker) never has to know which class backs a type.
 */

require_once __DIR__ . '/IntegrationClient.php';
require_once __DIR__ . '/CarrierAdapter.php';
require_once __DIR__ . '/PaymentAdapter.php';
require_once __DIR__ . '/NotificationAdapter.php';

class IntegrationManager {
    /**
     * Build an IntegrationClient for a type/provider.
     * @return IntegrationClient|null
     */
    public static function client(PDO $db, $type, $provider = null) {
        return IntegrationClient::loadByType($db, $type, $provider);
    }

    /**
     * Build the Adapter instance appropriate for an integration row.
     * Provider-specific subclasses are auto-resolved from the provider name.
     */
    public static function adapter(PDO $db, array $integration) {
        $type = $integration['integration_type'] ?? 'other';
        $provider = ucfirst(strtolower($integration['provider'] ?? ''));
        $class = null;

        switch ($type) {
            case 'tracking':
            case 'rating':
            case 'shipping':
                $class = self::resolveClass('Carrier', $provider);
                return $class ? new $class($db, $integration) : new CarrierAdapter($db, $integration);
            case 'payment':
                $class = self::resolveClass('Payment', $provider);
                return $class ? new $class($db, $integration) : new PaymentAdapter($db, $integration);
            case 'sms':
            case 'email':
                $class = self::resolveClass('Notification', $provider);
                return $class ? new $class($db, $integration) : new NotificationAdapter($db, $integration);
            default:
                return null;
        }
    }

    private static function resolveClass($family, $provider) {
        if (!$provider) {
            return null;
        }
        $name = $provider . $family . 'Adapter';
        $path = __DIR__ . '/adapters/' . $name . '.php';
        if (file_exists($path)) {
            require_once $path;
            return class_exists($name) ? $name : null;
        }
        return null;
    }
}
