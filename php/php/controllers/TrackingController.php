<?php
/**
 * TrackingController
 *
 * Handles HTTP requests for tracking endpoints.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/rate-limit.php';
require_once __DIR__ . '/../services/TrackingService.php';

class TrackingController {
    public static function track(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            json_error('Method not allowed.', 405, 'METHOD_NOT_ALLOWED');
        }

        $trackingNumber = trim($_GET['id'] ?? '');

        $errors = TrackingValidator::validateTrackingNumber($trackingNumber);
        if ($errors) {
            json_success([
                'found' => false,
                'message' => implode(' ', $errors),
                'shipment' => null,
                'history' => [],
            ]);
        }

        if (!rate_limit('tracking_api', 120, 60)) {
            json_error('Too many requests. Please try again later.', 429, 'RATE_LIMIT_EXCEEDED');
        }

        $result = TrackingService::track($trackingNumber);

        $publicShipment = $result['shipment'] ? $result['shipment']->toTrackingArray() : null;

        json_success([
            'found' => $result['shipment'] !== null,
            'shipment' => $publicShipment,
            'history' => array_map(fn($e) => $e->toArray(), $result['history']),
        ]);
    }

    public static function statusUpdate(): void {
        require_role(['admin', 'staff']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_error('Method not allowed.', 405, 'METHOD_NOT_ALLOWED');
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $userId = (int)($_SESSION['user_id'] ?? 0);

        try {
            $result = TrackingService::updateStatus($input, $userId);
            json_success([
                'shipment' => $result['shipment']->toArray(),
                'event' => $result['event']->toArray(),
            ], 200, 'Status updated.');
        } catch (InvalidArgumentException $e) {
            json_error($e->getMessage(), 422, 'VALIDATION_ERROR');
        } catch (RuntimeException $e) {
            $code = $e->getCode() ?: 'ERROR';
            $status = match ($code) {
                404 => 404,
                422 => 422,
                default => 400,
            };
            json_error($e->getMessage(), $status, $code);
        }
    }

    public static function createShipment(): void {
        require_role(['admin', 'staff']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_error('Method not allowed.', 405, 'METHOD_NOT_ALLOWED');
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $userId = (int)($_SESSION['user_id'] ?? 0);

        try {
            $result = TrackingService::createShipment($input, $userId);
            json_success([
                'shipment' => $result['shipment']->toArray(),
                'event' => $result['event']->toArray(),
            ], 201, 'Shipment created.');
        } catch (InvalidArgumentException $e) {
            json_error($e->getMessage(), 422, 'VALIDATION_ERROR');
        } catch (RuntimeException $e) {
            $code = $e->getCode() ?: 'ERROR';
            $status = match ($code) {
                404 => 404,
                422 => 422,
                default => 400,
            };
            json_error($e->getMessage(), $status, $code);
        }
    }

    /**
     * @return TrackingHistory[]
     */
    public static function history(string $trackingNumber): array {
        return TrackingService::getHistory($trackingNumber);
    }
}
